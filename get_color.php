<?php

// CLONE PROJECT INI KE DIR CLOUD_MUSIC_PLAYER/API/

// Sertakan autoloader Composer
require_once 'vendor/autoload.php';
// hanya dan hanya jika file ini diakses dari cloud_music_player direktori
require_once __DIR__ . '/../../utils/utils.php';

// Gunakan namespace dari ColorThief
use ColorThief\ColorThief;
use ColorThief\Exception\NotReadableException;

function getDominantColors($imageUrl, $db): ?array
{
    $palette = [];
    $bg_color = '';
    $text_color = '';
    $error = '';
    // Cek jika URL tidak kosong
    if (!empty($imageUrl)) {
        $imageUrl = filter_var($imageUrl, FILTER_SANITIZE_URL);

        // Validasi URL
        if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
            try {
                // Cek header gambar untuk memastikan itu benar-benar gambar
                // getimagesize() juga bisa bekerja dengan URL
                if (@getimagesize($imageUrl)) {
                    // Ambil 8 warna paling dominan dari gambar
                    $palette = ColorThief::getPalette($imageUrl, 2);
                } else {
                    $error = 'URL yang dimasukkan bukan gambar yang valid.';
                }
            } catch (NotReadableException $e) {
                $error = 'Gagal memuat gambar dari URL. Pastikan URL dapat diakses secara publik.';
            } catch (Exception $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        } else {
            $error = 'Format URL yang Anda masukkan tidak valid.';
        }
    }

    if (!empty($error)) {
        // wajar warning karena file ini akan diinclude.
        log_message($error);
    }

    if (!empty($palette)) {
        $index = 0;
        foreach ($palette as $color) {
            // Konversi array RGB [R, G, B] ke format string CSS rgb()
            // $rgbCss = "rgb(" . $color[0] . ", " . $color[1] . ", " . $color[2] . ")";
            // Konversi array RGB ke format HEX
            $hex = sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
            if ($index === 0) {
                $bg_color = $hex;
            } else {
                $text_color = $hex;
            }
            $index++;
        }
    }

    if (!empty(($bg_color)) && !empty($text_color)) {
        $stmt_dominant = $db->prepare(
            "INSERT INTO dominant_color (image_url, bg_color, text_color) VALUES (?, ?, ?) 
            -- Gunakan perintah INSERT ... ON DUPLICATE KEY UPDATE. 
            -- Perintah ini secara cerdas akan melakukan INSERT jika datanya baru, atau UPDATE jika datanya sudah ada. 
            -- Ini sering disebut operasi \"UPSERT\" (Update or Insert)
            ON DUPLICATE KEY UPDATE 
                    bg_color = VALUES(bg_color), 
                    text_color = VALUES(text_color)"
        );
        // Type data: i = integer, s = string
        $stmt_dominant->bind_param(
            "sss",
            $imageUrl,
            $bg_color,
            $text_color
        );

        if (!$stmt_dominant->execute()) {
            // If failed, error will displayed di sini!
            die("Error inserting dominant: " . $stmt_dominant->error);
        }
        $stmt_dominant->close();
        log_message("Warna dominan berhasil disimpan untuk URL: " . $imageUrl);
        // Kembalikan warna dalam format array asosiatif
        return [
            'bg_color' => $bg_color,
            'text_color' => $text_color
        ];
    } else {
        log_message("Warna dominan tidak ditemukan untuk URL: " . $imageUrl);
        return null;
    }
}