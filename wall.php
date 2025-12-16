<?php
require_once 'config.php';
require_once 'includes/captcha.php';

if (!isset($_GET['uuid'])) {
    header("Location: index.php");
    exit;
}

$uuid = $_GET['uuid'];
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'share';
$giftAdded = false;
$errorMsg = '';

// Fetch wall data
try {
    $stmt = $pdo->prepare("SELECT w.*, t.name as theme_name, t.slug as theme_slug FROM walls w LEFT JOIN themes t ON w.theme_id = t.id WHERE w.wall_uuid = ?");
    $stmt->execute([$uuid]);
    $wall = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$wall) {
        die("Duvar bulunamadı!");
    }

    $eventTime = new DateTime($wall['event_time']);
    $now = new DateTime();
    $isUnlocked = $now >= $eventTime;

    $stmtGifts = $pdo->prepare("SELECT * FROM gifts WHERE wall_id = ? ORDER BY created_at ASC");
    $stmtGifts->execute([$wall['id']]);
    $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

    $stmtTypes = $pdo->prepare("SELECT name, icon FROM theme_gifts WHERE theme_id = ?");
    $stmtTypes->execute([$wall['theme_id']]);
    $giftTypes = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Veritabanı hatası!");
}

// Handle gift submission with CAPTCHA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $mode === 'share') {
    $senderName = trim($_POST['sender_name'] ?? '');
    $giftType = trim($_POST['gift_type'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $posX = floatval($_POST['position_x'] ?? 0);
    $posY = floatval($_POST['position_y'] ?? 0);
    $captchaAnswer = trim($_POST['captcha_answer'] ?? '');
    $csrfToken = $_POST['csrf_token'] ?? '';

    // Validate CSRF token
    if (!validateCsrfToken($csrfToken)) {
        $errorMsg = "Güvenlik doğrulaması başarısız!";
    }
    // Validate CAPTCHA
    elseif (!validateCaptcha($captchaAnswer)) {
        $errorMsg = "CAPTCHA doğrulaması başarısız! Lütfen işlemi tekrar yapın.";
        generateCaptcha(); // Generate new one
    }
    // Check rate limit
    elseif (!checkRateLimit()) {
        $errorMsg = "Çok hızlı gönderim! Lütfen 1 dakika bekleyin.";
    }
    // Validate inputs
    elseif ($senderName && $giftType && $message && $posX && $posY) {
        if ($now < $eventTime) {
            try {
                $stmtInsert = $pdo->prepare("INSERT INTO gifts (wall_id, sender_name, gift_type, message, position_x, position_y) VALUES (?, ?, ?, ?, ?, ?)");
                $stmtInsert->execute([$wall['id'], $senderName, $giftType, $message, $posX, $posY]);
                $giftAdded = true;

                $stmtGifts->execute([$wall['id']]);
                $gifts = $stmtGifts->fetchAll(PDO::FETCH_ASSOC);

                // Generate new CAPTCHA for next submission
                generateCaptcha();
            } catch (PDOException $e) {
                $errorMsg = "Hediye eklenemedi!";
                generateCaptcha();
            }
        } else {
            $errorMsg = "Hediye ekleme süresi doldu!";
        }
    } else {
        $errorMsg = "Lütfen tüm alanları doldurun!";
        generateCaptcha();
    }
}

// Generate CAPTCHA question
$captchaQuestion = getCaptcha();
$csrfToken = generateCsrfToken();

$diff = $eventTime->getTimestamp() - time();
$countdownText = $diff > 0 ? "" : "🎉 Açıldı!";

$pageTitle = htmlspecialchars($wall['creator_name']) . "'in Duvarı";
$bodyClass = "theme-" . $wall['theme_slug'] . " text-white overflow-hidden m-0 p-0 relative";
include 'includes/header.php';
?>

<div id="wall-container" class="relative w-screen h-screen overflow-hidden">

    <?php if ($wall['theme_slug'] === 'christmas'): ?>
        <!-- ===== CHRISTMAS THEME ===== -->
        <!-- Cozy room background elements -->
        <div class="christmas-room-wall"></div>
        <div class="christmas-wooden-floor"></div>

        <!-- Snow overlay - more snowflakes -->
        <div class="christmas-snow-overlay">
            <?php for ($i = 0; $i < 25; $i++): ?>
                <div class="christmas-snowflake"
                    style="left: <?php echo rand(0, 100); ?>%; animation-duration: <?php echo rand(8, 15); ?>s; animation-delay: <?php echo rand(0, 50) / 10; ?>s; font-size: <?php echo rand(8, 20); ?>px;">
                    ❄</div>
            <?php endfor; ?>
        </div>

        <!-- Window with snowy scene and curtains -->
        <div class="christmas-window">
            <div class="christmas-window-snow"></div>
            <div class="christmas-curtain christmas-curtain-left"></div>
            <div class="christmas-curtain christmas-curtain-right"></div>
        </div>

        <!-- Fireplace with mantel and stockings -->
        <div class="christmas-fireplace">
            <div class="christmas-fireplace-brick"></div>
            <div class="christmas-fireplace-opening">
                <div class="christmas-fire-log"></div>
                <div class="christmas-fire"></div>
                <div class="christmas-fire christmas-fire-2"></div>
            </div>
            <div class="christmas-mantel">
                <div class="christmas-stocking"></div>
                <div class="christmas-stocking"></div>
                <div class="christmas-stocking"></div>
            </div>
        </div>

        <!-- Christmas Tree with decorations -->
        <div class="christmas-tree-container">
            <div class="christmas-tree-trunk"></div>
            <div class="christmas-tree"></div>
            <div class="christmas-tree-star">⭐</div>
            <!-- Tree lights -->
            <div class="christmas-tree-lights">
                <?php for ($i = 0; $i < 20; $i++): ?>
                    <div class="christmas-light"
                        style="left: <?php echo rand(20, 80); ?>%; top: <?php echo rand(15, 85); ?>%; animation-delay: <?php echo rand(0, 20) / 10; ?>s; background: <?php echo ['#ff0000', '#00ff00', '#ffff00', '#0066ff', '#ff6600'][rand(0, 4)]; ?>;">
                    </div>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Gift boxes under tree -->
        <div class="christmas-gifts">
            <div class="christmas-gift christmas-gift-1">🎁</div>
            <div class="christmas-gift christmas-gift-2">🎄</div>
            <div class="christmas-gift christmas-gift-3">🎁</div>
        </div>

    <?php elseif ($wall['theme_slug'] === 'birthday'): ?>
        <!-- ===== BIRTHDAY THEME ===== -->
        <!-- Party room background -->
        <div class="birthday-wall-pattern"></div>

        <!-- Party garland/bunting -->
        <div class="birthday-garland">
            <div class="birthday-flag" style="background: #ff6b9d;"></div>
            <div class="birthday-flag" style="background: #feca57;"></div>
            <div class="birthday-flag" style="background: #48dbfb;"></div>
            <div class="birthday-flag" style="background: #c780e8;"></div>
            <div class="birthday-flag" style="background: #1dd1a1;"></div>
            <div class="birthday-flag" style="background: #ff6b9d;"></div>
            <div class="birthday-flag" style="background: #feca57;"></div>
            <div class="birthday-flag" style="background: #48dbfb;"></div>
        </div>

        <!-- Floating balloons -->
        <div class="birthday-balloons">
            <div class="birthday-balloon"
                style="left: 8%; animation-delay: 0s; background: linear-gradient(135deg, #ff6b9d 0%, #ff4081 100%);"></div>
            <div class="birthday-balloon"
                style="left: 18%; animation-delay: 0.5s; background: linear-gradient(135deg, #48dbfb 0%, #00bcd4 100%);">
            </div>
            <div class="birthday-balloon"
                style="left: 75%; animation-delay: 1s; background: linear-gradient(135deg, #feca57 0%, #ff9800 100%);">
            </div>
            <div class="birthday-balloon"
                style="left: 85%; animation-delay: 1.5s; background: linear-gradient(135deg, #c780e8 0%, #9c27b0 100%);">
            </div>
            <div class="birthday-balloon"
                style="left: 92%; animation-delay: 0.8s; background: linear-gradient(135deg, #1dd1a1 0%, #00c853 100%);">
            </div>
        </div>

        <!-- Confetti overlay - more pieces -->
        <div class="birthday-confetti-overlay">
            <?php for ($i = 0; $i < 30; $i++): ?>
                <div class="birthday-confetti"
                    style="left: <?php echo rand(5, 95); ?>%; animation-duration: <?php echo rand(30, 60) / 10; ?>s; animation-delay: <?php echo rand(0, 30) / 10; ?>s; background: <?php echo ['#ff6b9d', '#c780e8', '#feca57', '#48dbfb', '#1dd1a1'][rand(0, 4)]; ?>;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Table and tablecloth -->
        <div class="birthday-tablecloth"></div>
        <div class="birthday-table"></div>

        <!-- Cake with candles -->
        <div class="birthday-cake">
            <div class="birthday-cake-plate"></div>
            <div class="birthday-cake-tier birthday-cake-tier-1"></div>
            <div class="birthday-cake-tier birthday-cake-tier-2"></div>
            <div class="birthday-cake-tier birthday-cake-tier-3"></div>
            <div class="birthday-candles">
                <div class="birthday-candle">
                    <div class="birthday-flame"></div>
                </div>
                <div class="birthday-candle">
                    <div class="birthday-flame"></div>
                </div>
                <div class="birthday-candle">
                    <div class="birthday-flame"></div>
                </div>
                <div class="birthday-candle">
                    <div class="birthday-flame"></div>
                </div>
                <div class="birthday-candle">
                    <div class="birthday-flame"></div>
                </div>
            </div>
        </div>

        <!-- Table edge (foreground for depth) -->
        <div class="birthday-table-edge"></div>

    <?php elseif ($wall['theme_slug'] === 'halloween'): ?>
        <!-- ===== HALLOWEEN THEME ===== -->
        <!-- Spooky sky with clouds -->
        <div class="halloween-clouds">
            <div class="halloween-cloud" style="left: 10%; top: 15%; animation-delay: 0s;"></div>
            <div class="halloween-cloud" style="left: 50%; top: 8%; animation-delay: 5s;"></div>
            <div class="halloween-cloud" style="left: 80%; top: 20%; animation-delay: 10s;"></div>
        </div>

        <!-- Moon with glow -->
        <div class="halloween-moon">
            <div class="halloween-moon-crater"></div>
            <div class="halloween-moon-crater halloween-moon-crater-2"></div>
        </div>

        <!-- Flying bats -->
        <div class="halloween-bats">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="halloween-bat"
                    style="left: <?php echo rand(10, 90); ?>%; top: <?php echo rand(10, 40); ?>%; animation-delay: <?php echo rand(0, 30) / 10; ?>s;">
                    🦇</div>
            <?php endfor; ?>
        </div>

        <!-- Dead tree silhouette -->
        <div class="halloween-dead-tree"></div>

        <!-- Haunted house -->
        <div class="halloween-house">
            <div class="halloween-house-roof"></div>
            <div class="halloween-window"></div>
            <div class="halloween-window"></div>
            <div class="halloween-door">
                <div class="halloween-door-knocker"></div>
            </div>
        </div>

        <!-- Graveyard ground with fence -->
        <div class="halloween-fence"></div>
        <div class="halloween-ground">
            <div class="halloween-tombstone"><span>RIP</span></div>
            <div class="halloween-tombstone"><span>RIP</span></div>
            <div class="halloween-tombstone halloween-tombstone-cross"></div>
            <div class="halloween-tombstone"><span>RIP</span></div>
        </div>

        <!-- Pumpkins -->
        <div class="halloween-pumpkins">
            <div class="halloween-pumpkin">🎃</div>
            <div class="halloween-pumpkin">🎃</div>
            <div class="halloween-pumpkin">🎃</div>
        </div>

        <!-- Fog overlay -->
        <div class="halloween-fog"></div>
        <div class="halloween-grass"></div>

    <?php elseif ($wall['theme_slug'] === 'baby'): ?>
        <!-- ===== BABY SHOWER THEME ===== -->
        <!-- Soft sky with floating clouds -->
        <div class="baby-sky">
            <?php for ($i = 0; $i < 6; $i++): ?>
                <div class="baby-cloud"
                    style="left: <?php echo $i * 18; ?>%; top: <?php echo rand(5, 25); ?>%; animation-delay: <?php echo $i * 2; ?>s;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Soft rainbow accent -->
        <div class="baby-rainbow"></div>

        <!-- Stars on walls -->
        <div class="baby-wall-stars">
            <?php for ($i = 0; $i < 15; $i++): ?>
                <div class="baby-wall-star"
                    style="left: <?php echo rand(5, 95); ?>%; top: <?php echo rand(5, 40); ?>%; animation-delay: <?php echo rand(0, 30) / 10; ?>s;">
                    ★</div>
            <?php endfor; ?>
        </div>

        <!-- Wall shelves with items -->
        <div class="baby-shelf baby-shelf-1">
            <div class="baby-shelf-toy">🧸</div>
            <div class="baby-shelf-toy">📖</div>
        </div>
        <div class="baby-shelf baby-shelf-2">
            <div class="baby-shelf-toy">🦆</div>
            <div class="baby-shelf-toy">🎀</div>
        </div>

        <!-- Hanging mobile -->
        <div class="baby-mobile">
            <div class="baby-mobile-arm"></div>
            <div class="baby-mobile-item baby-mobile-item-1">⭐</div>
            <div class="baby-mobile-item baby-mobile-item-2">🌙</div>
            <div class="baby-mobile-item baby-mobile-item-3">☁️</div>
        </div>

        <!-- Floor rug -->
        <div class="baby-rug"></div>

        <!-- Crib with padding and pillow -->
        <div class="baby-crib-back">
            <div class="baby-crib-padding"></div>
            <div class="baby-crib-mattress"></div>
            <div class="baby-crib-pillow"></div>
            <div class="baby-crib-blanket"></div>
        </div>

        <!-- Crib front bars -->
        <div class="baby-crib-bars">
            <?php for ($i = 0; $i < 10; $i++): ?>
                <div class="baby-crib-bar"></div>
            <?php endfor; ?>
        </div>

        <!-- Stuffed toys on floor -->
        <div class="baby-floor-toys">
            <div class="baby-floor-toy">🧸</div>
            <div class="baby-floor-toy">🐰</div>
        </div>

    <?php elseif ($wall['theme_slug'] === 'graduation'): ?>
        <!-- ===== GRADUATION THEME ===== -->
        <!-- Blue sky with clouds -->
        <div class="graduation-sky">
            <?php for ($i = 0; $i < 5; $i++): ?>
                <div class="graduation-cloud"
                    style="left: <?php echo $i * 22; ?>%; top: <?php echo rand(10, 30); ?>%; animation-delay: <?php echo $i * 3; ?>s;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Flying graduation caps -->
        <div class="graduation-caps">
            <?php for ($i = 0; $i < 8; $i++): ?>
                <div class="graduation-cap"
                    style="left: <?php echo rand(15, 85); ?>%; animation-delay: <?php echo rand(0, 30) / 10; ?>s;">🎓</div>
            <?php endfor; ?>
        </div>

        <!-- Confetti celebration -->
        <div class="graduation-confetti">
            <?php for ($i = 0; $i < 20; $i++): ?>
                <div class="graduation-confetti-piece"
                    style="left: <?php echo rand(10, 90); ?>%; animation-delay: <?php echo rand(0, 30) / 10; ?>s; background: <?php echo ['#ffd700', '#1e3a8a', '#ffffff', '#dc2626'][rand(0, 3)]; ?>;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- School building background -->
        <div class="graduation-building"></div>

        <!-- School gate -->
        <div class="graduation-gate">
            <div class="graduation-gate-pillar">
                <div class="graduation-ivy"></div>
                <div class="graduation-pillar-top"></div>
            </div>
            <div class="graduation-gate-arch">
                <div class="graduation-arch-text">CONGRATULATIONS</div>
            </div>
            <div class="graduation-gate-pillar">
                <div class="graduation-ivy"></div>
                <div class="graduation-pillar-top"></div>
            </div>
        </div>

        <!-- Ground/lawn -->
        <div class="graduation-lawn"></div>

        <!-- Podium with microphone -->
        <div class="graduation-podium">
            <div class="graduation-podium-front"></div>
            <div class="graduation-microphone"></div>
            <div class="graduation-emblem">🏆</div>
        </div>

    <?php elseif ($wall['theme_slug'] === 'farewell'): ?>
        <!-- ===== FAREWELL / OFFICE THEME ===== -->
        <!-- Office wall texture -->
        <div class="farewell-wall"></div>
        <div class="farewell-floor"></div>

        <!-- Window with blinds -->
        <div class="farewell-window">
            <div class="farewell-blinds"></div>
            <div class="farewell-window-light"></div>
        </div>

        <!-- Corkboard with post-its and photos -->
        <div class="farewell-corkboard">
            <div class="farewell-postit farewell-postit-1">💛</div>
            <div class="farewell-postit farewell-postit-2">📌</div>
            <div class="farewell-postit farewell-postit-3">❤️</div>
            <div class="farewell-photo"></div>
            <div class="farewell-photo farewell-photo-2"></div>
        </div>

        <!-- Desk with items -->
        <div class="farewell-desk">
            <div class="farewell-computer">
                <div class="farewell-screen"></div>
                <div class="farewell-keyboard"></div>
            </div>
            <div class="farewell-mug">☕</div>
            <div class="farewell-plant">🪴</div>
            <div class="farewell-papers"></div>
        </div>

        <!-- Moving boxes -->
        <div class="farewell-boxes">
            <div class="farewell-box farewell-box-1">📦</div>
            <div class="farewell-box farewell-box-2">📦</div>
        </div>

        <!-- Suitcase -->
        <div class="farewell-suitcase">
            <div class="farewell-suitcase-handle"></div>
            <div class="farewell-suitcase-stripe"></div>
            <div class="farewell-suitcase-tag">✈️</div>
        </div>

    <?php elseif ($wall['theme_slug'] === 'love'): ?>
        <!-- ===== ROMANTIC / VALENTINE'S THEME ===== -->
        <!-- Starry night sky -->
        <div class="romantic-stars">
            <?php for ($i = 0; $i < 50; $i++): ?>
                <div class="romantic-star"
                    style="left: <?php echo rand(0, 100); ?>%; top: <?php echo rand(0, 55); ?>%; animation-delay: <?php echo rand(0, 40) / 10; ?>s; width: <?php echo rand(2, 5); ?>px; height: <?php echo rand(2, 5); ?>px;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Shooting stars -->
        <div class="romantic-shooting-stars">
            <div class="romantic-shooting-star" style="top: 20%; left: 60%; animation-delay: 0s;"></div>
            <div class="romantic-shooting-star" style="top: 35%; left: 30%; animation-delay: 4s;"></div>
        </div>

        <!-- Moon -->
        <div class="romantic-moon"></div>

        <!-- Fireflies -->
        <div class="romantic-fireflies">
            <?php for ($i = 0; $i < 12; $i++): ?>
                <div class="romantic-firefly"
                    style="left: <?php echo rand(10, 90); ?>%; top: <?php echo rand(40, 85); ?>%; animation-delay: <?php echo rand(0, 50) / 10; ?>s;">
                </div>
            <?php endfor; ?>
        </div>

        <!-- Grass field with flowers -->
        <div class="romantic-grass">
            <div class="romantic-flower" style="left: 10%;">🌸</div>
            <div class="romantic-flower" style="left: 25%;">🌷</div>
            <div class="romantic-flower" style="left: 75%;">🌹</div>
            <div class="romantic-flower" style="left: 90%;">🌺</div>
        </div>

        <!-- Picnic blanket -->
        <div class="romantic-blanket">
            <div class="romantic-blanket-pattern"></div>
        </div>

        <!-- Picnic basket -->
        <div class="romantic-basket">🧺</div>

        <!-- Table with candles and roses -->
        <div class="romantic-table">
            <div class="romantic-candle">
                <div class="romantic-flame"></div>
            </div>
            <div class="romantic-roses">🌹</div>
            <div class="romantic-candle">
                <div class="romantic-flame"></div>
            </div>
            <div class="romantic-wine">🍷</div>
        </div>

        <!-- Floating hearts -->
        <div class="romantic-hearts">
            <?php for ($i = 0; $i < 8; $i++): ?>
                <div class="romantic-heart"
                    style="left: <?php echo rand(20, 80); ?>%; animation-delay: <?php echo rand(0, 40) / 10; ?>s;">❤️</div>
            <?php endfor; ?>
        </div>

        <!-- Blur edges -->
        <div class="romantic-blur-edges"></div>

    <?php elseif ($wall['theme_slug'] === 'getwell'): ?>
        <!-- ===== GET WELL SOON THEME ===== -->
        <!-- Cozy room wall -->
        <div class="getwell-wall"></div>
        <div class="getwell-floor"></div>

        <!-- Window with sunshine -->
        <div class="getwell-window">
            <div class="getwell-curtain getwell-curtain-left"></div>
            <div class="getwell-curtain getwell-curtain-right"></div>
            <div class="getwell-sunrays"></div>
        </div>

        <!-- Picture frames -->
        <div class="getwell-frames">
            <div class="getwell-frame">🖼️</div>
            <div class="getwell-frame">🏞️</div>
        </div>

        <!-- Fireplace with warmth -->
        <div class="getwell-fireplace">
            <div class="getwell-fireplace-mantel"></div>
            <div class="getwell-fire">
                <div class="getwell-flame"></div>
                <div class="getwell-flame getwell-flame-2"></div>
            </div>
            <div class="getwell-fireplace-glow"></div>
        </div>

        <!-- Side table with items -->
        <div class="getwell-side-table">
            <div class="getwell-lamp">💡</div>
            <div class="getwell-tea">🍵</div>
            <div class="getwell-tea-steam"></div>
        </div>

        <!-- Book stack -->
        <div class="getwell-books">📚</div>

        <!-- Armchair -->
        <div class="getwell-chair">
            <div class="getwell-chair-back"></div>
            <div class="getwell-chair-seat">
                <div class="getwell-cushion"></div>
            </div>
            <div class="getwell-armrest getwell-armrest-left"></div>
            <div class="getwell-armrest getwell-armrest-right"></div>
        </div>

        <!-- Cozy blanket -->
        <div class="getwell-blanket">
            <div class="getwell-blanket-fold"></div>
        </div>

        <!-- Slippers -->
        <div class="getwell-slippers">👟</div>

        <!-- Flower vase -->
        <div class="getwell-flowers">
            <div class="getwell-vase"></div>
            <div class="getwell-flower-stems">🌸🌼🌷</div>
        </div>

    <?php endif; ?>


    <!-- Header -->
    <div class="absolute top-0 left-0 w-full p-4 md:p-6 z-20 flex justify-between items-start pointer-events-none">
        <div class="pointer-events-auto bg-black/30 backdrop-blur-md border border-white/10 px-4 py-2 rounded-full">
            <h1 class="text-lg md:text-2xl font-bold"><?php echo htmlspecialchars($wall['creator_name']); ?>'in Duvarı
            </h1>
            <p class="text-xs md:text-sm text-slate-300 opacity-75"><?php echo htmlspecialchars($wall['theme_name']); ?>
            </p>
        </div>
        <div
            class="pointer-events-auto bg-black/30 backdrop-blur-md border border-white/10 px-4 py-2 rounded-full text-center">
            <div class="text-xs uppercase tracking-widest text-slate-400">Kalan Süre</div>
            <div id="countdown" class="text-sm md:text-xl font-mono font-bold"><?php echo $countdownText; ?></div>
        </div>
    </div>

    <!-- Gifts Layer -->
    <div id="gifts-layer" class="absolute inset-0 z-10 w-full h-full">
        <?php foreach ($gifts as $gift): ?>
            <?php if ($mode === 'view'): ?>
                <!-- View mode: Clickable gifts -->
                <div class="gift-item"
                    style="left: <?php echo $gift['position_x']; ?>%; top: <?php echo $gift['position_y']; ?>%;"
                    onclick="viewGift('<?php echo htmlspecialchars($gift['gift_type'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($gift['sender_name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($gift['message'], ENT_QUOTES); ?>', <?php echo $isUnlocked ? 'false' : 'true'; ?>)">
                    <div class="gift-icon"><?php echo $gift['gift_type']; ?></div>
                    <div class="gift-sender"><?php echo htmlspecialchars($gift['sender_name']); ?></div>
                </div>
            <?php else: ?>
                <!-- Share mode: Non-clickable gifts -->
                <div class="gift-item cursor-default"
                    style="left: <?php echo $gift['position_x']; ?>%; top: <?php echo $gift['position_y']; ?>%;">
                    <div class="gift-icon"><?php echo $gift['gift_type']; ?></div>
                    <div class="gift-sender"><?php echo htmlspecialchars($gift['sender_name']); ?></div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>

    <?php if ($mode === 'share' && !$isUnlocked): ?>
        <button id="addGiftBtn" onclick="showGiftForm()"
            class="fixed bottom-8 right-8 w-14 h-14 md:w-16 md:h-16 bg-gradient-to-r from-primary to-secondary rounded-full shadow-2xl flex items-center justify-center text-2xl md:text-3xl text-white hover:scale-110 transition-all z-30">
            ＋
        </button>
    <?php endif; ?>

    <div id="positionMarker" class="hidden fixed w-12 h-12 pointer-events-none z-40"
        style="transform: translate(-50%, -50%);">
        <div class="relative w-full h-full">
            <div class="absolute inset-0 bg-primary rounded-full opacity-50 animate-ping"></div>
            <div class="absolute inset-0 bg-primary rounded-full border-4 border-white"></div>
        </div>
    </div>

    <button id="saveBtn" onclick="submitGift()"
        class="hidden fixed bottom-8 right-8 w-14 h-14 md:w-16 md:h-16 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full shadow-2xl flex items-center justify-center text-white hover:scale-110 transition-all z-30 animate-pulse">
        <svg class="w-8 h-8 md:w-10 md:h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
        </svg>
    </button>
</div>

<!-- Gift Form Modal -->
<div id="giftFormModal"
    class="fixed inset-0 z-50 hidden flex items-end md:items-center justify-center bg-black/80 backdrop-blur-sm">
    <div
        class="bg-slate-900 border border-slate-700 rounded-t-3xl md:rounded-3xl p-6 w-full md:max-w-md shadow-2xl max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl md:text-2xl font-bold mb-6">Hediye Bırak 🎁</h2>

        <?php if ($errorMsg): ?>
            <div class="bg-red-500/20 border border-red-500 rounded-lg p-3 mb-4 text-sm"><?php echo $errorMsg; ?></div>
        <?php endif; ?>

        <form id="giftPreForm" class="space-y-4">
            <div>
                <label class="block text-sm text-slate-400 mb-1">Adın</label>
                <input type="text" id="sender_name"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none"
                    required>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1">Hediye Seç</label>
                <select id="gift_type"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none">
                    <?php foreach ($giftTypes as $type): ?>
                        <option value="<?php echo htmlspecialchars($type['icon']); ?>"><?php echo $type['icon']; ?>
                            <?php echo htmlspecialchars($type['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block text-sm text-slate-400 mb-1">Mesajın</label>
                <textarea id="gift_message" rows="3"
                    class="w-full bg-slate-800 border border-slate-700 rounded-xl px-4 py-3 text-white focus:border-primary focus:outline-none"
                    required></textarea>
            </div>

            <!-- CAPTCHA -->
            <div class="bg-slate-800/50 border border-slate-600 rounded-xl p-4">
                <label class="block text-sm text-slate-400 mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                        </path>
                    </svg>
                    Güvenlik Doğrulaması
                </label>
                <div class="flex items-center gap-3">
                    <div
                        class="flex-1 bg-slate-950 rounded-lg px-4 py-3 font-mono text-lg text-center border border-slate-700">
                        <?php echo htmlspecialchars($captchaQuestion); ?>
                    </div>
                    <input type="number" id="captcha_answer" placeholder="?"
                        class="w-20 bg-slate-800 border border-slate-700 rounded-lg px-4 py-3 text-center text-white focus:border-primary focus:outline-none"
                        required>
                </div>
            </div>

            <button type="button" onclick="startPositionSelect()"
                class="w-full bg-primary hover:bg-violet-600 text-white font-bold py-4 rounded-xl shadow-lg transition-all">
                Devam Et (Konum Seç) →
            </button>
        </form>

        <button onclick="closeGiftForm()" class="mt-4 text-slate-400 hover:text-white text-sm">İptal</button>
    </div>
</div>

<!-- Hidden Form -->
<form id="finalSubmitForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="sender_name" id="final_sender">
    <input type="hidden" name="gift_type" id="final_gift">
    <input type="hidden" name="message" id="final_message">
    <input type="hidden" name="position_x" id="final_x">
    <input type="hidden" name="position_y" id="final_y">
    <input type="hidden" name="captcha_answer" id="final_captcha">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
</form>

<!-- View Gift Modal -->
<div id="viewModal"
    class="fixed inset-0 z-50 hidden flex items-center justify-center bg-black/80 backdrop-blur-md px-4">
    <div
        class="bg-white/10 border border-white/20 backdrop-blur-xl rounded-3xl p-8 w-full max-w-sm text-center shadow-2xl">
        <div id="viewIcon" class="text-6xl mb-6">🎁</div>
        <div class="mb-6">
            <p class="text-sm text-slate-300 uppercase tracking-widest mb-2">GÖNDEREN</p>
            <h3 id="viewSender" class="text-2xl font-bold">...</h3>
        </div>
        <div class="bg-black/20 rounded-xl p-6">
            <p id="viewMessage" class="text-lg italic text-slate-200">"..."</p>
        </div>
        <button onclick="closeViewModal()"
            class="mt-6 bg-white/20 hover:bg-white/30 text-white px-8 py-2 rounded-full">Kapat</button>
    </div>
</div>

<?php if ($giftAdded): ?>
    <script>
        alert('🎁 Hediyein eklendi! Teşekkürler!');
    </script>
<?php endif; ?>

<script>
    const eventTime = new Date('<?php echo $wall['event_time']; ?>').getTime();
    let waitingForPosition = false;
    let selectedPos = null;

    // Theme-specific drop zone configurations
    const themeConfigs = {
        christmas: {
            zones: [
                { name: 'tree', x: 15, y: 50, width: 40, height: 35, description: 'Ağaç altı' },
                { name: 'mantel', x: 70, y: 35, width: 25, height: 15, description: 'Şömine rafı' },
                { name: 'window', x: 5, y: 20, width: 25, height: 30, description: 'Pencere önü' }
            ]
        },
        birthday: {
            zones: [
                { name: 'table', x: 25, y: 60, width: 50, height: 20, description: 'Masa üstü' },
                { name: 'cake', x: 42, y: 50, width: 16, height: 15, description: 'Pasta üstü' },
                { name: 'balloons', x: 30, y: 15, width: 40, height: 30, description: 'Balon alanı' }
            ]
        },
        halloween: {
            zones: [
                { name: 'house', x: 65, y: 35, width: 25, height: 30, description: 'Ev önü' },
                { name: 'graveyard', x: 20, y: 65, width: 60, height: 25, description: 'Mezarlık' },
                { name: 'sky', x: 20, y: 20, width: 40, height: 30, description: 'Gökyüzü' }
            ]
        },
        baby: {
            zones: [
                { name: 'crib', x: 35, y: 45, width: 30, height: 25, description: 'Beşik içi' },
                { name: 'shelf1', x: 60, y: 20, width: 30, height: 10, description: 'Üst raf' },
                { name: 'shelf2', x: 10, y: 40, width: 25, height: 10, description: 'Alt raf' },
                { name: 'rug', x: 30, y: 75, width: 40, height: 15, description: 'Halı üstü' }
            ]
        },
        graduation: {
            zones: [
                { name: 'sky', x: 20, y: 25, width: 60, height: 40, description: 'Gökyüzü (kepeler)' },
                { name: 'podium', x: 42, y: 65, width: 16, height: 15, description: 'Podyum' },
                { name: 'gate', x: 30, y: 70, width: 40, height: 20, description: 'Kapı önü' }
            ]
        },
        farewell: {
            zones: [
                { name: 'desk', x: 10, y: 55, width: 30, height: 20, description: 'Masa üstü' },
                { name: 'suitcase', x: 60, y: 70, width: 25, height: 15, description: 'Valiz içi' },
                { name: 'corkboard', x: 65, y: 30, width: 25, height: 30, description: 'Pano' }
            ]
        },
        love: {
            zones: [
                { name: 'blanket', x: 30, y: 60, width: 40, height: 20, description: 'Piknik örtüsü' },
                { name: 'table', x: 42, y: 55, width: 16, height: 15, description: 'Masa üstü' },
                { name: 'grass', x: 15, y: 75, width: 70, height: 15, description: 'Çimen' }
            ]
        },
        getwell: {
            zones: [
                { name: 'chair', x: 35, y: 50, width: 30, height: 25, description: 'Koltuk' },
                { name: 'table', x: 70, y: 60, width: 15, height: 15, description: 'Sehpa' },
                { name: 'fireplace', x: 10, y: 65, width: 18, height: 20, description: 'Şömine rafı' }
            ]
        }
    };

    // Get current theme configuration
    const currentTheme = '<?php echo $wall['theme_slug']; ?>';
    const currentThemeConfig = themeConfigs[currentTheme] || { zones: [] };


    setInterval(() => {
        const now = Date.now();
        const diff = eventTime - now;
        if (diff <= 0) {
            document.getElementById('countdown').innerText = '🎉 Açıldı!';
            return;
        }
        const d = Math.floor(diff / 86400000);
        const h = Math.floor((diff % 86400000) / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);
        document.getElementById('countdown').innerText = `${d}g ${h}s ${m}d ${s}sn`;
    }, 1000);

    function showGiftForm() {
        document.getElementById('giftFormModal').classList.remove('hidden');
        document.getElementById('addGiftBtn').classList.add('hidden');
    }

    function closeGiftForm() {
        document.getElementById('giftFormModal').classList.add('hidden');
        document.getElementById('addGiftBtn').classList.remove('hidden');
        waitingForPosition = false;
        document.getElementById('positionMarker').classList.add('hidden');
        document.getElementById('saveBtn').classList.add('hidden');
    }

    function startPositionSelect() {
        const name = document.getElementById('sender_name').value.trim();
        const msg = document.getElementById('gift_message').value.trim();
        const captcha = document.getElementById('captcha_answer').value.trim();

        if (!name || !msg) {
            alert('Lütfen tüm alanları doldurun!');
            return;
        }

        if (!captcha) {
            alert('Lütfen güvenlik doğrulamasını yapın!');
            return;
        }

        document.getElementById('giftFormModal').classList.add('hidden');
        waitingForPosition = true;
        document.getElementById('positionMarker').classList.remove('hidden');
        alert('🎯 Hediyeni koymak istediğin yeri tıkla!');
    }


    document.getElementById('wall-container').addEventListener('mousemove', (e) => {
        if (waitingForPosition) {
            document.getElementById('positionMarker').style.left = e.clientX + 'px';
            document.getElementById('positionMarker').style.top = e.clientY + 'px';
        }
    });

    // Touch support for mobile
    document.getElementById('wall-container').addEventListener('touchmove', (e) => {
        if (waitingForPosition && e.touches[0]) {
            e.preventDefault();
            const touch = e.touches[0];
            document.getElementById('positionMarker').style.left = touch.clientX + 'px';
            document.getElementById('positionMarker').style.top = touch.clientY + 'px';
        }
    }, { passive: false });

    document.getElementById('wall-container').addEventListener('click', (e) => {
        if (waitingForPosition) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;

            // Responsive boundaries to prevent gift overflow
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
            const xMin = isMobile ? 8 : (isTablet ? 6 : 5);
            const xMax = isMobile ? 88 : (isTablet ? 92 : 95);
            const yMin = isMobile ? 15 : (isTablet ? 12 : 10);
            const yMax = isMobile ? 82 : (isTablet ? 88 : 90);

            selectedPos = {
                x: Math.max(xMin, Math.min(xMax, x)),
                y: Math.max(yMin, Math.min(yMax, y))
            };

            waitingForPosition = false;
            document.getElementById('positionMarker').classList.add('hidden');
            document.getElementById('saveBtn').classList.remove('hidden');
        }
    });

    // Touch end support for mobile
    document.getElementById('wall-container').addEventListener('touchend', (e) => {
        if (waitingForPosition && e.changedTouches[0]) {
            e.preventDefault();
            const touch = e.changedTouches[0];
            const rect = e.currentTarget.getBoundingClientRect();
            const x = ((touch.clientX - rect.left) / rect.width) * 100;
            const y = ((touch.clientY - rect.top) / rect.height) * 100;

            // Responsive boundaries to prevent gift overflow
            const isMobile = window.innerWidth < 768;
            const isTablet = window.innerWidth >= 768 && window.innerWidth < 1024;
            const xMin = isMobile ? 8 : (isTablet ? 6 : 5);
            const xMax = isMobile ? 88 : (isTablet ? 92 : 95);
            const yMin = isMobile ? 15 : (isTablet ? 12 : 10);
            const yMax = isMobile ? 82 : (isTablet ? 88 : 90);

            selectedPos = {
                x: Math.max(xMin, Math.min(xMax, x)),
                y: Math.max(yMin, Math.min(yMax, y))
            };

            waitingForPosition = false;
            document.getElementById('positionMarker').classList.add('hidden');
            document.getElementById('saveBtn').classList.remove('hidden');
        }
    }, { passive: false });

    function submitGift() {
        if (!selectedPos) return;

        document.getElementById('final_sender').value = document.getElementById('sender_name').value;
        document.getElementById('final_gift').value = document.getElementById('gift_type').value;
        document.getElementById('final_message').value = document.getElementById('gift_message').value;
        document.getElementById('final_captcha').value = document.getElementById('captcha_answer').value;
        document.getElementById('final_x').value = selectedPos.x;
        document.getElementById('final_y').value = selectedPos.y;

        document.getElementById('finalSubmitForm').submit();
    }

    function viewGift(icon, sender, message, locked) {
        document.getElementById('viewIcon').innerText = icon;
        document.getElementById('viewSender').innerText = sender;

        if (locked) {
            const eventDate = new Date('<?php echo $wall['event_time']; ?>');
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
            const formattedDate = eventDate.toLocaleDateString('tr-TR', options);
            document.getElementById('viewMessage').innerHTML = `<span class="text-yellow-400">🔒 Bu mesaj kilitli</span><br><span class="text-sm text-slate-400 mt-2 block">Açılış: ${formattedDate}</span>`;
            document.getElementById('viewMessage').classList.remove('blur-sm');
        } else {
            document.getElementById('viewMessage').innerText = message;
            document.getElementById('viewMessage').classList.remove('blur-sm');
        }

        document.getElementById('viewModal').classList.remove('hidden');
    }

    function closeViewModal() {
        document.getElementById('viewModal').classList.add('hidden');
    }
</script>

<!-- Minimal Wall Footer -->
<a href="index.php"
    class="fixed bottom-4 left-4 z-20 flex items-center gap-2 bg-black/30 backdrop-blur-sm px-3 py-1.5 rounded-full text-white/40 hover:text-white/80 hover:bg-black/50 transition-all text-xs group">
    <span
        class="w-4 h-4 bg-gradient-to-br from-primary to-secondary rounded flex items-center justify-center text-[8px] group-hover:scale-110 transition-transform">✨</span>
    <span>Magical Moments</span>
</a>

<?php include 'includes/footer.php'; ?>