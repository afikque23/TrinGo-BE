# Tips & Trick Recommendation Algorithm (Hybrid)

Dokumen ini menjelaskan algoritma rekomendasi yang digunakan untuk menampilkan tips yang lebih relevan untuk user, berdasarkan riwayat interaksi (implicit feedback) dan riwayat pencarian.

## Tujuan

- Membuat halaman Tips & Trick menjadi **personalized**.
- Jika user sering mencari/menyukai topik tertentu, sistem akan lebih sering menampilkan konten yang mirip.

## Istilah / Nama Algoritma

- **Hybrid Recommender System** (Content-Based + Implicit Feedback Ranking)

## Data yang Digunakan

### 1) Content-based (kemiripan konten)

- Tag konten: relasi `tips` <-> `tip_tags` lewat pivot `tip_tag_pivot`.

### 2) Implicit feedback (sinyal minat)

- Like: `tip_likes`
- Bookmark/Simpan: `tip_bookmarks`

Bobot default:

- Like = +2
- Bookmark = +3

### 3) Search intent

- Log keyword search: `tip_search_logs`

Keyword disimpan otomatis ketika client memanggil list tips dengan parameter `search=...`.

### 4) Global ranking

- Popularity: `likes_count`, `bookmarks_count`, `views_count`
- Recency: `created_at`

## Output

Client menggunakan:

- `GET /api/v1/motorcycle/tips?sort_by=recommended`
- `GET /api/v1/motorcycle/public/tips?sort_by=recommended`

Jika tidak ada identity (tidak login dan tidak ada header `X-Device-ID`), server fallback ke `popular`.

## Skema Skor

Secara konseptual:

$$
Score(u,i)=TagScore(u,i)+SearchScore(u,i)+0.8\cdot Popularity(i)+0.3\cdot Recency(i)-PenaltySeen(u,i)
$$

### TagScore

- Ambil semua tag dari konten yang di-like/bookmark user.
- Hitung bobot per tag:
    - Like -> tag +2
    - Bookmark -> tag +3
- Ambil top-N tag (default 12).
- Konten yang memiliki tag tersebut mendapat boost sesuai bobot tag.

### SearchScore

- Ambil keyword terakhir (unik) dalam 30 hari terakhir (default max 4).
- Konten yang match keyword pada:
    - `title` (+2)
    - `description` (+1)
    - `hashtags` (+1)

### Popularity

Menggunakan fungsi log agar tidak terlalu bias ke konten yang sangat besar angkanya:

- `LOG(1 + likes_count)`
- `1.5 * LOG(1 + bookmarks_count)`
- `0.2 * LOG(1 + views_count)`

### Recency

- Semakin baru, skor sedikit naik.

### PenaltySeen

- Jika konten sudah di-like: -5
- Jika konten sudah di-bookmark: -7

Tujuan penalti ini agar feed tidak berputar pada konten yang sama.

## Cara Verifikasi (Manual)

1. Like beberapa tips dengan tag tertentu.
2. Bookmark beberapa tips lain.
3. Lakukan search keyword tertentu melalui parameter `search=...`.
4. Panggil endpoint dengan `sort_by=recommended`.
5. Observasi: konten dengan tag/keyword terkait akan naik ke urutan atas.

## File Terkait

- Controller: `app/Http/Controllers/TipsController.php`
- Model log keyword: `app/Models/TipSearchLog.php`
- Migration: `database/migrations/2026_05_13_000001_create_tip_search_logs_table.php`
