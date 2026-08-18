-- Jalankan dulu in dry-run buat verifikasi hasilnya:
SELECT
  image_url AS befores,
  REPLACE(image_url, 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=', '') AS after
FROM dominant_colors
WHERE image_url LIKE 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=%'
LIMIT 10;

-- Lihat dulu mana yang duplikat
SELECT
  REPLACE(image_url, 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=', '') AS normalized,
  COUNT(*) as cnt
FROM dominant_colors
GROUP BY normalized
HAVING cnt > 1;

-- Kalau sudah yakin, baru jalankan update:
UPDATE dominant_colors
SET image_url = REPLACE(image_url, 'https://cybeat.sibeux.my.id/cloud-music-player/api/music/stream?file_type=image&cover_url=', '');