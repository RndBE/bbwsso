<?php if (!defined('BASEPATH'))
    exit('No direct script access allowed');
error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
ini_set('display_errors', 0);

class Chatbot extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('mlogin');
        $this->load->model('m_analisa');
        $this->config->load('openai', TRUE);
        $this->load->library('DateResolver');
    }

    // ─── Helper: read JSON body (supports internal fake input) ───
    private function _json_input()
    {
        if (!empty($this->_use_fake) && isset($this->_fake_input)) {
            return $this->_fake_input;
        }
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    // ─── Helper: JSON response ───
    private function _json_response($data)
    {
        // Bersihkan output buffer agar tidak ada PHP warning/notice yang bocor
        if (ob_get_length() > 0) {
            ob_clean();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }

    // ═══════════════════════════════════════════════════════════
    //  OPENAI CHAT ENDPOINT
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /chatbot/chat
     * Body: { "message": "...", "session_id": "..." (optional) }
     *
     * Session-based conversation: backend stores full message chain
     * (including tool_calls + tool results) so multi-turn context works.
     */
    public function chat()
    {
        // ── Pastikan tidak timeout saat proses query berat (tool calls + API) ──
        @set_time_limit(300);

        // ── Tangkap fatal error PHP agar response tidak pernah kosong ──
        register_shutdown_function(function () {
            $err = error_get_last();
            if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
                // Pastikan buffer bersih
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                http_response_code(500);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'status'  => 'error',
                    'message' => 'PHP Fatal Error: ' . $err['message'],
                    '_debug_file' => basename($err['file']),
                    '_debug_line' => $err['line'],
                ], JSON_UNESCAPED_UNICODE);
            }
        });

        $input = $this->_json_input();
        $message = isset($input['message']) ? trim($input['message']) : '';
        $sid = isset($input['session_id']) ? $input['session_id'] : null;

        if ($message === '') {
            return $this->_json_response(['status' => 'error', 'message' => 'Pesan tidak boleh kosong']);
        }

        $api_key = $this->config->item('openai_api_key', 'openai');
        if (empty($api_key)) {
            return $this->_json_response(['status' => 'error', 'message' => 'API key OpenAI belum dikonfigurasi']);
        }

        $model = $this->config->item('openai_model', 'openai') ?: 'gpt-4o-mini';

        // ── Load or create session ──
        if (!$sid) {
            $sid = uniqid('copilot_', true);
        }
        $messages = $this->_load_session($sid);

        // If new session, prepend system prompt
        if (empty($messages)) {
            $messages[] = ['role' => 'system', 'content' => $this->_system_prompt()];
        } else {
            // Refresh system prompt (date/time changes each request)
            $messages[0] = ['role' => 'system', 'content' => $this->_system_prompt()];
        }

        // Append current user message
        $messages[] = ['role' => 'user', 'content' => $message];

        // Token management: keep system + last N messages
        $messages = $this->_trim_messages($messages, 20);

        // OpenAI tools definition
        $tools = $this->_openai_tools();

        // ── First API call ──
        $response = $this->_call_openai($api_key, $model, $messages, $tools);

        if (!$response || isset($response['_error'])) {
            $err_detail = isset($response['_error']) ? $response['_error'] : 'Gagal menghubungi OpenAI API';
            http_response_code(500);
            return $this->_json_response(['status' => 'error', 'message' => $err_detail]);
        }

        // ── Handle tool calls (function calling loop, max 5 iterations) ──
        $iterations = 0;
        $debug_log = []; // debug: track all tool calls
        while (
            isset($response['choices'][0]['message']['tool_calls']) &&
            !empty($response['choices'][0]['message']['tool_calls']) &&
            $iterations < 5
        ) {
            $assistant_msg = $response['choices'][0]['message'];
            $messages[] = $assistant_msg; // includes tool_calls array

            foreach ($assistant_msg['tool_calls'] as $tool_call) {
                $fn_name = $tool_call['function']['name'];
                $fn_args = json_decode($tool_call['function']['arguments'], true) ?? [];

                // Execute the function internally
                $fn_result = $this->_execute_tool($fn_name, $fn_args);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $tool_call['id'],
                    'content' => json_encode($fn_result, JSON_UNESCAPED_UNICODE)
                ];

                // debug: record tool call + result
                $debug_log[] = [
                    'tool' => $fn_name,
                    'args' => $fn_args,
                    'result_preview' => mb_substr(json_encode($fn_result, JSON_UNESCAPED_UNICODE), 0, 2000)
                ];
            }

            // Call OpenAI again with tool results
            $response = $this->_call_openai($api_key, $model, $messages, $tools);

            if (!$response || isset($response['_error'])) {
                $err_detail = isset($response['_error']) ? $response['_error'] : 'Gagal menghubungi OpenAI API';
                http_response_code(500);
                return $this->_json_response(['status' => 'error', 'message' => $err_detail]);
            }

            $iterations++;
        }

        // ── Extract final answer ──
        $reply = isset($response['choices'][0]['message']['content'])
            ? $response['choices'][0]['message']['content']
            : 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.';

        // Append assistant's final reply to history
        $messages[] = ['role' => 'assistant', 'content' => $reply];

        // Save full conversation to session
        $this->_save_session($sid, $messages);

        $this->_json_response([
            'status' => 'sukses',
            'session_id' => $sid,
            'reply' => $reply,
            '_debug' => $debug_log
        ]);
    }

    // ═══════════════════════════════════════════════════════════
    //  ASK ENDPOINT — External chatbot integration
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /chatbot/ask
     * Body: { "uuid": "...", "message": "..." }
     *
     * Endpoint penerima dari sistem eksternal (misalnya _ask_bot).
     * Menggunakan uuid sebagai session_id agar percakapan multi-turn tetap terjaga.
     * Response: { "message": { "content": "..." } }
     */
    public function ask()
    {
        // Hanya terima method POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            return $this->_json_response([
                'status' => 'error',
                'message' => ['content' => 'Method Not Allowed']
            ]);
        }

        $input = $this->_json_input();
        $uuid = isset($input['uuid']) ? trim($input['uuid']) : '';
        $message = isset($input['message']) ? trim($input['message']) : '';

        // Validasi input
        if ($uuid === '' || $message === '') {
            http_response_code(400);
            return $this->_json_response([
                'status' => 'error',
                'message' => ['content' => 'Parameter uuid dan message wajib diisi']
            ]);
        }

        // Cek API key
        $api_key = $this->config->item('openai_api_key', 'openai');
        if (empty($api_key)) {
            http_response_code(503);
            return $this->_json_response([
                'status' => 'error',
                'message' => ['content' => 'API key belum dikonfigurasi']
            ]);
        }

        $model = $this->config->item('openai_model', 'openai') ?: 'gpt-4o-mini';

        // ── Gunakan uuid sebagai session_id ──
        $sid = 'ext_' . $uuid;
        $messages = $this->_load_session($sid);

        if (empty($messages)) {
            $messages[] = ['role' => 'system', 'content' => $this->_system_prompt()];
        } else {
            $messages[0] = ['role' => 'system', 'content' => $this->_system_prompt()];
        }

        $messages[] = ['role' => 'user', 'content' => $message];
        $messages = $this->_trim_messages($messages, 20);

        $tools = $this->_openai_tools();

        // ── First API call ──
        $response = $this->_call_openai($api_key, $model, $messages, $tools);

        if (!$response || isset($response['_error'])) {
            http_response_code(502);
            return $this->_json_response([
                'status' => 'error',
                'message' => ['content' => 'Gagal menghubungi AI. Silakan coba lagi nanti.']
            ]);
        }

        // ── Handle tool calls loop (max 5 iterations) ──
        $iterations = 0;
        while (
            isset($response['choices'][0]['message']['tool_calls']) &&
            !empty($response['choices'][0]['message']['tool_calls']) &&
            $iterations < 5
        ) {
            $assistant_msg = $response['choices'][0]['message'];
            $messages[] = $assistant_msg;

            foreach ($assistant_msg['tool_calls'] as $tool_call) {
                $fn_name = $tool_call['function']['name'];
                $fn_args = json_decode($tool_call['function']['arguments'], true) ?? [];

                $fn_result = $this->_execute_tool($fn_name, $fn_args);

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $tool_call['id'],
                    'content' => json_encode($fn_result, JSON_UNESCAPED_UNICODE)
                ];
            }

            $response = $this->_call_openai($api_key, $model, $messages, $tools);

            if (!$response || isset($response['_error'])) {
                http_response_code(502);
                return $this->_json_response([
                    'status' => 'error',
                    'message' => ['content' => 'Gagal menghubungi AI. Silakan coba lagi nanti.']
                ]);
            }

            $iterations++;
        }

        // ── Extract final reply ──
        $reply = isset($response['choices'][0]['message']['content'])
            ? $response['choices'][0]['message']['content']
            : 'Maaf, saya tidak bisa memproses permintaan Anda saat ini.';

        $messages[] = ['role' => 'assistant', 'content' => $reply];
        $this->_save_session($sid, $messages);

        // ── Response sesuai format yang dibaca _ask_bot ──
        $this->_json_response([
            'status' => 'sukses',
            'message' => ['content' => $reply],
            'content' => $reply,
            'reply' => $reply
        ]);
    }

    // ─── Session Helpers: file-based conversation storage ───

    private function _session_dir()
    {
        $dir = APPPATH . 'cache/copilot_sessions';
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir;
    }

    private function _session_path($sid)
    {
        // Sanitize session ID to prevent directory traversal
        $safe_sid = preg_replace('/[^a-zA-Z0-9_\.\-]/', '', $sid);
        return $this->_session_dir() . '/' . $safe_sid . '.json';
    }

    private function _load_session($sid)
    {
        $path = $this->_session_path($sid);
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            return is_array($data) ? $data : [];
        }
        return [];
    }

    private function _save_session($sid, $messages)
    {
        $path = $this->_session_path($sid);
        file_put_contents($path, json_encode($messages, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * Trim messages to stay within token budget.
     * Keeps: system prompt (index 0) + last $max messages.
     * Ensures tool_calls/tool response pairs are never split.
     */
    private function _trim_messages($messages, $max = 40)
    {
        if (count($messages) <= $max + 1) {
            return $messages;
        }

        $system = [$messages[0]]; // always keep system prompt

        // Start from where we'd normally cut
        $cut_start = count($messages) - $max;
        if ($cut_start < 1)
            $cut_start = 1;

        // Scan forward to find a safe cut point (a 'user' or 'system' message)
        // Avoid cutting in the middle of tool_calls → tool response sequences
        for ($i = $cut_start; $i < count($messages); $i++) {
            $role = $messages[$i]['role'] ?? '';
            // Safe to start from 'user' or 'assistant' without tool_calls
            if ($role === 'user') {
                $cut_start = $i;
                break;
            }
            // Also safe if it's an assistant message WITHOUT tool_calls
            if ($role === 'assistant' && empty($messages[$i]['tool_calls'])) {
                $cut_start = $i;
                break;
            }
        }

        $tail = array_slice($messages, $cut_start);

        return array_merge($system, $tail);
    }

    // ═══════════════════════════════════════════════════════════
    //  WHISPER TRANSCRIBE ENDPOINT
    // ═══════════════════════════════════════════════════════════

    /**
     * POST /chatbot/transcribe
     * Body: multipart/form-data with 'audio' file
     * Returns: { "status": "sukses", "text": "..." }
     */
    public function transcribe()
    {
        $api_key = $this->config->item('openai_api_key', 'openai');
        if (empty($api_key)) {
            return $this->_json_response(['status' => 'error', 'message' => 'API key OpenAI belum dikonfigurasi']);
        }

        // Check for uploaded audio file
        if (!isset($_FILES['audio']) || $_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            return $this->_json_response(['status' => 'error', 'message' => 'File audio tidak ditemukan']);
        }

        $tmp_path = $_FILES['audio']['tmp_name'];
        $mime = $_FILES['audio']['type'];

        // Map mime to extension (Whisper supports: mp3, mp4, mpeg, mpga, m4a, wav, webm)
        $ext_map = [
            'audio/webm' => 'webm',
            'audio/ogg' => 'ogg',
            'audio/wav' => 'wav',
            'audio/wave' => 'wav',
            'audio/mp3' => 'mp3',
            'audio/mpeg' => 'mp3',
            'audio/mp4' => 'mp4',
            'audio/m4a' => 'm4a',
        ];
        $ext = isset($ext_map[$mime]) ? $ext_map[$mime] : 'webm';

        // Send to Whisper API
        $ch = curl_init('https://api.openai.com/v1/audio/transcriptions');
        $cfile = new CURLFile($tmp_path, $mime, 'audio.' . $ext);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_POSTFIELDS => [
                'file' => $cfile,
                'model' => 'whisper-1',
                'language' => 'id',  // Indonesian
            ],
        ]);

        $response = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'Whisper API curl error: ' . $err);
            return $this->_json_response(['status' => 'error', 'message' => 'Gagal menghubungi Whisper API']);
        }

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            log_message('error', 'Whisper API error: ' . json_encode($decoded['error']));
            return $this->_json_response(['status' => 'error', 'message' => 'Whisper API error: ' . ($decoded['error']['message'] ?? 'Unknown')]);
        }

        $text = isset($decoded['text']) ? trim($decoded['text']) : '';

        $this->_json_response([
            'status' => 'sukses',
            'text' => $text
        ]);
    }

    // ─── System Prompt ───
    private function _system_prompt()
    {
        $now = date('Y-m-d H:i');
        $hari_arr = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $hari = $hari_arr[date('w')];

        return "Kamu adalah Copilot, asisten AI untuk sistem monitoring BBWS Serayu Opak (Balai Besar Wilayah Sungai). "
            . "Tugasmu membantu pengguna memahami data dari pos-pos monitoring (logger) seperti AWS, ARR, AWLR, dan sensor lainnya.\n\n"
            . "WILAYAH KERJA BBWS SERAYU OPAK:\n"
            . "- Wilayah kerja HANYA mencakup sebagian Provinsi JAWA TENGAH dan Provinsi D.I. YOGYAKARTA.\n"
            . "- DAS (Daerah Aliran Sungai) yang tercakup: Serayu, Opak, Oyo, Progo, Serang, Bogowonto, Luk Ulo, Ijo, Tipar, Telomoyo, Wawar, dan sekitarnya.\n"
            . "- SEMUA pos monitoring dalam sistem ini berada di Jawa Tengah atau DIY. TIDAK ADA pos di Jawa Timur, Jawa Barat, atau provinsi lain.\n"
            . "- Jika pengguna bertanya tentang pos di wilayah di luar Jawa Tengah/DIY, jawab bahwa wilayah tersebut di luar cakupan BBWS Serayu Opak.\n\n"
            . "PENGETAHUAN KATEGORI LOGGER:\n"
            . "- AWS (Automatic Weather Station) / AWR (Automatic Weather Recorder): SAMA, hanya beda istilah. BBWS menyebut AWS, PSDA menyebut AWR. Sensor cuaca LENGKAP (suhu, kelembapan, angin, tekanan udara, radiasi matahari, curah hujan). Pertanyaan 'cuaca', 'suhu', 'kelembapan', 'angin', 'tekanan' → cari pos AWS/AWR.\n"
            . "- ARR (Automatic Rain Recorder): KHUSUS sensor curah hujan. Pertanyaan 'hujan' bisa AWS/AWR atau ARR.\n"
            . "- AWLR (Automatic Water Level Recorder): sensor TMA (Tinggi Muka Air) dan debit sungai. Pertanyaan 'tinggi muka air', 'debit', 'TMA', 'banjir' → cari pos AWLR.\n"
            . "- Klimatologi: mirip AWS, sensor cuaca lengkap.\n"
            . "- AFMR (Automatic Flow Meter Recorder): sensor debit sungai.\n\n"
            . "PENTING TERMINOLOGI: AWR = AWS (sama persis). Jika user menyebut 'AWR' itu sama dengan 'AWS'. Jangan bingung dengan 'AWLR' yang berbeda (untuk tinggi muka air).\n\n"
            . "ATURAN PENTING SAAT USER TIDAK MENYEBUT POS SPESIFIK:\n"
            . "- Jika user tanya 'cuaca 7 hari terakhir' TANPA menyebut pos → TANYAKAN pos mana yang dimaksud. Jangan loop semua pos.\n"
            . "- Jika user tanya tentang cuaca umum → gunakan search_logger(keyword='cuaca') untuk cari pos AWS/AWR, lalu tanyakan pos mana.\n"
            . "- Jika user tanya tentang hujan di suatu wilayah → gunakan cek_hujan untuk status live, atau gunakan search_logger(keyword='hujan') untuk cari pos ARR/AWS.\n"
            . "- Jika user tanya tentang CURAH HUJAN HISTORIS di SEMUA pos pada tanggal tertentu → gunakan cek_hujan_historis.\n"
            . "- Jika user tanya tentang TMA/debit → gunakan search_logger(keyword='awlr') atau search_logger(keyword='TMA') untuk cari pos AWLR.\n"
            . "- JANGAN PERNAH loop panggil get_data_ringkasan untuk semua pos → token akan habis!\n\n"
            . "Konteks waktu saat ini:\n"
            . "- Sekarang: {$now} ({$hari})\n"
            . "- Hari ini: " . date('Y-m-d') . "\n"
            . "- Kemarin: " . date('Y-m-d', strtotime('-1 day')) . "\n"
            . "- Bulan ini: " . date('Y-m') . "\n"
            . "- Tahun ini: " . date('Y') . "\n\n"
            . "Panduan:\n"
            . "- Selalu jawab dalam Bahasa Indonesia yang ramah dan informatif.\n"
            . "- Gunakan tools/function yang tersedia untuk mengambil data aktual dari database.\n"
            . "- PENTING: Jika pengguna menyebut NAMA pos/lokasi (bukan ID angka), SELALU gunakan search_logger TERLEBIH DAHULU.\n"
            . "- PENTING: Jika pengguna menyebut referensi WAKTU/TANGGAL, SELALU gunakan resolve_date.\n"
            . "- cek_hujan HANYA untuk kondisi hujan SAAT INI / LIVE. JANGAN gunakan cek_hujan untuk data historis!\n"
            . "- Untuk data curah hujan HISTORIS di SEMUA pos pada tanggal tertentu → HARUS gunakan cek_hujan_historis.\n"
            . "- Untuk data curah hujan HISTORIS di 1 POS tertentu → gunakan get_data_ringkasan dengan parameter='hujan'.\n"
            . "- PENTING STRATEGI DATA HISTORIS:\n"
            . "  * Untuk semua data historis, SELALU gunakan get_data_ringkasan sebagai pilihan UTAMA.\n"
            . "  * Mode 1 hari: set tanggal='YYYY-MM-DD'.\n"
            . "  * Mode range (misal '7 hari terakhir'): gunakan resolve_date untuk dapat start/end, lalu set start dan end di get_data_ringkasan.\n"
            . "  * Mode bulanan: set bulan='YYYY-MM'.\n"
            . "  * Jika user hanya tanya SATU parameter → tambahkan filter parameter.\n"
            . "  * Gunakan get_data_analisa HANYA jika user minta data DETAIL PER-JAM untuk SATU parameter SPESIFIK.\n"
            . "  * JANGAN PERNAH memanggil get_data_analisa berulang kali untuk banyak parameter!\n"
            . "- Jika minta perbandingan, gunakan get_data_komparasi.\n\n"
            . "PENTING ALUR PERMINTAAN DATA:\n"
            . "- Jika user minta 'data pengukuran', 'data terkini', 'data pos X' → LANGSUNG panggil get_data_ringkasan (untuk hari ini). JANGAN tampilkan daftar parameter/detail teknis terlebih dahulu.\n"
            . "- JANGAN memanggil get_logger_parameter kecuali user SECARA EKSPLISIT minta 'daftar sensor' atau 'parameter apa saja'.\n"
            . "- JANGAN memanggil get_logger_detail kecuali user minta info teknis (serial number, IMEI, PIC, dll).\n"
            . "- Alur ideal: search_logger → LANGSUNG get_data_ringkasan. Jangan ada langkah perantara yang tidak perlu.\n"
            . "- Saat menampilkan data parameter, JANGAN tampilkan info teknis seperti tipe_graf, icon, kolom_sensor. Cukup tampilkan nama parameter, nilai, dan satuan.\n\n"
            . "- Format jawaban dengan rapi.\n"
            . "- Untuk data ringkasan SATU WAKTU dengan banyak parameter (misal data terkini pos X), gunakan format list/bullet BUKAN tabel. Contoh: '🌡️ Suhu: 24°C | 💧 Kelembapan: 87.5% | 💨 Angin: 0.03 Km'.\n"
            . "- Gunakan tabel markdown HANYA untuk data yang punya BANYAK BARIS (perbandingan antar waktu/tanggal/pos, data harian, data per-jam, daftar pos, hasil cek_hujan, hasil cek_hujan_historis).\n"
            . "- Setiap tabel otomatis mendapat tombol 'Download CSV'. JANGAN tawarkan ekspor data secara manual, cukup beri tahu user bahwa tombol download tersedia di bawah tabel.\n"
            . "- Saat menampilkan data, SELALU buat ringkasan singkat yang informatif.\n"
            . "- Untuk data hujan, gunakan klasifikasi yang sudah disediakan di response.\n\n"
            . "MENAMPILKAN GRAFIK:\n"
            . "- Jika user minta grafik/chart, atau jika data cocok ditampilkan sebagai grafik, gunakan format code block ```chart dengan JSON config.\n"
            . "- Format JSON: {\"type\":\"line\",\"title\":\"Judul\",\"yLabel\":\"Satuan\",\"labels\":[\"08:00\",\"09:00\",...],\"datasets\":[{\"label\":\"Nama\",\"data\":[1,2,...]}]}\n"
            . "- Gunakan type 'line' untuk semua parameter (suhu, kelembapan, angin, TMA, dll).\n"
            . "- Gunakan type 'bar' HANYA untuk curah hujan.\n"
            . "- labels berisi array waktu/tanggal, datasets berisi array data numerik.\n"
            . "- Contoh untuk curah hujan:\n"
            . "```chart\n"
            . "{\"type\":\"bar\",\"title\":\"Curah Hujan Harian\",\"yLabel\":\"mm\",\"labels\":[\"01 Mar\",\"02 Mar\"],\"datasets\":[{\"label\":\"mm\",\"data\":[5.2,12.4]}]}\n"
            . "```\n"
            . "- Contoh untuk suhu:\n"
            . "```chart\n"
            . "{\"type\":\"line\",\"title\":\"Temperatur Udara\",\"yLabel\":\"°C\",\"labels\":[\"06:00\",\"07:00\",\"08:00\"],\"datasets\":[{\"label\":\"°C\",\"data\":[22.5,23.1,24.0]}]}\n"
            . "```\n"
            . "- PENTING: Data chart HARUS dari function call, JANGAN mengarang data.\n"
            . "- Tampilkan grafik setelah tabel ringkasan, bukan sebagai pengganti tabel.\n\n"
            . "- Jika data tidak tersedia atau error, sampaikan dengan sopan.\n"
            . "- Jangan mengarang data, selalu ambil dari function yang tersedia.\n"
            . "- Tampilkan data numerik dengan format yang mudah dibaca.\n\n"
            . "BATASAN TOPIK (WAJIB DIPATUHI):\n"
            . "- Kamu HANYA boleh menjawab pertanyaan yang berkaitan dengan sistem monitoring BBWS Serayu Opak: data logger, curah hujan, tinggi muka air, debit, cuaca, status koneksi, sensor, pos monitoring, dan topik terkait.\n"
            . "- Jika pengguna bertanya di LUAR topik monitoring (misalnya: resep masakan, matematika, sejarah, coding, gosip, curhat, pengetahuan umum, politik, olahraga, dll), TOLAK dengan sopan.\n"
            . "- Contoh penolakan: 'Mohon maaf, saya hanya bisa membantu terkait data monitoring BBWS Serayu Opak seperti curah hujan, tinggi muka air, status logger, dan data sensor lainnya. Silakan tanyakan seputar topik tersebut 😊'\n"
            . "- JANGAN coba menjawab pertanyaan OOT meskipun kamu tahu jawabannya. Tetap tolak dengan sopan.\n";
    }

    // ─── OpenAI Tools Definition ───
    private function _openai_tools()
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cek_hujan',
                    'description' => 'Mengecek kondisi hujan/curah hujan SAAT INI (LIVE/REALTIME) di semua pos ARR dan AWS. JANGAN gunakan untuk data historis! Gunakan cek_hujan_historis untuk data tanggal tertentu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'filter' => [
                                'type' => 'string',
                                'enum' => ['hujan_saja', 'semua'],
                                'description' => 'Filter hasil: "hujan_saja" (default) hanya pos yang hujan, "semua" tampilkan semua pos'
                            ]
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'cek_hujan_historis',
                    'description' => 'Mengecek data curah hujan HISTORIS di semua pos ARR dan AWS pada tanggal tertentu. Mengembalikan akumulasi curah hujan harian dan klasifikasi untuk setiap pos. Gunakan jika pengguna bertanya hujan pada tanggal tertentu, misalnya "hujan kemarin", "curah hujan tanggal 28 Feb", "tampilkan curah hujan di seluruh pos tanggal X".',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tanggal' => [
                                'type' => 'string',
                                'description' => 'Tanggal yang ingin dicek dalam format YYYY-MM-DD, contoh: "2026-02-28"'
                            ],
                            'filter' => [
                                'type' => 'string',
                                'enum' => ['hujan_saja', 'semua'],
                                'description' => 'Filter hasil: "hujan_saja" (default) hanya pos yang hujan, "semua" tampilkan semua pos'
                            ]
                        ],
                        'required' => ['tanggal']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'resolve_date',
                    'description' => 'Menerjemahkan ekspresi tanggal natural language Bahasa Indonesia menjadi format tanggal terstruktur. WAJIB dipanggil jika pengguna menyebut referensi waktu seperti "hari ini", "kemarin", "7 hari terakhir", "bulan lalu", "minggu ini", "januari 2026", dll. Hasilnya digunakan sebagai parameter untuk get_data_analisa.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'text' => [
                                'type' => 'string',
                                'description' => 'Ekspresi tanggal dari pengguna, contoh: "hari ini", "kemarin", "7 hari terakhir", "bulan lalu", "minggu ini", "januari 2026", "1-15 maret 2026"'
                            ]
                        ],
                        'required' => ['text']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'search_logger',
                    'description' => 'Mencari logger/pos monitoring berdasarkan nama atau lokasi. Gunakan ini TERLEBIH DAHULU jika pengguna menyebut nama pos (bukan ID angka). Mengembalikan daftar logger yang cocok beserta id_logger-nya.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Kata kunci nama pos atau lokasi, contoh: "Seturan", "Pejengkolan", "Kali Meneng", "AWR Kaliurang"'
                            ],
                            'kategori' => [
                                'type' => 'string',
                                'enum' => ['aws', 'awr', 'arr', 'awlr', 'klimatologi', 'afmr'],
                                'description' => 'Opsional: soft filter kategori. "aws" atau "awr" = cuaca (sama), "arr" = hujan, "awlr" = TMA/debit. PENTING: "awr" dan "aws" itu SAMA.'
                            ]
                        ],
                        'required' => ['keyword']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_logger_list',
                    'description' => 'Mendapatkan daftar semua pos monitoring (logger) beserta statusnya. Bisa difilter berdasarkan kategori.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'kategori' => [
                                'type' => 'string',
                                'enum' => ['all', 'arr', 'awlr', 'aws', 'awr', 'afmr'],
                                'description' => 'Filter kategori. "all" untuk semua, "arr" untuk curah hujan, "awlr" untuk tinggi muka air, "aws"/"awr" untuk cuaca (sama), "afmr" untuk debit.'
                            ]
                        ],
                        'required' => []
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_logger_detail',
                    'description' => 'Mendapatkan detail informasi satu logger/pos monitoring tertentu berdasarkan ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger, contoh: "10063"'
                            ]
                        ],
                        'required' => ['id_logger']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_logger_parameter',
                    'description' => 'Mendapatkan daftar parameter sensor yang dimiliki oleh satu logger.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger'
                            ]
                        ],
                        'required' => ['id_logger']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_logger_koneksi',
                    'description' => 'Mengecek status koneksi (online/offline/perbaikan) dari satu logger.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger'
                            ]
                        ],
                        'required' => ['id_logger']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_data_realtime',
                    'description' => 'Mendapatkan data terbaru (realtime) dari sensor di satu logger. Mode "last" untuk satu data terakhir, "live" untuk 25 data terakhir.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger'
                            ],
                            'mode' => [
                                'type' => 'string',
                                'enum' => ['last', 'live'],
                                'description' => 'Mode: "last" untuk data terakhir saja, "live" untuk 25 data terbaru'
                            ],
                            'id_sensor' => [
                                'type' => 'string',
                                'description' => 'ID sensor (hanya diperlukan untuk mode "live")'
                            ]
                        ],
                        'required' => ['id_logger']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_data_ringkasan',
                    'description' => 'Mendapatkan RINGKASAN (rata-rata, min, max, total) SEMUA parameter sensor dari satu logger. Mendukung 3 mode: (1) satu hari via tanggal, (2) range via start+end, (3) bulanan via bulan. GUNAKAN INI sebagai pilihan UTAMA saat user minta data historis. Bisa juga filter parameter tertentu saja (misal hanya curah hujan).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger'
                            ],
                            'tanggal' => [
                                'type' => 'string',
                                'description' => 'Untuk data 1 hari (YYYY-MM-DD). Default: hari ini.'
                            ],
                            'start' => [
                                'type' => 'string',
                                'description' => 'Tanggal awal range (YYYY-MM-DD). Gunakan bersama end.'
                            ],
                            'end' => [
                                'type' => 'string',
                                'description' => 'Tanggal akhir range (YYYY-MM-DD). Gunakan bersama start.'
                            ],
                            'bulan' => [
                                'type' => 'string',
                                'description' => 'Untuk data 1 bulan (YYYY-MM). Contoh: 2026-02.'
                            ],
                            'parameter' => [
                                'type' => 'string',
                                'description' => 'Opsional: filter nama parameter. Contoh: "hujan", "suhu", "angin". Jika tidak diisi, semua parameter ditampilkan.'
                            ]
                        ],
                        'required' => ['id_logger']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_data_analisa',
                    'description' => 'Mendapatkan data analisa historis DETAIL PER-JAM dari SATU sensor spesifik. HANYA gunakan jika user secara eksplisit minta data per-jam untuk satu parameter tertentu. Untuk ringkasan umum, gunakan get_data_ringkasan.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id_logger' => [
                                'type' => 'string',
                                'description' => 'ID unik logger'
                            ],
                            'id_sensor' => [
                                'type' => 'string',
                                'description' => 'ID parameter sensor'
                            ],
                            'granularity' => [
                                'type' => 'string',
                                'enum' => ['day', 'month', 'year', 'range'],
                                'description' => 'Granularity analisa'
                            ],
                            'tanggal' => [
                                'type' => 'string',
                                'description' => 'Tanggal (YYYY-MM-DD) untuk granularity "day"'
                            ],
                            'bulan' => [
                                'type' => 'string',
                                'description' => 'Bulan (YYYY-MM) untuk granularity "month"'
                            ],
                            'tahun' => [
                                'type' => 'string',
                                'description' => 'Tahun (YYYY) untuk granularity "year"'
                            ],
                            'start' => [
                                'type' => 'string',
                                'description' => 'Tanggal mulai (YYYY-MM-DD) untuk granularity "range"'
                            ],
                            'end' => [
                                'type' => 'string',
                                'description' => 'Tanggal akhir (YYYY-MM-DD) untuk granularity "range"'
                            ]
                        ],
                        'required' => ['id_logger', 'id_sensor', 'granularity']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_data_komparasi',
                    'description' => 'Membandingkan data dari beberapa logger sekaligus pada tanggal tertentu.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'loggers' => [
                                'type' => 'array',
                                'items' => ['type' => 'string'],
                                'description' => 'Array ID logger yang akan dibandingkan'
                            ],
                            'tanggal' => [
                                'type' => 'string',
                                'description' => 'Tanggal perbandingan (YYYY-MM-DD)'
                            ]
                        ],
                        'required' => ['loggers']
                    ]
                ]
            ]
        ];
    }

    // ─── Execute tool internally (calls the existing methods via output buffer) ───
    private function _execute_tool($fn_name, $args)
    {
        // Map tool names to internal methods and their expected input
        $map = [
            'cek_hujan' => 'cek_hujan',
            'cek_hujan_historis' => 'cek_hujan_historis',
            'resolve_date' => 'resolve_date',
            'search_logger' => 'search_logger',
            'get_logger_list' => 'logger_list',
            'get_logger_detail' => 'logger_detail',
            'get_logger_parameter' => 'logger_parameter',
            'get_logger_koneksi' => 'logger_koneksi',
            'get_data_realtime' => 'data_realtime',
            'get_data_ringkasan' => 'data_ringkasan',
            'get_data_analisa' => 'data_analisa',
            'get_data_komparasi' => 'data_komparasi',
        ];

        if (!isset($map[$fn_name])) {
            return ['status' => 'error', 'message' => "Fungsi {$fn_name} tidak dikenali"];
        }

        $method = $map[$fn_name];

        // Override php://input by using a temporary stream for the method
        // We call the method via a helper that fakes the JSON input
        $result = $this->_call_internal($method, $args);

        return $result;
    }

    // ─── Call an internal method, capturing its JSON output ───
    private function _call_internal($method, $args)
    {
        // Store the original input
        $GLOBALS['_chatbot_fake_input'] = json_encode($args);

        // Temporarily override _json_input behavior
        // We use output buffering to capture the echo'd JSON from the method
        ob_start();

        // Swap _json_input temporarily — we override via a flag
        $this->_fake_input = $args;
        $this->_use_fake = true;

        $this->$method();

        $this->_use_fake = false;

        $output = ob_get_clean();

        // Remove any extra headers that were set
        if (!headers_sent()) {
            header_remove('Content-Type');
        }

        $decoded = json_decode($output, true);
        return $decoded ?: ['raw' => $output];
    }

    // Override _json_input to support internal calls
    // (We need to re-check — the original is already defined above,
    //  so we wrap it in the chat flow using a flag)

    // ─── Call OpenAI Chat Completions API ───
    private function _call_openai($api_key, $model, $messages, $tools)
    {
        $payload = [
            'model' => $model,
            'messages' => $messages,
            'tools' => $tools,
            'max_completion_tokens' => 4096,
        ];

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $api_key,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE),
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            log_message('error', 'OpenAI cURL error: ' . $err);
            // Return error with detail so chat() can show it
            return ['_error' => 'cURL error: ' . $err];
        }

        log_message('debug', 'OpenAI HTTP ' . $http_code . ' response: ' . substr($response, 0, 500));

        $decoded = json_decode($response, true);

        if (isset($decoded['error'])) {
            $err_msg = $decoded['error']['message'] ?? json_encode($decoded['error']);
            log_message('error', 'OpenAI API error (HTTP ' . $http_code . '): ' . $err_msg);
            return ['_error' => 'OpenAI: ' . $err_msg];
        }

        // Detect truncated response (finish_reason == 'length')
        $finish_reason = $decoded['choices'][0]['finish_reason'] ?? null;
        if ($finish_reason === 'length') {
            log_message('warning', 'OpenAI response truncated (finish_reason=length)');
            // Still return the partial response — let chat() handle it
            // The content may be incomplete but at least won't crash
        }

        return $decoded;
    }

    // ═══════════════════════════════════════════
    // CEK HUJAN — rainfall status across ARR/AWS
    // ═══════════════════════════════════════════
    public function cek_hujan()
    {
        $input = $this->_json_input();
        $filter = isset($input['filter']) ? $input['filter'] : 'hujan_saja';

        // Only query ARR & AWS categories
        $query_kat = $this->db->query(
            "SELECT * FROM kategori_logger WHERE view = 1 AND (controller = 'awr' OR controller = 'arr')"
        );

        $pos_hujan = [];
        $pos_tidak_hujan = 0;
        $pos_offline = 0;
        $pos_perbaikan = 0;
        $total_pos = 0;

        foreach ($query_kat->result() as $kat) {
            $tabel_temp = $kat->temp_data;

            $query_logger = $this->db->query("
                SELECT t_logger.*, t_lokasi.nama_lokasi, t_lokasi.latitude, t_lokasi.longitude
                FROM t_logger
                INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi
                WHERE kategori_log = '{$kat->id_katlogger}'
            ");

            foreach ($query_logger->result() as $lok) {
                $total_pos++;
                $id_logger = $lok->id_logger;
                $tabel_main = $lok->tabel_main;

                // Cek perbaikan
                $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;
                if ($is_perbaikan) {
                    $pos_perbaikan++;
                    continue;
                }

                // Cek koneksi
                $temp = $this->db->where('code_logger', $id_logger)->get($tabel_temp)->row();
                $waktu = $temp ? $temp->waktu : null;
                $kn = $this->_cek_koneksi($waktu);

                if ($kn !== 'On') {
                    $pos_offline++;
                    continue;
                }

                // Get primary rainfall sensor
                $p_utama = $this->db
                    ->where('logger_id', $id_logger)
                    ->where('parameter_utama', '1')
                    ->get('parameter_sensor')
                    ->row();

                if (!$p_utama)
                    continue;

                $kolom = $p_utama->kolom_sensor;

                // Hourly accumulation (current hour)
                $akum_jam = $this->db->query(
                    "SELECT SUM({$kolom}) as val FROM {$tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d H') . ":00'"
                )->row();
                $val_jam = $akum_jam->val ?? 0;

                // Daily accumulation (today)
                $akum_hari = $this->db->query(
                    "SELECT SUM({$kolom}) as val FROM {$tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d') . " 00:00'"
                )->row();
                $val_hari = $akum_hari->val ?? 0;

                $klas_jam = $this->_klasifikasi_hujan_jam($val_jam);
                $klas_hari = $this->_klasifikasi_hujan_harian($val_hari);
                $is_hujan = ($val_jam > 0);

                if ($is_hujan) {
                    $pos_hujan[] = [
                        'id_logger' => $id_logger,
                        'lokasi' => $lok->nama_lokasi,
                        'curah_hujan_jam' => number_format($val_jam, 2, '.', ''),
                        'klasifikasi_jam' => $klas_jam,
                        'curah_hujan_harian' => number_format($val_hari, 2, '.', ''),
                        'klasifikasi_harian' => $klas_hari,
                        'waktu_terakhir' => $waktu
                    ];
                } else {
                    if ($filter === 'semua') {
                        $pos_hujan[] = [
                            'id_logger' => $id_logger,
                            'lokasi' => $lok->nama_lokasi,
                            'curah_hujan_jam' => '0.00',
                            'klasifikasi_jam' => $klas_jam,
                            'curah_hujan_harian' => number_format($val_hari, 2, '.', ''),
                            'klasifikasi_harian' => $klas_hari,
                            'waktu_terakhir' => $waktu
                        ];
                    }
                    $pos_tidak_hujan++;
                }
            }
        }

        // ── Merge rain data from PSDA API ──
        $local_ids = [];
        $query_local_ids = $this->db->query("SELECT id_logger FROM t_logger");
        foreach ($query_local_ids->result() as $r) {
            $local_ids[] = $r->id_logger;
        }

        // Always fetch ALL PSDA stations to count total_pos correctly
        $psda_url = "https://dpupesdm.monitoring4system.com/integrasi/cek_hujan?filter=semua";
        $psda_json = @file_get_contents($psda_url);
        $psda_data = @json_decode($psda_json, true);

        if (!empty($psda_data['data'])) {
            foreach ($psda_data['data'] as $p) {
                $pid = $p['id_logger'] ?? '';
                if (in_array($pid, $local_ids))
                    continue; // skip local loggers

                $total_pos++;
                $val_jam = floatval($p['curah_hujan_jam'] ?? 0);
                $val_hari = floatval($p['curah_hujan_harian'] ?? 0);
                $is_hujan = ($val_jam > 0);
                $koneksi = $p['status_koneksi'] ?? 'On';

                if ($koneksi !== 'On') {
                    $pos_offline++;
                    continue;
                }

                if ($is_hujan || $filter === 'semua') {
                    $pos_hujan[] = [
                        'id_logger' => $pid,
                        'lokasi' => $p['lokasi'] ?? '',
                        'curah_hujan_jam' => number_format($val_jam, 2, '.', ''),
                        'klasifikasi_jam' => $this->_klasifikasi_hujan_jam($val_jam),
                        'curah_hujan_harian' => number_format($val_hari, 2, '.', ''),
                        'klasifikasi_harian' => $this->_klasifikasi_hujan_harian($val_hari),
                        'waktu_terakhir' => $p['waktu_terakhir'] ?? null
                    ];
                }
                if (!$is_hujan)
                    $pos_tidak_hujan++;
            }
        }

        // Sort by heaviest rain first
        usort($pos_hujan, function ($a, $b) {
            return (float) $b['curah_hujan_jam'] <=> (float) $a['curah_hujan_jam'];
        });

        $jml_hujan = count(array_filter($pos_hujan, function ($p) {
            return (float) $p['curah_hujan_jam'] > 0;
        }));

        $this->_json_response([
            'status' => 'sukses',
            'waktu' => date('Y-m-d H:i'),
            'ringkasan' => "{$jml_hujan} dari {$total_pos} pos mendeteksi hujan saat ini",
            'total_pos' => $total_pos,
            'pos_hujan' => $jml_hujan,
            'pos_tidak_hujan' => $pos_tidak_hujan,
            'pos_offline' => $pos_offline,
            'pos_perbaikan' => $pos_perbaikan,
            'data' => $pos_hujan
        ]);
    }

    // ═══════════════════════════════════════════
    // CEK HUJAN HISTORIS — historical rainfall across all stations
    // ═══════════════════════════════════════════
    public function cek_hujan_historis()
    {
        $input = $this->_json_input();
        $tanggal = isset($input['tanggal']) ? $input['tanggal'] : null;
        $filter = isset($input['filter']) ? $input['filter'] : 'hujan_saja';

        if (!$tanggal) {
            return $this->_json_response(['status' => 'error', 'message' => 'tanggal wajib diisi (format: YYYY-MM-DD)']);
        }

        $pos_data = [];
        $total_pos = 0;

        // ── 1) Query local BBWS loggers (ARR & AWS) ──
        $query_kat = $this->db->query(
            "SELECT * FROM kategori_logger WHERE view = 1 AND (controller = 'awr' OR controller = 'arr')"
        );

        $local_ids = [];
        foreach ($query_kat->result() as $kat) {
            $query_logger = $this->db->query("
                SELECT t_logger.*, t_lokasi.nama_lokasi, t_lokasi.latitude, t_lokasi.longitude
                FROM t_logger
                INNER JOIN t_lokasi ON t_logger.lokasi_logger = t_lokasi.idlokasi
                WHERE kategori_log = '{$kat->id_katlogger}'
            ");

            foreach ($query_logger->result() as $lok) {
                $total_pos++;
                $id_logger = $lok->id_logger;
                $local_ids[] = $id_logger;
                $tabel_main = $lok->tabel_main;

                // Get primary rainfall sensor
                $p_utama = $this->db
                    ->where('logger_id', $id_logger)
                    ->where('parameter_utama', '1')
                    ->get('parameter_sensor')
                    ->row();

                if (!$p_utama)
                    continue;

                $kolom = $p_utama->kolom_sensor;

                // Daily accumulation for the given date
                $akum = $this->db->query(
                    "SELECT SUM({$kolom}) as val FROM {$tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '{$tanggal} 00:00' AND waktu <= '{$tanggal} 23:59'"
                )->row();
                $val = floatval($akum->val ?? 0);

                $klas = $this->_klasifikasi_hujan_harian($val);
                $is_hujan = ($val > 0);

                if ($is_hujan || $filter === 'semua') {
                    $pos_data[] = [
                        'pos' => $lok->nama_logger,
                        'mm' => number_format($val, 2, '.', ''),
                        'klasifikasi' => $klas
                    ];
                }
            }
        }

        // Always fetch ALL PSDA stations to count total_pos correctly
        $psda_url = "https://dpupesdm.monitoring4system.com/integrasi/cek_hujan_historis?tanggal={$tanggal}&filter=semua";
        $psda_json = @file_get_contents($psda_url);
        $psda_data = @json_decode($psda_json, true);

        if (!empty($psda_data['data'])) {
            foreach ($psda_data['data'] as $p) {
                $pid = $p['id_logger'] ?? '';
                if (in_array($pid, $local_ids))
                    continue;

                $total_pos++;
                $val = floatval($p['curah_hujan_harian'] ?? 0);
                $klas = $this->_klasifikasi_hujan_harian($val);
                $is_hujan = ($val > 0);

                if ($is_hujan || $filter === 'semua') {
                    $pos_data[] = [
                        'pos' => $p['lokasi'] ?? '',
                        'mm' => number_format($val, 2, '.', ''),
                        'klasifikasi' => $klas
                    ];
                }
            }
        }

        // Sort by heaviest rain first
        usort($pos_data, function ($a, $b) {
            return (float) $b['mm'] <=> (float) $a['mm'];
        });

        $jml_hujan = count(array_filter($pos_data, function ($p) {
            return (float) $p['mm'] > 0;
        }));

        $this->_json_response([
            'status' => 'sukses',
            'tanggal' => $tanggal,
            'ringkasan' => "{$jml_hujan} dari {$total_pos} pos mendeteksi hujan pada tanggal {$tanggal}",
            'total_pos' => $total_pos,
            'pos_hujan' => $jml_hujan,
            'data' => $pos_data
        ]);
    }

    // ═══════════════════════════════════════════
    // RESOLVE DATE — natural language date parser
    // ═══════════════════════════════════════════
    public function resolve_date()
    {
        $input = $this->_json_input();
        $text = isset($input['text']) ? trim($input['text']) : '';

        if ($text === '') {
            return $this->_json_response(['status' => 'error', 'message' => 'text wajib diisi']);
        }

        $result = $this->dateresolver->resolve($text);
        $this->_json_response($result);
    }

    // ═══════════════════════════════════════════
    // SEARCH LOGGER — fuzzy name search (native PHP, no dependency)
    // ═══════════════════════════════════════════
    public function search_logger()
    {
        $input    = $this->_json_input();
        $keyword  = isset($input['keyword'])  ? trim($input['keyword'])           : '';
        $kategori = isset($input['kategori']) ? strtolower(trim($input['kategori'])) : '';

        if ($keyword === '') {
            return $this->_json_response(['status' => 'error', 'message' => 'keyword wajib diisi']);
        }

        // Normalise search term: gabungkan kategori + keyword jika ada
        $search_term = ($kategori !== '') ? $kategori . ' ' . $keyword : $keyword;
        $search_term = strtolower($search_term);

        // Strip spasi/tanda baca untuk matching: "wadas lintang" → "wadaslintang"
        $search_stripped = preg_replace('/[\s_\-\.]+/', '', $search_term);

        // Pecah jadi kata-kata (min 2 karakter)
        $words = array_filter(explode(' ', $search_term), function ($w) {
            return strlen($w) >= 2;
        });

        // Build flat list
        $mapping = $this->_load_logger_mapping();
        $scored  = [];

        foreach ($mapping as $cat) {
            $cat_name = $cat['nama_kategori'];
            $is_bbws  = true;

            foreach ($cat['logger'] as $l) {
                $nama    = $l['nama_lokasi'] ?? $l['nama_logger'] ?? '';
                $is_psda = !empty($l['status_aset']);

                $lokasi_lower    = strtolower($nama);
                $lokasi_stripped = preg_replace('/[\s_\-\.]+/', '', $lokasi_lower);
                $kat_lower       = strtolower($cat_name);

                // ── Scoring ──
                $score = 0;

                // 1) Exact substring match di lokasi (bobot tinggi)
                if (strpos($lokasi_lower, $search_term) !== false) {
                    $score += 100;
                }
                // 2) Exact substring di lokasi_stripped (untuk "wadaslintang" → "wadas lintang")
                if (strpos($lokasi_stripped, $search_stripped) !== false) {
                    $score += 90;
                }
                // 3) Per-kata match
                foreach ($words as $word) {
                    if (strpos($lokasi_lower, $word) !== false) {
                        $score += 30;
                    }
                    if (strpos($kat_lower, $word) !== false) {
                        $score += 10;
                    }
                    // Stripped match (kata tanpa spasi)
                    $word_stripped = preg_replace('/[\s_\-\.]+/', '', $word);
                    if ($word_stripped !== $word && strpos($lokasi_stripped, $word_stripped) !== false) {
                        $score += 20;
                    }
                }
                // 4) Similar_text fallback (fuzzy)
                similar_text($search_term, $lokasi_lower, $pct);
                if ($pct >= 40) {
                    $score += (int)($pct * 0.5);
                }

                if ($score > 0) {
                    $scored[] = [
                        'score'      => $score,
                        'id_logger'  => $l['id_logger'] ?? '',
                        'nama_logger'=> $nama,
                        'lokasi'     => $nama,
                        'kategori'   => $cat_name . ($is_psda ? ' (PSDA)' : ' (BBWS)'),
                        'latitude'   => $l['latitude'] ?? '',
                        'longitude'  => $l['longitude'] ?? '',
                        'relevance'  => 0,
                    ];
                }
            }
        }

        // Sort by score descending
        usort($scored, function ($a, $b) { return $b['score'] - $a['score']; });

        // Format results (limit 15), normalise relevance to 0-100
        $max_score = !empty($scored) ? $scored[0]['score'] : 1;
        $data = [];
        foreach (array_slice($scored, 0, 15) as $r) {
            $r['relevance'] = round(($r['score'] / $max_score) * 100, 1);
            unset($r['score']);
            $data[] = $r;
        }

        $this->_json_response([
            'status'  => 'sukses',
            'keyword' => $keyword,
            'total'   => count($data),
            'data'    => $data
        ]);
    }


    // ─── Helper: cek koneksi (1 jam terakhir) ───
    private function _cek_koneksi($waktu)
    {
        $batas = date('Y-m-d H:i:s', strtotime('-1 hour'));
        return ($waktu >= $batas) ? 'On' : 'Off';
    }

    // ─── Helper: klasifikasi curah hujan PER JAM ───
    private function _klasifikasi_hujan_jam($mm)
    {
        if ($mm <= 0)
            return 'Berawan / Tidak Hujan';
        if ($mm <= 1)
            return 'Hujan Sangat Ringan';
        if ($mm <= 5)
            return 'Hujan Ringan';
        if ($mm <= 10)
            return 'Hujan Sedang';
        if ($mm <= 20)
            return 'Hujan Lebat';
        return 'Hujan Sangat Lebat';
    }

    // ─── Helper: klasifikasi curah hujan PER HARI ───
    private function _klasifikasi_hujan_harian($mm)
    {
        if ($mm <= 0)
            return 'Berawan / Tidak Hujan';
        if ($mm <= 10)
            return 'Hujan Sangat Ringan';
        if ($mm <= 20)
            return 'Hujan Ringan';
        if ($mm <= 50)
            return 'Hujan Sedang';
        if ($mm <= 100)
            return 'Hujan Lebat';
        return 'Hujan Sangat Lebat';
    }

    // ═══════════════════════════════════════════
    // 1) logger_list — daftar semua pos + status
    // ═══════════════════════════════════════════
    public function logger_list()
    {
        $input = $this->_json_input();
        $kategori = isset($input['kategori']) ? strtolower(trim($input['kategori'])) : 'all';

        // Normalize: AWR = AWS (beda istilah PSDA vs BBWS)
        if ($kategori === 'awr')
            $kategori = 'aws';

        $mapping = $this->_load_logger_mapping();
        $data = [];

        foreach ($mapping as $cat) {
            $cat_name = $cat['nama_kategori'];

            // Category filter — match by nama_kategori
            if ($kategori !== 'all' && stripos($cat_name, $kategori) === false) {
                continue;
            }

            foreach ($cat['logger'] as $l) {
                $is_psda = !empty($l['status_aset']);
                $status = $l['status_logger'] ?? '';

                if (stripos($status, 'Perbaikan') !== false) {
                    $kn = 'perbaikan';
                } elseif (stripos($status, 'Terhubung') !== false) {
                    $kn = 'On';
                } else {
                    $kn = 'Off';
                }

                $data[] = [
                    'id_logger' => $l['id_logger'] ?? '',
                    'nama' => $l['nama_logger'] ?? $l['nama_lokasi'] ?? '',
                    'kategori' => $cat_name . ($is_psda ? ' (PSDA)' : ' (BBWS)'),
                    'koneksi' => $kn
                ];
            }
        }

        $this->_json_response([
            'status' => 'sukses',
            'total' => count($data),
            'data' => $data
        ]);
    }

    // ═══════════════════════════════════════════
    // 2) logger_detail — detail 1 logger
    // ═══════════════════════════════════════════
    public function logger_detail()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $info = $this->db->where('logger_id', $id_logger)->get('t_informasi')->row();
        if (!$info) {
            // ── PSDA fallback: cari info dari API PSDA ──
            $psda_data = $this->_psda_find_logger($id_logger);
            if ($psda_data) {
                return $this->_json_response([
                    'status' => 'sukses',
                    'sumber' => 'PSDA',
                    'data' => [
                        'id_logger' => $psda_data['id_logger'],
                        'nama_logger' => $psda_data['nama_logger'],
                        'lokasi' => $psda_data['lokasi'],
                        'kategori' => $psda_data['kategori'],
                        'latitude' => $psda_data['latitude'],
                        'longitude' => $psda_data['longitude'],
                        'keterangan' => 'Data dari pos PSDA'
                    ]
                ]);
            }
            return $this->_json_response(['status' => 'error', 'message' => 'Logger tidak ditemukan']);
        }

        // cek status SD & sensor
        $logger = $this->db->where('id_logger', $id_logger)->get('t_logger')->row();
        $kategori = $this->db->where('id_katlogger', $logger->kategori_log)->get('kategori_logger')->row();
        $status_sd = 'OK';

        if ($kategori) {
            $cek = $this->db->query("SELECT sensor13, sensor12 FROM {$kategori->temp_data} WHERE code_logger = '{$id_logger}' ORDER BY waktu DESC LIMIT 1")->row();
            if ($cek) {
                $status_sd = ($cek->sensor13 == '1') ? 'OK' : 'Terjadi Kesalahan';
            }
        }

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'id_logger' => $info->logger_id,
                'seri' => $info->seri_logger,
                'serial_number' => $info->serial_number,
                'sensor' => $info->sensor,
                'elevasi' => $info->elevasi ?? null,
                'awal_kontrak' => $info->awal_kontrak,
                'akhir_garansi' => $info->garansi,
                'logger_aktif' => $info->tanggal_pemasangan,
                'no_seluler' => $info->nosell,
                'imei' => $info->imei,
                'nama_pic' => $info->nama_pic,
                'no_pic' => $info->no_pic,
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // 3) logger_parameter — parameter sensor 1 logger
    // ═══════════════════════════════════════════
    public function logger_parameter()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $params = $this->db->where('logger_id', $id_logger)->get('parameter_sensor')->result();
        $data = [];
        foreach ($params as $p) {
            $data[] = [
                'id_param' => $p->id_param,
                'nama_parameter' => $p->nama_parameter,
                'kolom_sensor' => $p->kolom_sensor,
                'satuan' => $p->satuan,
                'tipe_graf' => $p->tipe_graf,
                'icon' => $p->icon_app
            ];
        }

        // ── PSDA fallback: ambil parameter dari API PSDA ──
        if (empty($data)) {
            $psda_params = $this->_psda_get_parameters($id_logger);
            if (!empty($psda_params)) {
                return $this->_json_response([
                    'status' => 'sukses',
                    'sumber' => 'PSDA',
                    'data' => $psda_params
                ]);
            }
        }

        $this->_json_response([
            'status' => 'sukses',
            'data' => $data
        ]);
    }

    // ═══════════════════════════════════════════
    // 4) logger_koneksi — status online/offline
    // ═══════════════════════════════════════════
    public function logger_koneksi()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $logger = $this->db
            ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
            ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
            ->where('t_logger.id_logger', $id_logger)
            ->get('t_logger')->row();

        if (!$logger) {
            // ── PSDA fallback: cek koneksi via API PSDA ──
            $psda_rt = $this->_psda_get_realtime($id_logger);
            if ($psda_rt) {
                $waktu = $psda_rt['waktu'] ?? null;
                $kn = $this->_cek_koneksi($waktu);
                return $this->_json_response([
                    'status' => 'sukses',
                    'sumber' => 'PSDA',
                    'data' => [
                        'id_logger' => $id_logger,
                        'nama_lokasi' => $psda_rt['nama_logger'] ?? '',
                        'koneksi' => $kn,
                        'keterangan' => ($kn === 'On') ? 'Koneksi Terhubung' : 'Koneksi Terputus',
                        'waktu_terakhir' => $waktu,
                        'is_perbaikan' => false
                    ]
                ]);
            }
            return $this->_json_response(['status' => 'error', 'message' => 'Logger tidak ditemukan']);
        }

        $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;

        if ($is_perbaikan) {
            return $this->_json_response([
                'status' => 'sukses',
                'data' => [
                    'id_logger' => $id_logger,
                    'nama_lokasi' => $logger->nama_lokasi,
                    'koneksi' => 'perbaikan',
                    'keterangan' => 'Sedang Perbaikan',
                    'waktu_terakhir' => null,
                    'is_perbaikan' => true
                ]
            ]);
        }

        $temp = $this->db->where('code_logger', $id_logger)->get($logger->temp_data)->row();
        $waktu = $temp ? $temp->waktu : null;
        $kn = $this->_cek_koneksi($waktu);

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'id_logger' => $id_logger,
                'nama_lokasi' => $logger->nama_lokasi,
                'koneksi' => $kn,
                'keterangan' => ($kn === 'On') ? 'Koneksi Terhubung' : 'Koneksi Terputus',
                'waktu_terakhir' => $waktu,
                'is_perbaikan' => false
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // PSDA HELPER METHODS
    // ═══════════════════════════════════════════

    /**
     * Load logger_mapping.json and cache in memory.
     * Returns the full mapping array.
     */
    private $_logger_mapping_cache = null;
    private function _load_logger_mapping()
    {
        if ($this->_logger_mapping_cache !== null) {
            return $this->_logger_mapping_cache;
        }
        $path = FCPATH . 'logger_mapping.json';
        if (!file_exists($path)) {
            $this->_logger_mapping_cache = [];
            return [];
        }
        $this->_logger_mapping_cache = json_decode(file_get_contents($path), true) ?: [];
        return $this->_logger_mapping_cache;
    }

    /**
     * Find a logger by ID in logger_mapping.json.
     * Returns the logger data with parent category info, or null.
     */
    private function _mapping_find_logger($id_logger)
    {
        $mapping = $this->_load_logger_mapping();
        foreach ($mapping as $cat) {
            foreach ($cat['logger'] as $l) {
                if ((string) ($l['id_logger'] ?? '') === (string) $id_logger) {
                    $l['_kategori_nama'] = $cat['nama_kategori'];
                    $l['_kategori_id'] = $cat['id_katlogger'];
                    $l['_temp_data'] = $cat['temp_data'];
                    $l['_tabel'] = $cat['tabel'];
                    $l['_is_psda'] = !empty($l['status_aset']);
                    return $l;
                }
            }
        }
        return null;
    }

    /**
     * Get realtime data from PSDA API.
     * Uses logger_mapping.json to determine the correct temp table first.
     * Falls back to trying both tables if logger not found in mapping.
     */
    private function _psda_get_realtime($id_logger)
    {
        // Check mapping to determine correct table
        $mapped = $this->_mapping_find_logger($id_logger);
        if ($mapped) {
            $temp = $mapped['temp_tabel'] ?? $mapped['_temp_data'] ?? '';
            if ($temp) {
                $url = "https://dpupesdm.monitoring4system.com/api/dtakhir?idlogger={$id_logger}&tabel={$temp}";
                $out = @json_decode(@file_get_contents($url), true);
                if (!empty($out) && !empty($out['data_terakhir'])) {
                    return $out;
                }
            }
        }

        // Fallback: try both tables
        $tables = ['temp_weather_station', 'temp_awlr'];
        foreach ($tables as $tbl) {
            $url = "https://dpupesdm.monitoring4system.com/api/dtakhir?idlogger={$id_logger}&tabel={$tbl}";
            $out = @json_decode(@file_get_contents($url), true);
            if (!empty($out) && !empty($out['data_terakhir'])) {
                return $out;
            }
        }
        return null;
    }

    /**
     * Find a logger in data sources (local mapping first, then PSDA API fallback).
     * Returns ['id_logger', 'nama_logger', 'lokasi', 'kategori', ...] or null.
     */
    private function _psda_find_logger($id_logger)
    {
        $mapped = $this->_mapping_find_logger($id_logger);
        if ($mapped) {
            $is_psda = !empty($mapped['status_aset']);
            return [
                'id_logger' => $mapped['id_logger'],
                'nama_logger' => $mapped['nama_logger'] ?? $mapped['nama_lokasi'] ?? '',
                'lokasi' => $mapped['nama_lokasi'] ?? '',
                'kategori' => $mapped['_kategori_nama'] . ($is_psda ? ' (PSDA)' : ' (BBWS)'),
                'latitude' => $mapped['latitude'] ?? '',
                'longitude' => $mapped['longitude'] ?? '',
                'das' => $mapped['das'] ?? '',
                'status_aset' => $mapped['status_aset'] ?? 'BBWS',
                'status_logger' => $mapped['status_logger'] ?? '',
                'temp_tabel' => $mapped['temp_tabel'] ?? $mapped['_temp_data'] ?? '',
                'is_psda' => $is_psda,
            ];
        }
        return null;
    }

    /**
     * Get parameters for a logger from local mapping.
     * Falls back to PSDA realtime API if not found.
     */
    private function _psda_get_parameters($id_logger)
    {
        // First try local mapping
        $mapped = $this->_mapping_find_logger($id_logger);
        if ($mapped && !empty($mapped['param'])) {
            $params = [];
            foreach ($mapped['param'] as $p) {
                // Handle both PSDA and BBWS param formats
                $params[] = [
                    'id_param' => $p['id_param'] ?? $p['id'] ?? '',
                    'nama_parameter' => $p['nama_parameter'] ?? $p['alias_sensor'] ?? '',
                    'kolom_sensor' => $p['kolom_sensor'] ?? $p['field_sensor'] ?? '',
                    'satuan' => $p['satuan'] ?? '',
                    'tipe_graf' => $p['tipe_graf'] ?? 'spline',
                    'icon' => $p['icon_app'] ?? $p['icon_sensor'] ?? '',
                ];
            }
            return $params;
        }

        // Fallback: try PSDA realtime API
        $rt = $this->_psda_get_realtime($id_logger);
        if (!empty($rt['data_terakhir'])) {
            $params = [];
            foreach ($rt['data_terakhir'] as $s) {
                $params[] = [
                    'id_param' => $s['idsensor'] ?? $s['id_param'] ?? '',
                    'nama_parameter' => $s['sensor'] ?? $s['nama_parameter'] ?? '',
                    'satuan' => $s['satuan'] ?? '',
                    'tipe_graf' => $s['tipe_graf'] ?? 'spline',
                    'icon' => $s['icon'] ?? $s['icon_app'] ?? ''
                ];
            }
            return $params;
        }
        return [];
    }

    // ═══════════════════════════════════════════
    // 4b) data_ringkasan — ringkasan SEMUA parameter dalam 1 call
    //     Supports: tanggal (1 hari), start+end (range), bulan (YYYY-MM)
    // ═══════════════════════════════════════════
    public function data_ringkasan()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;
        $tanggal = isset($input['tanggal']) ? $input['tanggal'] : null;
        $start = isset($input['start']) ? $input['start'] : null;
        $end = isset($input['end']) ? $input['end'] : null;
        $bulan = isset($input['bulan']) ? $input['bulan'] : null;
        // Optional: filter by specific parameter name(s)
        $filter_param = isset($input['parameter']) ? $input['parameter'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        // Determine date mode
        if ($bulan) {
            // Monthly mode: YYYY-MM → generate start/end
            $start = $bulan . '-01';
            $end = date('Y-m-t', strtotime($start)); // last day of month
            $mode = 'bulanan';
        } elseif ($start && $end) {
            $mode = 'range';
        } elseif ($tanggal) {
            $start = $tanggal;
            $end = $tanggal;
            $mode = 'harian';
        } else {
            // Default: hari ini
            $start = date('Y-m-d');
            $end = date('Y-m-d');
            $mode = 'harian';
        }

        // Safety: max 31 days
        $date_diff = (strtotime($end) - strtotime($start)) / 86400;
        if ($date_diff > 31) {
            return $this->_json_response(['status' => 'error', 'message' => 'Range maksimal 31 hari. Gunakan bulan untuk data per bulan.']);
        }

        // Ambil info logger
        $logger = $this->db
            ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
            ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
            ->where('t_logger.id_logger', $id_logger)
            ->get('t_logger')->row();

        if (!$logger) {
            // ── PSDA fallback: gunakan API analisa PSDA ──
            return $this->_ringkasan_psda_fallback($id_logger, $mode, $start, $end, $tanggal, $bulan);
        }

        // Ambil semua parameter sensor
        $params = $this->db->where('logger_id', $id_logger)->get('parameter_sensor')->result();
        if (empty($params)) {
            return $this->_json_response(['status' => 'error', 'message' => 'Tidak ada parameter sensor']);
        }

        $tabel_main = $logger->tabel_main;
        $is_hujan_logger = ($logger->controller == 'arr' || $logger->controller == 'awr');

        // Filter parameters if specified
        if ($filter_param) {
            // Synonym map: Indonesian ↔ English parameter names
            $synonyms = [
                'hujan' => ['precipitation', 'rain', 'rainfall', 'curah'],
                'precipitation' => ['hujan', 'rain', 'rainfall', 'curah'],
                'rain' => ['hujan', 'precipitation', 'rainfall', 'curah'],
                'curah' => ['hujan', 'precipitation', 'rain', 'rainfall'],
                'suhu' => ['temperature', 'temp'],
                'temperature' => ['suhu', 'temp'],
                'angin' => ['wind', 'speed'],
                'wind' => ['angin'],
                'kelembapan' => ['humidity', 'rh', 'kelembaban'],
                'humidity' => ['kelembapan', 'rh', 'kelembaban'],
                'tekanan' => ['pressure', 'barometer'],
                'pressure' => ['tekanan', 'barometer'],
                'radiasi' => ['solar', 'radiation', 'matahari'],
                'solar' => ['radiasi', 'matahari'],
                'uv' => ['ultraviolet'],
                'debit' => ['flow', 'discharge'],
                'tma' => ['water', 'level', 'tinggi', 'muka', 'air'],
            ];

            // Split filter into words, then expand with synonyms
            $filter_words = array_filter(
                explode(' ', strtolower(str_replace('_', ' ', $filter_param))),
                function ($w) {
                    return strlen($w) >= 2;
                }
            );

            // Add synonyms for each word
            $expanded = $filter_words;
            foreach ($filter_words as $word) {
                if (isset($synonyms[$word])) {
                    $expanded = array_merge($expanded, $synonyms[$word]);
                }
            }
            $expanded = array_unique($expanded);

            error_log("[RINGKASAN-FILTER] filter='{$filter_param}' words=" . implode(',', $filter_words) . " expanded=" . implode(',', $expanded));

            // Log all parameter names for debugging
            foreach ($params as $p) {
                error_log("[RINGKASAN-PARAM] nama={$p->nama_parameter} tipe_graf={$p->tipe_graf} satuan={$p->satuan}");
            }

            if (!empty($expanded)) {
                $params = array_filter($params, function ($p) use ($expanded) {
                    $nama_lower = strtolower(str_replace('_', ' ', $p->nama_parameter));
                    $satuan_lower = strtolower($p->satuan);
                    foreach ($expanded as $word) {
                        if (stripos($nama_lower, $word) !== false || stripos($satuan_lower, $word) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
            }
            error_log("[RINGKASAN-FILTER] matched=" . count($params) . " params");
        }

        // Single day mode → compact all-parameter summary
        if ($start === $end) {
            $ringkasan = [];
            foreach ($params as $p) {
                $kolom = $p->kolom_sensor;
                $nama = $p->nama_parameter;
                $is_column = ($p->tipe_graf == 'column');

                $sql = "SELECT
                            COUNT({$kolom}) as jml,
                            ROUND(AVG({$kolom}), 2) as avg_val,
                            ROUND(MIN({$kolom}), 2) as min_val,
                            ROUND(MAX({$kolom}), 2) as max_val,
                            ROUND(SUM({$kolom}), 2) as sum_val
                        FROM {$tabel_main}
                        WHERE code_logger = ?
                          AND waktu >= '{$start} 00:00'
                          AND waktu <= '{$start} 23:59'";

                $row = $this->db->query($sql, [$id_logger])->row();
                error_log("[RINGKASAN-SINGLE] tabel={$tabel_main} kolom={$kolom} tipe_graf={$p->tipe_graf} is_column=" . ($is_column ? 'Y' : 'N') . " jml=" . ($row->jml ?? 0) . " sum=" . ($row->sum_val ?? 'null') . " avg=" . ($row->avg_val ?? 'null'));
                if (!$row || $row->jml == 0)
                    continue;

                $item = [
                    'parameter' => str_replace('_', ' ', $nama),
                    'satuan' => $p->satuan,
                ];

                if ($is_column) {
                    $item['total'] = $row->sum_val;
                    $item['max_per_jam'] = $row->max_val;
                    $item['klasifikasi_harian'] = $this->_klasifikasi_hujan_harian($row->sum_val);
                } else {
                    $item['rata_rata'] = $row->avg_val;
                    $item['minimum'] = $row->min_val;
                    $item['maximum'] = $row->max_val;
                }

                $ringkasan[] = $item;
            }

            return $this->_json_response([
                'status' => 'sukses',
                'id_logger' => $id_logger,
                'nama' => $logger->nama_logger,
                'lokasi' => $logger->nama_lokasi,
                'kategori' => $logger->nama_kategori,
                'mode' => $mode,
                'tanggal' => $start,
                'total_parameter' => count($ringkasan),
                'ringkasan' => $ringkasan
            ]);
        }

        // Multi-day mode → per-day summary per parameter (compact table)
        // Generate list of dates
        $dates = [];
        $cur = $start;
        while ($cur <= $end) {
            $dates[] = $cur;
            $cur = date('Y-m-d', strtotime($cur . ' +1 day'));
        }

        $ringkasan = [];
        foreach ($params as $p) {
            $kolom = $p->kolom_sensor;
            $nama = $p->nama_parameter;
            $is_column = ($p->tipe_graf == 'column');

            // Query per-day aggregation in 1 SQL — matching Api.php's GROUP BY pattern
            $sql = "SELECT
                        DATE(waktu) as tgl,
                        COUNT({$kolom}) as jml,
                        ROUND(AVG({$kolom}), 2) as avg_val,
                        ROUND(MIN({$kolom}), 2) as min_val,
                        ROUND(MAX({$kolom}), 2) as max_val,
                        ROUND(SUM({$kolom}), 2) as sum_val
                    FROM {$tabel_main}
                    WHERE code_logger = ?
                      AND waktu >= '{$start} 00:00'
                      AND waktu <= '{$end} 23:59'
                    GROUP BY DAY(waktu), MONTH(waktu), YEAR(waktu)
                    ORDER BY waktu ASC";

            $rows = $this->db->query($sql, [$id_logger])->result();
            error_log("[RINGKASAN-RANGE] tabel={$tabel_main} kolom={$kolom} param={$nama} tipe_graf={$p->tipe_graf} is_column=" . ($is_column ? 'Y' : 'N') . " rows=" . count($rows));
            if (empty($rows))
                continue;

            // Build daily data indexed by date
            $daily_map = [];
            foreach ($rows as $r) {
                $daily_map[$r->tgl] = $r;
            }

            $daily_data = [];
            foreach ($dates as $d) {
                if (isset($daily_map[$d])) {
                    $r = $daily_map[$d];
                    if ($is_column) {
                        $daily_data[] = [
                            'tanggal' => $d,
                            'total' => $r->sum_val,
                            'max_per_jam' => $r->max_val,
                            'klasifikasi' => $this->_klasifikasi_hujan_harian($r->sum_val),
                        ];
                    } else {
                        $daily_data[] = [
                            'tanggal' => $d,
                            'rata_rata' => $r->avg_val,
                            'minimum' => $r->min_val,
                            'maximum' => $r->max_val,
                        ];
                    }
                } else {
                    $daily_data[] = ['tanggal' => $d, 'data' => 'tidak tersedia'];
                }
            }

            $ringkasan[] = [
                'parameter' => str_replace('_', ' ', $nama),
                'satuan' => $p->satuan,
                'harian' => $daily_data,
            ];
        }

        $this->_json_response([
            'status' => 'sukses',
            'id_logger' => $id_logger,
            'nama' => $logger->nama_logger,
            'lokasi' => $logger->nama_lokasi,
            'kategori' => $logger->nama_kategori,
            'mode' => $mode,
            'periode' => $start . ' s/d ' . $end,
            'jumlah_hari' => count($dates),
            'total_parameter' => count($ringkasan),
            'ringkasan' => $ringkasan
        ]);
    }

    /**
     * Ringkasan fallback for PSDA loggers.
     * Uses PSDA analisa endpoints and dtakhir to build a summary.
     */
    private function _ringkasan_psda_fallback($id_logger, $mode, $start, $end, $tanggal, $bulan)
    {
        // Find logger info from PSDA
        $psda_info = $this->_psda_find_logger($id_logger);
        $nama = $psda_info ? $psda_info['nama_logger'] : '';
        $lokasi = $psda_info ? $psda_info['lokasi'] : '';
        $kategori = $psda_info ? $psda_info['kategori'] : 'PSDA';

        // Get parameter list from PSDA
        $params = $this->_psda_get_parameters($id_logger);
        if (empty($params)) {
            return $this->_json_response(['status' => 'error', 'message' => 'Logger PSDA tidak ditemukan atau tidak memiliki parameter']);
        }

        // For each parameter, call PSDA analisa API and extract summary
        $ringkasan = [];
        $base = 'https://dpupesdm.monitoring4system.com/api/';

        foreach ($params as $p) {
            $id_sensor = $p['id_param'];
            if (empty($id_sensor))
                continue;

            // Call appropriate PSDA analisa endpoint
            if ($mode === 'harian') {
                $url = $base . "analisapertanggal2?idsensor={$id_sensor}&tanggal={$start}";
            } elseif ($mode === 'bulanan') {
                $url = $base . "analisaperbulan2?idsensor={$id_sensor}&tanggal={$bulan}";
            } else {
                // range
                $url = $base . "analisaperrange2?idsensor={$id_sensor}&dari={$start}&sampai={$end}";
            }

            $data_psda = @json_decode(@file_get_contents($url), true);
            if (empty($data_psda['data']))
                continue;

            $values = [];
            if (is_array($data_psda['data'])) {
                $values = array_map('floatval', array_filter($data_psda['data'], 'is_numeric'));
            }

            if (empty($values))
                continue;

            $is_column = ($p['tipe_graf'] ?? 'spline') === 'column';
            $item = [
                'parameter' => str_replace('_', ' ', $p['nama_parameter']),
                'satuan' => $p['satuan'],
            ];

            if ($is_column) {
                $item['total'] = round(array_sum($values), 2);
                $item['max_per_jam'] = round(max($values), 2);
                $item['klasifikasi_harian'] = $this->_klasifikasi_hujan_harian(array_sum($values));
            } else {
                $item['rata_rata'] = round(array_sum($values) / count($values), 2);
                $item['minimum'] = round(min($values), 2);
                $item['maximum'] = round(max($values), 2);
            }

            $ringkasan[] = $item;
        }

        $response = [
            'status' => 'sukses',
            'sumber' => 'PSDA',
            'id_logger' => $id_logger,
            'nama' => $nama,
            'lokasi' => $lokasi,
            'kategori' => $kategori,
            'mode' => $mode,
            'total_parameter' => count($ringkasan),
            'ringkasan' => $ringkasan
        ];

        if ($start === $end) {
            $response['tanggal'] = $start;
        } else {
            $response['periode'] = $start . ' s/d ' . $end;
        }

        return $this->_json_response($ringkasan ? $response : ['status' => 'error', 'message' => 'Data PSDA tidak tersedia untuk periode ini']);
    }

    // ═══════════════════════════════════════════
    // 5) data_realtime — data terbaru / live
    // ═══════════════════════════════════════════

    // --- helpers interpolasi (inlined dari Api.php) ---
    private function linear_interpolation($x)
    {
        $data = [
            [10, 0.251],
            [20, 1.005],
            [30, 2.262],
            [40, 4.021],
            [50, 6.283],
            [60, 9.048],
            [70, 12.315],
            [80, 15.834],
            [90, 19.392],
            [100, 22.988],
            [110, 26.623],
            [120, 30.296],
            [130, 34.008],
            [140, 37.758],
            [150, 41.546],
            [160, 45.373],
            [170, 49.238],
            [180, 53.142],
            [190, 57.084],
            [200, 61.065],
            [210, 65.084],
            [220, 69.142],
            [230, 73.238],
            [240, 77.373],
            [250, 81.546],
            [260, 85.757],
            [270, 90.007],
            [280, 94.295],
            [290, 98.622],
            [300, 102.987],
            [310, 107.391],
            [320, 111.833],
            [330, 116.314],
            [340, 120.833],
            [350, 125.397],
            [360, 130.024],
            [370, 134.725],
            [380, 139.501],
            [390, 144.789],
            [400, 150.182],
            [410, 155.626],
            [420, 161.121],
            [430, 166.667],
            [440, 172.264],
            [450, 177.912],
            [460, 183.611],
            [470, 189.36],
            [480, 195.16],
            [490, 201.011],
            [500, 206.913],
            [510, 212.866],
            [520, 218.87],
            [530, 224.925],
            [540, 231.031],
            [550, 237.188],
            [560, 243.396],
            [570, 249.655],
            [580, 256.03],
            [590, 262.738],
            [600, 269.795],
            [610, 277.202],
            [620, 284.958],
            [630, 293.048],
            [640, 301.23],
            [650, 309.422],
            [660, 317.624],
            [670, 325.836],
            [680, 334.058],
            [690, 343.05],
            [700, 352.048],
            [710, 361.046],
            [720, 370.044],
            [730, 379.042],
            [740, 388.04],
            [750, 397.038],
            [760, 406.036],
            [770, 415.034],
            [780, 424.032],
            [790, 433.03],
            [800, 442.028],
            [810, 451.026],
            [820, 460.024],
            [830, 469.022],
            [837, 475.321],
        ];
        usort($data, function ($a, $b) {
            return $a[0] - $b[0];
        });
        $x1 = $x2 = $y1 = $y2 = null;
        foreach ($data as $point) {
            if ($point[0] <= $x) {
                $x1 = $point[0];
                $y1 = $point[1];
            }
            if ($point[0] >= $x) {
                $x2 = $point[0];
                $y2 = $point[1];
                break;
            }
        }
        if (is_null($x1) || is_null($x2))
            return null;
        if ($x == $x1)
            return $y1;
        return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
    }

    private function debit_interpolation($x)
    {
        $data = [
            [0.20, 12.60],
            [0.40, 17.82],
            [0.60, 21.82],
            [0.80, 25.20],
            [1.00, 28.17],
            [1.20, 31.21],
            [1.40, 33.96],
            [1.60, 36.71],
            [1.80, 38.94],
            [2.00, 41.04],
            [2.20, 43.36],
            [2.40, 45.29],
            [2.60, 47.14],
            [2.80, 48.92],
            [3.00, 50.64],
            [3.20, 52.30],
            [3.40, 53.91],
            [3.60, 55.47],
            [3.80, 56.99],
            [4.00, 58.47],
            [4.20, 23.97],
            [4.40, 24.53],
            [4.60, 25.08],
            [4.80, 25.62],
            [5.00, 26.15],
            [5.20, 26.67],
        ];
        usort($data, function ($a, $b) {
            return $a[0] - $b[0];
        });
        $x1 = $x2 = $y1 = $y2 = null;
        foreach ($data as $point) {
            if ($point[0] <= $x) {
                $x1 = $point[0];
                $y1 = $point[1];
            }
            if ($point[0] >= $x) {
                $x2 = $point[0];
                $y2 = $point[1];
                break;
            }
        }
        if (is_null($x1) || is_null($x2))
            return null;
        if ($x == $x1)
            return $y1;
        return $y1 + (($x - $x1) / ($x2 - $x1)) * ($y2 - $y1);
    }

    private function kalimeneng($tmaInput)
    {
        $data = [
            ['TMA' => 0.3, 'Cd' => 1.185, 'Bef' => 29.674],
            ['TMA' => 0.35, 'Cd' => 1.185, 'Bef' => 29.721],
            ['TMA' => 0.4, 'Cd' => 1.185, 'Bef' => 29.708],
            ['TMA' => 0.5, 'Cd' => 1.185, 'Bef' => 29.692],
            ['TMA' => 0.6, 'Cd' => 1.322, 'Bef' => 29.668],
            ['TMA' => 1.0, 'Cd' => 1.322, 'Bef' => 29.608],
            ['TMA' => 1.5, 'Cd' => 1.394, 'Bef' => 29.5],
            ['TMA' => 2.0, 'Cd' => 1.415, 'Bef' => 29.46],
            ['TMA' => 2.5, 'Cd' => 1.414, 'Bef' => 29.26],
            ['TMA' => 3.0, 'Cd' => 1.394, 'Bef' => 29.14],
            ['TMA' => 3.5, 'Cd' => 1.389, 'Bef' => 29.14],
            ['TMA' => 7.5, 'Cd' => 1.389, 'Bef' => 29.02],
        ];
        $K = 1.705;
        $n = count($data);
        if ($tmaInput < $data[0]['TMA'] || $tmaInput > $data[$n - 1]['TMA'])
            return 0;
        for ($i = 0; $i < $n - 1; $i++) {
            $tma1 = $data[$i]['TMA'];
            $tma2 = $data[$i + 1]['TMA'];
            if ($tmaInput >= $tma1 && $tmaInput <= $tma2) {
                $Cd = $data[$i]['Cd'] + (($tmaInput - $tma1) * ($data[$i + 1]['Cd'] - $data[$i]['Cd'])) / ($tma2 - $tma1);
                $Bef = $data[$i]['Bef'] + (($tmaInput - $tma1) * ($data[$i + 1]['Bef'] - $data[$i]['Bef'])) / ($tma2 - $tma1);
                return $K * $Cd * $Bef * pow($tmaInput, 1.5);
            }
        }
        return 0;
    }

    public function data_realtime()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;
        $mode = isset($input['mode']) ? $input['mode'] : 'last';
        $id_sensor = isset($input['id_sensor']) ? $input['id_sensor'] : null;

        if (!$id_logger) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger wajib diisi']);
        }

        $logger = $this->db
            ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
            ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
            ->where('t_logger.id_logger', $id_logger)
            ->get('t_logger')->row();

        // fallback PSDA jika logger tidak ada lokal
        if (!$logger) {
            $psda_rt = $this->_psda_get_realtime($id_logger);
            return $this->_json_response([
                'status' => $psda_rt ? 'sukses' : 'error',
                'sumber' => 'PSDA',
                'data' => $psda_rt
            ]);
        }

        // === MODE: last ===
        if ($mode === 'last') {
            $is_perbaikan = $this->db->where('id_logger', $id_logger)->count_all_results('t_perbaikan') > 0;
            $qparam = $this->db->query("SELECT * FROM parameter_sensor WHERE logger_id = '{$id_logger}' ORDER BY CAST(SUBSTR(kolom_sensor,7) AS UNSIGNED)");
            $qdata = $this->db->query("SELECT * FROM {$logger->tabel_main} WHERE code_logger = '{$id_logger}' ORDER BY waktu DESC LIMIT 1")->row();
            $waktu = $qdata->waktu ?? null;

            $sensor_list = [];
            foreach ($qparam->result() as $s) {
                $h = $qdata->{$s->kolom_sensor} ?? null;

                if (!$is_perbaikan) {
                    if ($s->debit_awlr == '1' && $id_logger == '10063') {
                        $debit = $this->kalimeneng((float) $h);
                        $h = $h < 0 ? 0 : $debit;
                    } elseif ($s->nama_parameter == 'Debit' && $id_logger == '10249') {
                        $h = $this->linear_interpolation($qdata->sensor2 * 100) * $h;
                    } elseif ($s->nama_parameter == 'Luas_Penampang_Basah') {
                        $h = $this->linear_interpolation($qdata->sensor2 * 100);
                    } elseif ($s->nama_parameter == 'Debit_Aliran_Sungai') {
                        $h = $this->debit_interpolation(abs($qdata->sensor1 - $qdata->sensor2));
                    }
                }
                if ($s->nama_parameter != 'Wind_Direction')
                    $h = number_format($h, 2, '.', '');

                $sensor_list[] = [
                    'id_sensor' => $s->id_param,
                    'nama' => $s->nama_parameter,
                    'nilai' => $h,
                    'satuan' => $s->satuan,
                    'icon' => $s->icon_app,
                    'tipe_graf' => $s->tipe_graf
                ];
            }

            $koneksi = $this->_cek_koneksi($waktu);
            $out = [
                'nama_logger' => $logger->nama_logger,
                'lokasi' => $logger->nama_lokasi,
                'kategori' => $logger->nama_kategori,
                'waktu' => $waktu,
                'koneksi' => $koneksi,
                'sensor' => $sensor_list
            ];
            if ($is_perbaikan)
                $out['status_perangkat'] = 'perbaikan';

            // Add rainfall classification for ARR/AWS
            if ($koneksi === 'On' && ($logger->controller == 'awr' || $logger->controller == 'arr')) {
                $p_utama = $this->db->where('logger_id', $id_logger)->where('parameter_utama', '1')->get('parameter_sensor')->row();
                if ($p_utama) {
                    $kolom_hujan = $p_utama->kolom_sensor;

                    // Hourly accumulation
                    $akum_jam = $this->db->query(
                        "SELECT SUM({$kolom_hujan}) as val FROM {$logger->tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d H') . ":00'"
                    )->row();
                    $val_jam = $akum_jam->val ?? 0;

                    // Daily accumulation
                    $akum_hari = $this->db->query(
                        "SELECT SUM({$kolom_hujan}) as val FROM {$logger->tabel_main} WHERE code_logger = '{$id_logger}' AND waktu >= '" . date('Y-m-d') . " 00:00'"
                    )->row();
                    $val_hari = $akum_hari->val ?? 0;

                    $out['curah_hujan_jam'] = [
                        'nilai' => number_format($val_jam, 2, '.', ''),
                        'satuan' => 'mm/jam',
                        'klasifikasi' => $this->_klasifikasi_hujan_jam($val_jam)
                    ];
                    $out['curah_hujan_harian'] = [
                        'nilai' => number_format($val_hari, 2, '.', ''),
                        'satuan' => 'mm/hari',
                        'klasifikasi' => $this->_klasifikasi_hujan_harian($val_hari)
                    ];
                }
            }

            return $this->_json_response(['status' => 'sukses', 'data' => $out]);
        }

        // === MODE: live ===
        if (!$id_sensor) {
            // ambil parameter utama sebagai default
            $p_utama = $this->db->where('logger_id', $id_logger)->where('parameter_utama', '1')->get('parameter_sensor')->row();
            $id_sensor = $p_utama ? $p_utama->id_param : null;
        }

        if (!$id_sensor) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_sensor wajib diisi untuk mode live']);
        }

        $param1 = $this->db->where('logger_id', $id_logger)->where('id_param', $id_sensor)->get('parameter_sensor')->row();
        $kolom = $param1->kolom_sensor;

        $data_rows = $this->db->where('code_logger', $id_logger)->order_by('waktu', 'desc')->limit(25)->get($logger->tabel_main)->result_array();
        $temp_data = $this->db->where('code_logger', $id_logger)->get($logger->temp_data)->row();

        $data_tabel = [];
        foreach ($data_rows as $val) {
            $data_tabel[] = [
                'waktu' => $val['waktu'],
                'nilai' => $val[$kolom]
            ];
        }

        $waktu_terakhir = $temp_data->waktu ?? null;
        $koneksi = $this->_cek_koneksi($waktu_terakhir);

        $this->_json_response([
            'status' => 'sukses',
            'data' => [
                'nama_pos' => $logger->nama_lokasi,
                'nama_param' => $param1->nama_parameter,
                'koneksi' => $koneksi,
                'satuan' => $param1->satuan,
                'tipe_graf' => $param1->tipe_graf,
                'waktu_terakhir' => $waktu_terakhir,
                'data_tabel' => $data_tabel
            ]
        ]);
    }

    // ═══════════════════════════════════════════
    // 6) data_analisa — analisa historis
    // ═══════════════════════════════════════════
    public function data_analisa()
    {
        $input = $this->_json_input();
        $id_logger = isset($input['id_logger']) ? $input['id_logger'] : null;
        $id_sensor = isset($input['id_sensor']) ? $input['id_sensor'] : null;
        $granularity = isset($input['granularity']) ? $input['granularity'] : null;

        if (!$id_logger || !$id_sensor || !$granularity) {
            return $this->_json_response(['status' => 'error', 'message' => 'id_logger, id_sensor, dan granularity wajib diisi']);
        }

        $tb_main = $this->db->where('id_logger', $id_logger)->get('t_logger')->row();

        // ---------- fallback PSDA ----------
        if (!$tb_main) {
            return $this->_analisa_psda_fallback($id_sensor, $granularity, $input);
        }

        // ---------- parameter info ----------
        $param = $this->db->where('id_param', $id_sensor)->get('parameter_sensor')->row();
        if (!$param) {
            return $this->_json_response(['status' => 'error', 'message' => 'Sensor tidak ditemukan']);
        }

        $is_column = ($param->tipe_graf == 'column');
        $namaSensor = ($is_column ? 'Akumulasi_' : 'Rerata_') . $param->nama_parameter;
        $agg_func = $is_column ? 'sum' : 'avg';
        $select = "{$agg_func}({$param->kolom_sensor}) as {$namaSensor}";
        $sensor = $param->kolom_sensor;
        $satuan = $param->satuan;
        $namaparameter = $param->nama_parameter;
        $cek_rumus = ($param->debit_awlr == '1');

        // ---------- build query berdasarkan granularity ----------
        $where = "code_logger = '{$id_logger}'";
        $group = '';

        switch ($granularity) {
            case 'day':
                $tgl = $input['tanggal'] ?? date('Y-m-d');
                $where .= " AND waktu >= '{$tgl} 00:00' AND waktu <= '{$tgl} 23:59'";
                $group = 'GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m-d H';
                $date_suffix = ':00';
                break;
            case 'month':
                $bln = $input['bulan'] ?? date('Y-m');
                $where .= " AND waktu >= '{$bln}-01 00:00' AND waktu <= '{$bln}-31 23:59'";
                $group = 'GROUP BY DAY(waktu), MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m-d';
                $date_suffix = '';
                break;
            case 'year':
                $thn = $input['tahun'] ?? date('Y');
                $where .= " AND waktu >= '{$thn}-01-01 00:00' AND waktu <= '{$thn}-12-31 23:59'";
                $group = 'GROUP BY MONTH(waktu), YEAR(waktu)';
                $date_format = 'Y-m';
                $date_suffix = '';
                break;
            case 'range':
                $start = $input['start'] ?? date('Y-m-d', strtotime('-7 days'));
                $end = $input['end'] ?? date('Y-m-d');
                $where .= " AND waktu >= '{$start}' AND waktu <= '{$end} 23:59:00'";
                $group = 'GROUP BY HOUR(waktu), DAY(waktu), MONTH(waktu), YEAR(waktu) ORDER BY waktu ASC';
                $date_format = 'Y-m-d H';
                $date_suffix = ':00';
                break;
            default:
                return $this->_json_response(['status' => 'error', 'message' => 'granularity harus day/month/year/range']);
        }

        $sql = "SELECT avg(sensor1 - sensor2) AS avg_diff, min(sensor1 - sensor2) AS min_diff, max(sensor1 - sensor2) AS max_diff, 
				avg(sensor2) as tma, min(sensor2) as tma_min, max(sensor2) as tma_max, 
				waktu, {$select}, min({$sensor}) as min, max({$sensor}) as max 
				FROM {$tb_main->tabel_main} WHERE {$where} {$group}";

        $query_data = $this->db->query($sql);
        $hsl = $query_data->result();

        $waktu_arr = [];
        $data_arr = [];
        $min_arr = [];
        $max_arr = [];

        foreach ($hsl as $datalog) {
            $h = $datalog->$namaSensor;
            $max_value = $datalog->max;
            $min_value = $datalog->min;

            // rumus debit
            if ($cek_rumus && $namaparameter == 'Debit') {
                $rumus = $this->db->where('id_logger', $id_logger)->get('rumus_debit')->row()->rumus;
                if ($h < 0) {
                    $h = 0;
                } else {
                    $debit_avg = eval ('return ' . $rumus . ';');
                    $h_bak = $h;
                    $h = $min_value;
                    $min_value = eval ('return ' . $rumus . ';');
                    $h = $max_value;
                    $max_value = eval ('return ' . $rumus . ';');
                    $h = $debit_avg;
                }
            }

            if ($namaparameter == 'Debit' && $id_logger == '10249') {
                if ($h >= 0) {
                    $n2 = $datalog->tma;
                    $h = number_format($this->linear_interpolation($n2 * 100) * $datalog->$namaSensor, 2, '.', '');
                    $max_value = number_format($this->linear_interpolation($datalog->tma_max * 100) * $datalog->max, 2, '.', '');
                    $min_value = number_format($this->linear_interpolation($datalog->tma_min * 100) * $datalog->min, 2, '.', '');
                } else {
                    $h = 0;
                }
            } elseif ($namaparameter == 'Luas_Penampang_Basah') {
                if ($h >= 0) {
                    $h = number_format($this->linear_interpolation($datalog->tma * 100), 2, '.', '');
                    $max_value = number_format($this->linear_interpolation($datalog->tma_max * 100), 2, '.', '');
                    $min_value = number_format($this->linear_interpolation($datalog->tma_min * 100), 2, '.', '');
                } else {
                    $h = 0;
                }
            } elseif ($namaparameter == 'Debit_Aliran_Sungai') {
                $h = $this->debit_interpolation(abs($datalog->avg_diff));
                $min_value = $this->debit_interpolation(abs($datalog->min_diff));
                $max_value = $this->debit_interpolation(abs($datalog->max_diff));
            }

            $waktu_arr[] = date($date_format, strtotime($datalog->waktu)) . $date_suffix;
            $data_arr[] = number_format($h, 2, '.', '');
            $min_arr[] = number_format($min_value, 2, '.', '');
            $max_arr[] = number_format($max_value, 2, '.', '');
        }

        if ($hsl) {
            $this->_json_response([
                'status' => 'sukses',
                'data' => [
                    'id_logger' => $id_logger,
                    'nama_sensor' => $namaSensor,
                    'satuan' => $satuan,
                    'tipe_graf' => $param->tipe_graf,
                    'waktu' => $waktu_arr,
                    'data' => $data_arr,
                    'data_min' => $min_arr,
                    'data_max' => $max_arr
                ]
            ]);
        } else {
            $this->_json_response(['status' => 'error', 'data' => null]);
        }
    }

    // helper PSDA fallback untuk analisa
    private function _analisa_psda_fallback($id_sensor, $granularity, $input)
    {
        $base = 'https://dpupesdm.monitoring4system.com/api/';
        switch ($granularity) {
            case 'day':
                $tgl = $input['tanggal'] ?? date('Y-m-d');
                $url = $base . "analisapertanggal2?idsensor={$id_sensor}&tanggal={$tgl}";
                break;
            case 'month':
                $bln = $input['bulan'] ?? date('Y-m');
                $url = $base . "analisaperbulan2?idsensor={$id_sensor}&tanggal={$bln}";
                break;
            case 'year':
                $thn = $input['tahun'] ?? date('Y');
                $url = $base . "analisapertahun2?idsensor={$id_sensor}&tahun={$thn}";
                break;
            case 'range':
                $start = $input['start'] ?? '';
                $end = $input['end'] ?? '';
                $url = $base . "analisaperrange2?idsensor={$id_sensor}&dari={$start}&sampai={$end}";
                break;
            default:
                return $this->_json_response(['status' => 'error', 'message' => 'granularity tidak valid']);
        }

        $data_psda = @json_decode(file_get_contents($url), true);
        if (!empty($data_psda['data'])) {
            $this->_json_response(['status' => 'sukses', 'data' => $data_psda]);
        } else {
            $this->_json_response(['status' => 'error', 'data' => null]);
        }
    }

    // ═══════════════════════════════════════════
    // 7) data_komparasi — bandingkan multi-logger
    // ═══════════════════════════════════════════
    public function data_komparasi()
    {
        $input = $this->_json_input();
        $loggers = isset($input['loggers']) ? $input['loggers'] : [];
        $tanggal = isset($input['tanggal']) ? $input['tanggal'] : date('Y-m-d');
        $export = isset($input['export']) ? $input['export'] : false;

        if (empty($loggers)) {
            return $this->_json_response(['status' => 'error', 'message' => 'loggers[] wajib diisi (minimal 1)']);
        }

        $selected = [];
        $is_single = count($loggers) === 1;

        foreach ($loggers as $logger_id) {
            $parts = explode('_', $logger_id);
            $id_logger_real = $parts[0];
            $aset = isset($parts[1]) ? $parts[1] : 'bbws';

            if ($aset === 'bbws') {
                $logger = $this->db
                    ->join('kategori_logger', 'kategori_logger.id_katlogger = t_logger.kategori_log')
                    ->join('t_lokasi', 't_lokasi.idlokasi = t_logger.lokasi_logger')
                    ->where('id_logger', $id_logger_real)
                    ->get('t_logger')->row();

                if (!$logger)
                    continue;

                $parameter = $this->db
                    ->where('logger_id', $id_logger_real)
                    ->where('parameter_utama', '1')
                    ->get('parameter_sensor')->row_array();

                if (!$parameter)
                    continue;

                $is_spline = $parameter['tipe_graf'] === 'spline';
                $agg_func = $is_spline ? 'avg' : 'sum';
                $sensor_name = ($is_spline ? 'Rerata_' : 'Akumulasi_') . $parameter['nama_parameter'];

                $query = $this->db->query("
					SELECT waktu, HOUR(waktu) as jam, DAY(waktu) as hari, MONTH(waktu) as bulan, YEAR(waktu) as tahun,
						{$agg_func}({$parameter['kolom_sensor']}) as {$sensor_name}
					FROM {$logger->tabel_main}
					WHERE code_logger = '{$id_logger_real}'
						AND waktu BETWEEN '{$tanggal} 00:00' AND '{$tanggal} 23:59'
					GROUP BY YEAR(waktu), MONTH(waktu), DAY(waktu), HOUR(waktu)
					ORDER BY waktu ASC
				");

                $data_nilai = [];
                foreach ($query->result() as $row) {
                    $val = number_format($row->$sensor_name, 3, '.', '');
                    $data_nilai[] = [
                        'nilai' => $val,
                        'waktu' => $row->waktu,
                        'jam' => (int) $row->jam
                    ];
                }

                // isi jam kosong
                for ($i = 0; $i < 24; $i++) {
                    if (array_search($i, array_column($data_nilai, 'jam')) === false) {
                        $jam = str_pad($i, 2, '0', STR_PAD_LEFT);
                        $data_nilai[] = [
                            'nilai' => '-',
                            'waktu' => "{$tanggal} {$jam}:00:00",
                            'jam' => $i
                        ];
                    }
                }
                array_multisort(array_column($data_nilai, 'waktu'), SORT_ASC, $data_nilai);

                $selected[] = [
                    'id_logger' => $logger_id,
                    'nama_lokasi' => $logger->nama_lokasi,
                    'nama_kategori' => $logger->nama_kategori,
                    'nama_chart' => $sensor_name,
                    'satuan' => $parameter['satuan'],
                    'tipe_graf' => $parameter['tipe_graf'],
                    'data_nilai' => $data_nilai
                ];

            } else {
                // PSDA via integrasi
                $curl = curl_init();
                $kirim = ['id_logger' => $id_logger_real, 'tanggal' => $tanggal, 'session_logger' => json_encode($loggers)];
                curl_setopt_array($curl, [
                    CURLOPT_URL => 'https://dpupesdm.monitoring4system.com/integrasi/komparasi',
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => $kirim,
                ]);
                $response = curl_exec($curl);
                curl_close($curl);
                $hasil = json_decode($response, true);
                if ($hasil)
                    $selected[] = $hasil;
            }
        }

        // chart name logic
        $kategori_list = array_column($selected, 'nama_kategori');
        $has_rain = in_array('AWS', $kategori_list) || in_array('ARR', $kategori_list);
        $has_awlr = in_array('AWLR', $kategori_list);

        if ($has_rain && !$has_awlr) {
            $chart_name = 'Akumulasi Curah Hujan';
        } elseif ($has_rain && $has_awlr) {
            $chart_name = 'Akumulasi Curah Hujan dan Rerata Tinggi Muka Air';
        } else {
            $chart_name = 'Rerata Tinggi Muka Air';
        }

        $this->_json_response([
            'status' => 'sukses',
            'chart_name' => $chart_name,
            'tanggal' => $tanggal,
            'data' => $selected
        ]);
    }
}
