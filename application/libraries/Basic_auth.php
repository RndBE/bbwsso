<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * HTTP Basic Authentication untuk API integrasi.
 *
 * Kredensial divalidasi ke tabel `t_user`, memakai skema hash yang sama
 * dengan form login web (MD5 — lihat Login.php). Disamakan supaya admin
 * tidak perlu mengelola dua daftar akun.
 */
class Basic_auth
{
	protected $CI;

	/** @var object|null baris t_user yang cocok, diisi setelah authenticate() sukses */
	protected $user = null;

	/**
	 * Level akun yang boleh memakai API.
	 *
	 * Sengaja TIDAK memuat 'user' dan 'tamu'. Akun `serayu_opak` (level
	 * `user`) dipakai Login::login_tamu() dengan sandi tertulis di source
	 * yang ada di repositori publik, jadi kredensialnya harus dianggap
	 * diketahui umum. Melebarkan daftar ini berarti membuka seluruh data
	 * telemetri ke siapa pun yang bisa membaca repo.
	 *
	 * Untuk mitra integrasi, buat akun tersendiri berlevel admin — jangan
	 * menambahkan 'user' ke sini.
	 */
	protected $level_diizinkan = ['admin'];

	public function __construct()
	{
		$this->CI =& get_instance();
	}

	/**
	 * @return bool true bila kredensial sah. Bila tidak, respons 401 sudah
	 *              dikirim dan pemanggil harus berhenti.
	 */
	public function authenticate()
	{
		list($username, $password) = $this->_credentials_from_request();

		if ($username === null || $password === null) {
			$this->_send_unauthorized('Kredensial tidak dikirim');
			return false;
		}

		$user = $this->CI->db
			->where('username', $username)
			->where('password', md5($password))
			->get('t_user')->row();

		if (!$user) {
			$this->_send_unauthorized('Username atau password salah');
			return false;
		}

		if (!in_array(strtolower((string) $user->level_user), $this->level_diizinkan, true)) {
			// Pesan sengaja tidak menyebut level, supaya tidak membocorkan
			// bahwa kredensialnya sebenarnya benar.
			$this->_send_unauthorized('Akun ini tidak berhak memakai API integrasi');
			return false;
		}

		$this->user = $user;
		return true;
	}

	/** Baris t_user yang lolos autentikasi, atau null. */
	public function user()
	{
		return $this->user;
	}

	/**
	 * Ambil kredensial Basic dari request.
	 *
	 * PHP_AUTH_USER tidak terisi saat PHP berjalan sebagai CGI/FastCGI —
	 * kasus yang berlaku di server ini — jadi header Authorization mentah
	 * ikut dibaca sebagai cadangan.
	 */
	private function _credentials_from_request()
	{
		$server = $_SERVER;
		if (!isset($server['HTTP_AUTHORIZATION'], $server['REDIRECT_HTTP_AUTHORIZATION'])
			&& function_exists('apache_request_headers')) {
			foreach (apache_request_headers() as $k => $v) {
				if (strcasecmp($k, 'Authorization') === 0) {
					$server['HTTP_AUTHORIZATION'] = $v;
					break;
				}
			}
		}
		return self::parse_basic($server);
	}

	/**
	 * Ambil [username, password] dari array bergaya $_SERVER.
	 *
	 * Murni dan statis supaya jalur keamanan ini bisa diuji langsung:
	 * `php tests/test_integrasi_api.php`.
	 *
	 * @return array [string|null, string|null]
	 */
	public static function parse_basic(array $server)
	{
		if (isset($server['PHP_AUTH_USER'], $server['PHP_AUTH_PW'])) {
			return [$server['PHP_AUTH_USER'], $server['PHP_AUTH_PW']];
		}

		$header = null;
		foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $k) {
			if (!empty($server[$k])) {
				$header = $server[$k];
				break;
			}
		}
		if ($header === null || stripos($header, 'basic ') !== 0) {
			return [null, null];
		}

		$decoded = base64_decode(substr($header, 6), true);
		if ($decoded === false || strpos($decoded, ':') === false) {
			return [null, null];
		}

		// Batasi pada pemisah PERTAMA: sandi boleh mengandung titik dua.
		list($u, $p) = explode(':', $decoded, 2);
		return [$u, $p];
	}

	private function _send_unauthorized($pesan)
	{
		$this->CI->output
			->set_status_header(401)
			->set_header('WWW-Authenticate: Basic realm="API Integrasi BBWS Serayu Opak"')
			->set_content_type('application/json')
			->set_output(json_encode([
				'status' => false,
				'pesan' => $pesan,
			], JSON_UNESCAPED_UNICODE));
	}
}
