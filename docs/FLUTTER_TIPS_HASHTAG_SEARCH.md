# Flutter Integration - Tips Hashtag and Search

Dokumen ini menjelaskan kontrak terbaru untuk fitur hashtag dan pencarian pada Tips Perawatan.

## Base URL

`http://{host}:{port}/api/v1/motorcycle`

Contoh local emulator Android:

`http://10.0.2.2:8000/api/v1/motorcycle`

## Endpoint yang Menggunakan Search dan Hashtag

1. `GET /public/tips`
2. `GET /tips`

Kedua endpoint menggunakan behavior filter yang sama.

## Query Parameters

### 1) search

- Tipe: string
- Behavior: partial match, case-insensitive
- Mencari ke field:
    - title
    - description
    - hashtags

Contoh:

`GET /public/tips?search=oli`

### 2) hashtags

- Tipe: string (comma-separated)
- Behavior: filter berdasarkan hashtag yang sudah dinormalisasi
- Semantics: OR (minimal satu hashtag cocok)

Contoh:

`GET /public/tips?hashtags=oli mesin,daily rider`

### 3) Kombinasi search + hashtags

Contoh:

`GET /public/tips?search=servis&hashtags=oli mesin,touring`

## Contract Hashtag untuk Create/Update

Berlaku untuk:

1. `POST /tips`
2. `PUT /tips/{id}`

Field request:

```json
{
    "hashtags": ["#Oli Mesin", "Perawatan   Rutin", "daily rider"]
}
```

Normalisasi backend sebelum simpan:

1. Trim spasi awal/akhir
2. Hapus prefix `#` berulang di awal
3. Ubah multiple spaces menjadi single space
4. Buang item kosong
5. Deduplikasi case-insensitive

Validasi setelah normalisasi:

1. Maksimal 10 item
2. Panjang setiap item 2-30 karakter

## Format Response

Field `hashtags` selalu tersedia pada list item dan detail:

```json
{
    "id": 1,
    "title": "Cara Efisien Ganti Oli",
    "hashtags": ["Oli Mesin", "Perawatan Rutin", "Daily Rider"]
}
```

Catatan:

- Response hashtag tidak wajib menggunakan simbol `#`
- Format ini konsisten untuk endpoint list dan detail

## Contoh Integrasi Flutter

### Build query parameter

```dart
Map<String, String> buildTipsQuery({
	String? search,
	List<String>? hashtags,
	int page = 1,
	int limit = 10,
}) {
	final query = <String, String>{
		'page': page.toString(),
		'limit': limit.toString(),
	};

	if (search != null && search.trim().isNotEmpty) {
		query['search'] = search.trim();
	}

	if (hashtags != null && hashtags.isNotEmpty) {
		query['hashtags'] = hashtags.join(',');
	}

	return query;
}
```

### Request contoh

```dart
final query = buildTipsQuery(
	search: 'oli',
	hashtags: ['oli mesin', 'daily rider'],
);

final uri = Uri.parse('$baseUrl/public/tips').replace(queryParameters: query);
final response = await http.get(uri, headers: headers);
```

## Error Handling (FE)

Jika input hashtag tidak valid, backend akan mengembalikan `422` dengan error validasi.

Contoh:

```json
{
    "success": false,
    "message": "Validasi gagal",
    "errors": {
        "hashtags": ["Hashtag maksimal 10 item"],
        "hashtags.0": ["Panjang hashtag harus 2-30 karakter"]
    },
    "error_code": "VALIDATION_ERROR"
}
```
