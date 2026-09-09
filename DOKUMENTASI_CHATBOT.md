# Dokumentasi Chatbot "SobatSO" / Copilot — BBWS Serayu Opak

Dokumen ini menjelaskan **cara kerja** chatbot AI pada sistem monitoring BBWS Serayu Opak: arsitektur, alur permintaan, tools yang tersedia, manajemen sesi, hingga batasan dan konfigurasi.

---

## 1. Ringkasan

| Aspek | Keterangan |
|-------|------------|
| Nama | SobatSO / Copilot |
| Tujuan | Asisten tanya-jawab data monitoring (hujan, TMA, debit, cuaca, status pos) |
| Wilayah | Jawa Tengah & D.I. Yogyakarta (DAS Serayu, Opak, Oyo, Progo, dll.) |
| Model AI | Lewat gateway **9router** di server yang sama (`http://127.0.0.1:20128/api/v1`), combo `Chatbot` → `ds/deepseek-v4-flash` + fallback `cx/gpt-5.6-terra`. Endpoint OpenAI-compatible, function calling didukung kedua anggota combo. |
| Speech-to-Text | OpenAI Whisper (`whisper-1`, bahasa `id`) — tetap OpenAI, DeepSeek tidak punya STT |
| Framework | CodeIgniter (PHP) |
| Penyimpanan sesi | File JSON di `application/cache/copilot_sessions/` |

**File utama:**
- Controller: [application/controllers/Chatbot.php](application/controllers/Chatbot.php)
- UI / frontend: [application/views/template_admin/copilot.php](application/views/template_admin/copilot.php)
- Integrasi WhatsApp: [application/controllers/Webhook_wa.php](application/controllers/Webhook_wa.php)
- Konfigurasi: `application/config/openai.php` (key `deepseek_api_key`, `deepseek_model`, `deepseek_base_url`, plus `openai_api_key` untuk Whisper)

---

## 2. Arsitektur Singkat

```
┌──────────────┐   POST /chatbot/chat        ┌───────────────────┐
│  UI Copilot  │ ──────────────────────────► │  Chatbot.php      │
│ (browser/WA) │                             │  (CodeIgniter)    │
└──────────────┘ ◄────── reply (markdown) ── └─────────┬─────────┘
                                                       │
                          ┌────────────────────────────┼───────────────────────────┐
                          │                            │                            │
                    ┌─────▼──────┐            ┌────────▼────────┐          ┌────────▼────────┐
                    │ DeepSeek   │            │  12 Tools /     │          │ Sesi JSON       │
                    │ v4-flash   │            │  Function calls │          │ copilot_sessions│
                    └────────────┘            │  → DB lokal /   │          └─────────────────┘
                                              │    API PSDA     │
                                              └─────────────────┘
```

Pola yang dipakai adalah **function calling** (format OpenAI, dilayani DeepSeek): model tidak mengakses database secara langsung. Model memutuskan fungsi mana yang dipanggil, backend mengeksekusinya, lalu hasilnya dikirim balik ke model untuk dirangkai menjadi jawaban natural.

---

## 3. Endpoint

| Method & Path | Fungsi | Body | Response |
|---------------|--------|------|----------|
| `POST /chatbot/chat` | Chat utama (UI web) | `{ "message", "session_id"? }` | `{ status, session_id, reply, _debug }` |
| `POST /chatbot/ask` | Integrasi sistem eksternal | `{ "uuid", "message" }` | `{ status, message:{content}, content, reply }` |
| `POST /chatbot/transcribe` | Speech-to-text (Whisper) | `multipart/form-data` field `audio` | `{ status, text }` |

Catatan:
- `chat` membuat `session_id` otomatis (`uniqid('copilot_', true)`) bila tidak dikirim.
- `ask` memakai `uuid` sebagai basis sesi (`ext_<uuid>`) agar konteks multi-turn tetap terjaga lintas sistem.

---

## 4. Alur Kerja Satu Permintaan Chat

Referensi: [Chatbot.php:48-180](application/controllers/Chatbot.php#L48-L180)

1. **Terima input** — baca `message` & `session_id` dari body JSON. Pesan kosong ditolak.
2. **Validasi config** — pastikan `deepseek_api_key` ada.
3. **Muat sesi** — `_load_session($sid)` membaca riwayat percakapan dari file JSON.
   - Sesi baru → tambahkan **system prompt** di indeks 0.
   - Sesi lama → **refresh** system prompt (karena memuat tanggal/jam saat ini).
4. **Tambahkan pesan user** ke riwayat.
5. **Trim riwayat** — `_trim_messages(..., 20)` menjaga system prompt + 20 pesan terakhir agar token tidak habis. Pemotongan tidak boleh memisahkan pasangan `tool_calls` ↔ `tool`.
6. **Panggil DeepSeek** (`_call_llm`) dengan daftar `tools`.
7. **Loop function calling (maks. 5 iterasi):**
   - Selama model meminta `tool_calls`, backend menjalankan tiap fungsi via `_execute_tool`.
   - Hasil fungsi dikirim balik sebagai pesan `role: tool`.
   - DeepSeek dipanggil lagi sampai model berhenti memanggil tool.
8. **Ambil jawaban akhir** dari `choices[0].message.content`.
9. **Simpan sesi** — seluruh riwayat (termasuk tool calls & hasilnya) ditulis ke file JSON.
10. **Kembalikan reply** (markdown) + `_debug` (jejak tool yang dipanggil).

Pengaman:
- `stream => false` **wajib** dikirim eksplisit. 9router menempelkan terminator SSE `data: [DONE]` di belakang objek JSON kalau `stream` tidak disebut, dan `json_decode()` gagal total — gejalanya "Gagal menghubungi DeepSeek API" padahal HTTP 200.
- Body non-JSON dikembalikan sebagai `_error` berisi cuplikan body, bukan `null`, supaya sambungan sukses tidak terbaca sebagai gagal sambung.
- `set_time_limit(300)` agar tidak timeout saat query berat.
- `register_shutdown_function` menangkap fatal error PHP agar response tidak pernah kosong.
- Output buffer dibersihkan sebelum mengirim JSON agar warning PHP tidak bocor.

---

## 5. Eksekusi Tool (Function Calling)

Referensi: [Chatbot.php:837-895](application/controllers/Chatbot.php#L837-L895)

- `_execute_tool($fn_name, $args)` memetakan nama fungsi (yang dikenal model) ke **method internal** controller (mis. `get_logger_list` → `logger_list`).
- `_call_internal()` mengeksekusi method dengan teknik **fake input**: argumen dari model disuntikkan lewat flag `_use_fake` + `_fake_input`, dan output JSON method ditangkap memakai output buffering, lalu di-decode kembali menjadi array.
- Artinya method-method endpoint yang sama (yang dipakai halaman web biasa) dipakai ulang oleh chatbot tanpa duplikasi logika.

---

## 6. Daftar Tools

Definisi: [Chatbot.php:551-834](application/controllers/Chatbot.php#L551-L834)

| # | Tool | Kegunaan | Parameter penting |
|---|------|----------|-------------------|
| 1 | `cek_hujan` | Status hujan **LIVE/realtime** semua pos ARR & AWS | `filter` (`hujan_saja`/`semua`) |
| 2 | `cek_hujan_historis` | Curah hujan **historis** semua pos pada tanggal tertentu | `tanggal` (wajib), `filter` |
| 3 | `resolve_date` | Mengubah ekspresi tanggal natural (mis. "7 hari terakhir") ke format terstruktur | `text` (wajib) |
| 4 | `search_logger` | Cari pos berdasarkan nama/lokasi (dipakai dulu bila user sebut nama) | `keyword` (wajib), `kategori` |
| 5 | `get_logger_list` | Daftar semua pos + statusnya | `kategori` |
| 6 | `get_logger_detail` | Detail teknis 1 pos (serial, IMEI, PIC, dll.) | `id_logger` (wajib) |
| 7 | `get_logger_parameter` | Daftar sensor/parameter pada 1 pos | `id_logger` (wajib) |
| 8 | `get_logger_koneksi` | Status koneksi pos (online/offline/perbaikan) | `id_logger` (wajib) |
| 9 | `get_data_realtime` | Data sensor terbaru (`last` = 1 data, `live` = 25 data) | `id_logger` (wajib), `mode`, `id_sensor` |
| 10 | `get_data_ringkasan` | **Pilihan utama** data historis: rata-rata/min/max/total. Mode 1 hari / range / bulanan | `id_logger` (wajib), `tanggal`/`start`+`end`/`bulan`, `parameter` |
| 11 | `get_data_analisa` | Data **detail per-jam** untuk 1 sensor spesifik | `id_logger`, `id_sensor`, `granularity` (wajib) |
| 12 | `get_data_komparasi` | Membandingkan beberapa pos pada tanggal yang sama | `loggers[]` (wajib), `tanggal` |
| 13 | `get_status_siaga` | **Tingkat siaga AWLR**: TMA terkini vs ambang `tingkat_siaga_awlr` per pos | `id_logger`, `das`, `filter` (`semua`/`bahaya_saja`) |
| 14 | `get_ranking` | **Agregasi/peringkat lintas semua pos** untuk 1 parameter pada 1 rentang | `parameter`, `tanggal`/`start`+`end`/`bulan`, `agregasi`, `urut`, `kategori`, `das`, `limit` |
| 15 | `get_kesehatan_pos` | **Kesehatan & kelengkapan data**: koneksi, data terakhir, jam kosong, %, perbaikan, baterai | `id_logger`, periode, `kategori`, `das`, `filter`, `batas_kelengkapan`, `batas_baterai` |

### Aturan pemilihan tool (dari system prompt)
- Sebut **nama pos** → wajib `search_logger` dulu (bukan langsung pakai ID).
- Sebut **referensi waktu** → wajib `resolve_date` dulu.
- Hujan **sekarang** → `cek_hujan`. Hujan **tanggal tertentu (semua pos)** → `cek_hujan_historis`. **JANGAN** pakai `cek_hujan` untuk historis.
- Hujan historis **1 pos** → `get_data_ringkasan` dengan `parameter='hujan'`.
- "data terkini / data pos X" → **langsung** `get_data_ringkasan` (jangan tampilkan daftar parameter/detail teknis dulu).
- **Larangan:** jangan loop `get_data_ringkasan` untuk semua pos (boros token); jangan panggil `get_data_analisa` berulang untuk banyak parameter.
- Pertanyaan **peringkat/total/rata-rata lintas pos** → `get_ranking` (satu panggilan, agregasi di SQL), bukan loop.
- Pertanyaan **bahaya/siaga/waspada/banjir** → `get_status_siaga`. Model dilarang menilai sendiri apakah angka TMA berbahaya; ambang berbeda tiap pos.
- Pos berlevel `Tidak diatur` = ambang belum diisi admin, **bukan** aman.
- Pertanyaan **data telat/bolong/offline/baterai** → `get_kesehatan_pos` (`get_logger_koneksi` hanya untuk 1 pos).

---

## 7. System Prompt

Referensi: [Chatbot.php:462-548](application/controllers/Chatbot.php#L462-L548)

System prompt dibangun ulang **setiap request** dan berisi:
- **Identitas & peran** sebagai Copilot BBWS Serayu Opak.
- **Wilayah kerja** (Jateng & DIY) + daftar DAS; menolak pertanyaan luar wilayah.
- **Pengetahuan kategori logger**: AWS = AWR (sama), ARR (hujan), AWLR (TMA/debit), Klimatologi, AFMR (debit).
- **Konteks waktu dinamis**: sekarang, hari ini, kemarin, bulan ini, tahun ini.
- **Panduan format jawaban**: Bahasa Indonesia, bullet untuk data 1 waktu, tabel markdown untuk data banyak baris (otomatis dapat tombol Download CSV).
- **Aturan grafik**: blok ` ```chart ` berisi JSON config (`bar` khusus hujan, `line` untuk parameter lain) — data harus dari hasil function call, dilarang mengarang.
- **Batasan topik**: hanya seputar monitoring; tolak sopan pertanyaan di luar topik.

---

## 8. Manajemen Sesi & Token

Referensi: [Chatbot.php:309-380](application/controllers/Chatbot.php#L309-L380)

- Sesi disimpan sebagai file JSON: `application/cache/copilot_sessions/<safe_sid>.json`.
- `session_id` di-sanitasi (regex `[^a-zA-Z0-9_.\-]`) untuk mencegah **directory traversal**.
- Riwayat menyimpan seluruh rantai pesan (`system`, `user`, `assistant`, `tool_calls`, `tool`) → konteks multi-turn utuh.
- `_trim_messages` menjaga system prompt + 20 pesan terakhir, dengan titik potong aman (di pesan `user` atau `assistant` tanpa `tool_calls`) agar pasangan tool tidak terbelah.

---

## 9. Fitur Frontend (UI Copilot)

Referensi: [copilot.php](application/views/template_admin/copilot.php)

- Modal chat dengan render **Markdown** (marked.js).
- **Grafik** otomatis dari blok ` ```chart ` menggunakan Chart.js.
- **Tombol Download CSV** otomatis di bawah setiap tabel.
- **Input suara**: rekam audio → `POST /chatbot/transcribe` → teks diisikan ke kolom chat.

---

## 10. Integrasi WhatsApp

Referensi: [Webhook_wa.php](application/controllers/Webhook_wa.php)

- Pesan WA masuk melalui webhook, diteruskan ke alur chatbot, dan balasan dikirim balik via layanan WA gateway eksternal.
- Memakai pola sesi yang sama sehingga percakapan WA juga multi-turn.

---

## 11. Sumber Data & Fallback

- Sumber utama: **database lokal** sistem monitoring (tabel `t_logger`, `t_lokasi`, `kategori_logger`, tabel data per kategori, `t_perbaikan`, `tingkat_siaga_awlr`, `parameter_sensor`, `rumus_debit`, dll.).
- **Fallback**: bila logger tidak ada di lokal, sebagian fungsi mengambil dari **API PSDA** (`dpupesdm.monitoring4system.com`).

---

## 11b. Tool Agregasi Lintas Pos (siaga / ranking / kesehatan)

Ketiga tool ini menghitung agregasi **di SQL** dan di-*batch* per tabel data, jadi satu
panggilan mencakup seluruh pos — tidak perlu loop tool per pos.

### Cakupan: pos BBWS + pos PSDA

`get_ranking` dan `get_kesehatan_pos` **menggabungkan** 41 pos DPUPESDM DIY (14 AWLR, 23 ARR, 4 AWS)
lewat API PSDA. `get_status_siaga` tetap BBWS-only. Setiap response memuat field `cakupan`
(`pos_psda_termasuk`, `pos_psda_tidak_termasuk`, alasan), dan system prompt mewajibkan model menyebutkannya.

| Tool | Pos PSDA | Sumber | Batas |
|---|---|---|---|
| `get_status_siaga` | **Tidak** | — | Ambang hanya bisa diisi untuk pos lokal ([Pengaturan.php:9](application/controllers/Pengaturan.php#L9) baca `t_logger`); halaman rekapitulasi pun mewarnai siaga **sebelum** `array_merge` PSDA ([Monitoring.php:344](application/controllers/Monitoring.php#L344)). PSDA mengirim field `siaga1/2/3` di tiap parameter tapi **semuanya 0** (0 dari 266 param terisi). |
| `get_ranking` | **Ya, hujan & TMA** | `integrasi/horizontal` (1 request/kategori) | `horizontal` hanya membuka **parameter utama** tiap pos (TMA untuk AWLR, curah hujan untuk ARR/AWS). Suhu/angin/kelembapan/debit → PSDA tidak ikut. Rentang > 31 hari → PSDA dilewat (rekap per jam terlalu besar). |
| `get_kesehatan_pos` | **Ya** | `integrasi/beranda` + `integrasi/horizontal` | Koneksi, data terakhir, dan baterai (`Baterai_Logger` ada di 41 pos) dari `beranda`. Kelengkapan jam dari `horizontal`, parameter utama saja. `jml_baris` tidak tersedia → `null`. |

Nilai pos PSDA berasal dari **rekap per jam** milik PSDA, bukan data mentah: TMA per jam sudah
dirata-rata di sisi mereka, jadi `max`/`min` pos PSDA adalah nilai per jam, bukan puncak sesaat.
Akumulasi hujan tidak terpengaruh. Response memuat `catatan` soal ini, dan tiap baris punya field `sumber` (`BBWS`/`PSDA`).

**Dua jebakan yang sudah ditangani:**
- Satu pos bisa muncul di **dua kategori** PSDA — pos `10114` ada di "Curah Hujan" dan "Duga Air Tanah". Entri pertama yang punya `parameter_utama` dipertahankan dan parameternya digabung; tanpa ini pos hujan salah terklasifikasi jadi AWLR.
- `logger_mapping.json` cuma snapshot dan bisa basi (isinya 41 pos, live 42 entri / 41 unik). Penyebut `cakupan` memakai `integrasi/beranda` bila sudah diambil di request itu, jatuh ke file mapping bila belum — supaya `status_siaga` tidak ikut kena request tambahan.

Tool lain yang **sudah** mencakup pos PSDA: `cek_hujan`, `cek_hujan_historis` (merge API),
`search_logger`, `get_logger_list` (dari `logger_mapping.json`), serta `get_logger_detail`,
`get_logger_parameter`, `get_logger_koneksi`, `get_data_ringkasan`, `get_data_analisa` (fallback per pos).

**Pola query:** pos dikelompokkan per (`t_logger.tabel_main`, `parameter_sensor.kolom_sensor`),
lalu tiap kelompok jadi satu `SELECT ... GROUP BY code_logger` yang di-`UNION ALL`.
Pola sama dipakai halaman analisa ([Analisa.php:1237](application/controllers/Analisa.php#L1237)).
Nama tabel & kolom yang berasal dari DB divalidasi `_aman_identifier()` sebelum masuk SQL mentah.

### `get_status_siaga`
- Ambang dibaca dari `tingkat_siaga_awlr` (`nama`, `nilai`, `warna`, `id_status`).
- Evaluasi ambang ada di [application/libraries/Siaga.php](application/libraries/Siaga.php) —
  bebas DB/CI supaya bisa diuji: `php tests/test_siaga.php`.
- Aturan ambang **sama dengan halaman rekapitulasi** ([Monitoring.php:161](application/controllers/Monitoring.php#L161)):
  ambang diurut naik, ambang **tertinggi yang dilampaui** yang dipakai (`>=`, jadi tepat di ambang = sudah melampaui).
- Level khusus: `Tidak diatur` (ambang belum diisi) dan `Data tidak tersedia` (TMA kosong/pos mati).
  Keduanya **bukan** `Aman` — dibedakan agar pos tanpa konfigurasi tidak terbaca aman.
- Daftar ambang lengkap hanya dikirim bila `id_logger` diisi (hemat token).

### `get_ranking`
- `agregasi=auto` → `SUM` untuk parameter kumulatif (`parameter_sensor.tipe_graf = 'column'`, mis. hujan), `MAX` untuk sisanya.
- Kategori ditebak dari parameter bila tidak diisi: hujan → ARR+AWS, tma/debit → AWLR+AFMR.
- Response memuat `ringkasan` (total & rata-rata **seluruh** pos berdata), bukan hanya baris yang ditampilkan.
- Rentang maksimal 366 hari.
- Debit: `rumus_debit` per pos diterapkan. Koreksi *hardcoded* per-pos di `data_analisa()`
  (10063, 10249, `Debit_Aliran_Sungai`) **tidak** ikut — response memuat `catatan` soal ini.

### `get_kesehatan_pos`
- Kelengkapan dihitung per **jam terisi** (`COUNT(DISTINCT DATE_FORMAT(waktu,'%Y-%m-%d %H')`) dibanding total jam periode.
  Interval kirim tiap perangkat tidak tersimpan di skema, jadi cakupan jam adalah ukuran paling jujur tanpa menebak interval.
- Periode hari ini dipotong sampai jam sekarang agar persentase tidak semu rendah.
- Ambang baterai bergantung perangkat → baterai hanya **dilaporkan**; ditandai bermasalah hanya bila `batas_baterai` diisi.
- Default `filter=bermasalah` supaya response tetap ringkas; `semua` untuk seluruh pos.
- Rentang maksimal 31 hari.

---

## 12. Batasan

1. **Topik** dibatasi hanya monitoring BBWS Serayu Opak (menolak OOT).
2. **Wilayah** hanya Jawa Tengah & DIY.
3. **Maks. 5 iterasi** function calling per pesan.
4. **Maks. ±20 pesan** riwayat yang dibawa (sisanya dipangkas).
5. Data historis disarankan dalam rentang wajar (hindari rentang sangat panjang demi token).
5b. Belum ada tool untuk: riwayat perbaikan (`t_riwayat`), garansi/kontrak fleet-wide (`t_garansi`), rating curve (`rumus_rating_curve`, `datasheet_debit`), piezometer (`t_piezometer`), config notifikasi (`notifikasi`), query geospasial radius/hulu-hilir, dan perbandingan antar-periode (bulan ini vs bulan lalu).
6. Bergantung pada **gateway 9router** (`systemd 9router`, loopback 20128) yang meneruskan ke DeepSeek/OpenAI (butuh koneksi & API key valid); Whisper masih butuh key OpenAI terpisah.
7. Transkripsi suara: bahasa **Indonesia**, format audio terbatas (mp3, mp4, m4a, wav, webm, ogg, mpeg).

---

## 13. Konfigurasi yang Dibutuhkan

`application/config/openai.php`:

```php
// Chat / function calling → gateway 9router di server ini
$config['deepseek_api_key']  = 'sk-...';                          // kunci API 9router (BUKAN kunci DeepSeek)
$config['deepseek_model']    = 'Chatbot';                         // combo 9router; atau 'ds/deepseek-v4-flash' langsung
$config['deepseek_base_url'] = 'http://127.0.0.1:20128/api/v1';   // loopback: lewati Cloudflare + nginx + TLS

// Tanpa gateway, DeepSeek langsung juga jalan:
// $config['deepseek_model']    = 'deepseek-v4-flash';
// $config['deepseek_base_url'] = 'https://api.deepseek.com';

// Speech-to-text (/chatbot/transcribe) → masih OpenAI Whisper
$config['openai_api_key'] = 'sk-...';  // hanya dipakai transcribe(); kosongkan bila fitur suara tidak dipakai
```

Pastikan folder `application/cache/copilot_sessions/` dapat ditulis (writable) oleh web server.
