<?php
// Sertakan autoloader Composer
require_once 'vendor/autoload.php';

// Gunakan namespace dari ColorThief
use ColorThief\ColorThief;
use ColorThief\Exception\NotReadableException;

// Inisialisasi variabel untuk menyimpan hasil
$imageUrl = '';
$palette = [];
$error = '';

// Cek jika form telah disubmit (metode POST) dan URL tidak kosong
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['image_url'])) {
    $imageUrl = filter_var($_POST['image_url'], FILTER_SANITIZE_URL);

    // Validasi URL
    if (filter_var($imageUrl, FILTER_VALIDATE_URL)) {
        try {
            // Cek header gambar untuk memastikan itu benar-benar gambar
            // getimagesize() juga bisa bekerja dengan URL
            if (@getimagesize($imageUrl)) {
                // Ambil 8 warna paling dominan dari gambar
                $palette = ColorThief::getPalette($imageUrl, 8);
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
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detektor Warna Dominan</title>
    <style>
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
        background-color: #f4f4f9;
        color: #333;
        display: flex;
        justify-content: center;
        align-items: center;
        flex-direction: column;
        min-height: 100vh;
        margin: 0;
    }

    .container {
        background: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        width: 90%;
        max-width: 600px;
        text-align: center;
    }

    h1 {
        color: #444;
        margin-bottom: 1.5rem;
    }

    form {
        margin-bottom: 2rem;
    }

    input[type="url"] {
        width: calc(100% - 22px);
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        font-size: 1rem;
    }

    button {
        margin-top: 1rem;
        padding: 10px 20px;
        border: none;
        background-color: #007bff;
        color: white;
        border-radius: 5px;
        font-size: 1rem;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    button:hover {
        background-color: #0056b3;
    }

    .error {
        color: #d9534f;
        margin-top: 1rem;
    }

    .results {
        margin-top: 2rem;
        text-align: left;
    }

    .image-preview {
        max-width: 100%;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid #ddd;
    }

    .color-palette {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 15px;
    }

    .color-swatch {
        display: flex;
        flex-direction: column;
        align-items: center;
        font-family: monospace;
        font-size: 0.9rem;
    }

    .color-box {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        border: 1px solid #eee;
        margin-bottom: 8px;
    }
    </style>
</head>

<body>

    <div class="container">
        <h1>🎨 Detektor Warna Dominan</h1>
        <p>Masukkan URL gambar untuk melihat palet warna dominannya.</p>

        <form action="index.php" method="POST">
            <input type="url" name="image_url" placeholder="https://example.com/image.jpg"
                value="<?= htmlspecialchars($imageUrl) ?>" required>
            <br>
            <button type="submit">Analisis Warna</button>
        </form>

        <?php if ($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <?php if (!empty($palette)): ?>
        <div class="results">
            <h2>Hasil Analisis:</h2>
            <img src="<?= htmlspecialchars($imageUrl) ?>" alt="Image Preview" class="image-preview">

            <h3>Palet Warna:</h3>
            <div class="color-palette">
                <?php foreach ($palette as $color):
                        // Konversi array RGB [R, G, B] ke format string CSS rgb()
                        $rgbCss = "rgb(" . $color[0] . ", " . $color[1] . ", " . $color[2] . ")";
                        // Konversi array RGB ke format HEX
                        $hex = sprintf("#%02x%02x%02x", $color[0], $color[1], $color[2]);
                        ?>
                <div class="color-swatch">
                    <div class="color-box" style="background-color: <?= $rgbCss ?>"></div>
                    <span><?= $hex ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

</body>

</html>