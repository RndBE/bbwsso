## Algoritma Umum

Agent harus mengikuti urutan langkah berikut untuk setiap pembacaan:

1. **Identifikasi stasiun** dari ID Logger atau nama AWLR.
2. **Validasi unit MA** — pastikan dalam satuan meter (m). Jika data dari logger dalam cm atau mm, konversi terlebih dahulu.
3. **Cek rentang MA** terhadap domain yang valid untuk stasiun tersebut. Jika MA di luar domain (misal MA < batas bawah atau MA > batas atas), **jangan ekstrapolasi** — tandai sebagai _out of range_.
4. **Pilih segmen rumus** sesuai rentang MA.
5. **Hitung Q** dengan rumus pangkat: `Q = a * (MA + b)^c` (perhatikan tanda b — bisa positif atau negatif).
6. **Tandai metode**:
   - Bagian rumus dari pengukuran langsung → metode **grafis-analitis**.
   - Bagian MA tinggi (lihat catatan tiap stasiun) → diturunkan dari **metode kemiringan-luas** atau **tabel limpasan bendung**, artinya hasil bersifat **ekstrapolasi terkalibrasi** dan akurasi lebih rendah dibanding segmen pengukuran langsung.
7. **Output**: Q (m³/det), segmen rumus yang dipakai, status validasi, dan metode penurunan.

---

## Tabel Stasiun AWLR

### 1. AWLR Ngrancah — `ID Logger: 10358`

**Domain MA valid:** `-0.5 m ≤ MA ≤ 3 m`

| Segmen | Rentang MA (m)    | Rumus Q (m³/det)                 | Sumber penurunan |
| ------ | ----------------- | -------------------------------- | ---------------- |
| 1      | `-0.5 ≤ MA ≤ 0.1` | `Q = 0.336 * (MA + 0.564)^2.6`   | Grafis-analitis  |
| 2      | `0.1 < MA ≤ 0.6`  | `Q = 6.183 * (MA + 0.072)^2.212` | Grafis-analitis  |
| 3      | `0.6 < MA ≤ 3.0`  | `Q = 9.586 * (MA - 0.141)^1.754` | Grafis-analitis  |

### 2. AWLR Madurejo — `ID Logger: 10346`

**Domain MA valid:** `-0.7 m ≤ MA ≤ 5 m`

| Segmen | Rentang MA (m)   | Rumus Q (m³/det)                  | Sumber penurunan |
| ------ | ---------------- | --------------------------------- | ---------------- |
| 1      | `-0.7 ≤ MA ≤ 0`  | `Q = 6.38 * (MA + 1.032)^2.591`   | Grafis-analitis  |
| 2      | `0 < MA ≤ 3.2`   | `Q = 10.983 * (MA + 0.759)^1.768` | Grafis-analitis  |
| 3      | `3.2 < MA ≤ 5.0` | `Q = 1.698 * (MA + 2.377)^2.5`    | Grafis-analitis  |

### 3. AWLR Karangtalun — `ID Logger: 10044`

**Domain MA valid:** `-0.2 m ≤ MA ≤ 2.2 m`

| Segmen | Rentang MA (m)    | Rumus Q (m³/det)                   | Sumber penurunan |
| ------ | ----------------- | ---------------------------------- | ---------------- |
| 1      | `-0.2 ≤ MA ≤ 2.2` | `Q = 113.822 * (MA + 0.274)^1.822` | Grafis-analitis  |

### 4. AWLR Slinga — `ID Logger: 10118`

**Domain MA valid:** `0 m ≤ MA ≤ 5 m`

| Segmen | Rentang MA (m) | Rumus Q (m³/det)                   | Sumber penurunan |
| ------ | -------------- | ---------------------------------- | ---------------- |
| 1      | `0 ≤ MA ≤ 5.0` | `Q = 181.751 * (MA + 0.009)^1.508` | Grafis-analitis  |

### 5. AWLR Gombong — `ID Logger: 10052`

**Domain MA valid:** `0.3 m ≤ MA ≤ 3.2 m`

| Segmen | Rentang MA (m)   | Rumus Q (m³/det)                 | Sumber penurunan |
| ------ | ---------------- | -------------------------------- | ---------------- |
| 1      | `0.3 ≤ MA ≤ 1.4` | `Q = 3.791 * (MA - 0.23)^2.264`  | Grafis-analitis  |
| 2      | `1.4 < MA ≤ 3.2` | `Q = 7.668 * (MA - 0.575)^1.834` | Grafis-analitis  |
