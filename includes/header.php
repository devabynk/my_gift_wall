<?php
// Include security headers
require_once __DIR__ . '/security.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Sevdikleriniz için unutulmaz dijital anılar oluşturun. Doğum günü, yılbaşı, mezuniyet ve daha fazlası için interaktif hediye duvarları.">
    <meta name="keywords" content="hediye duvarı, doğum günü, yılbaşı, mezuniyet, dijital hediye, sürpriz">
    <meta name="author" content="Magical Moments">
    <meta name="robots" content="index, follow">

    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:title"
        content="<?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Magical Moments'; ?>">
    <meta property="og:description" content="Sevdikleriniz için unutulmaz dijital anılar oluşturun.">
    <meta property="og:image" content="assets/img/og-image.jpg">

    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Magical Moments'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Outfit', 'sans-serif'] },
                    colors: {
                        primary: '#f97316',
                        secondary: '#8b5cf6',
                        accent: '#06b6d4',
                        highlight: '#fbbf24',
                        dark: '#0f172a'
                    }
                }
            }
        }
    </script>
    <?php if (isset($customStyles)): ?>
        <style>
            <?php echo $customStyles; ?>
        </style>
    <?php endif; ?>
</head>
<body<?php echo isset($bodyClass) ? ' class="' . htmlspecialchars($bodyClass) . '"' : ''; ?>>