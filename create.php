<?php
require_once 'config.php';
require_once 'includes/captcha.php';

$errorMessage = '';
$currentStep = 1;
$postedName = '';
$postedEventType = '';
$postedEventTime = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $creatorName = trim($_POST['creator_name'] ?? '');
    $eventType = trim($_POST['event_type'] ?? '');
    $eventTime = trim($_POST['event_time'] ?? '');
    $currentStep = intval($_POST['current_step'] ?? 1);

    // Preserve form values for re-display
    $postedName = $creatorName;
    $postedEventType = $eventType;
    $postedEventTime = $eventTime;

    if ($creatorName && $eventType && $eventTime) {
        $eventDateTime = DateTime::createFromFormat('Y-m-d\TH:i', $eventTime);
        $now = new DateTime();

        if ($eventDateTime && $eventDateTime > $now) {
            $mysqlDateTime = $eventDateTime->format('Y-m-d H:i:s');

            try {
                $stmtTheme = $pdo->prepare("SELECT id FROM themes WHERE slug = ?");
                $stmtTheme->execute([$eventType]);
                $themeRow = $stmtTheme->fetch();

                if ($themeRow) {
                    $wallUuid = bin2hex(random_bytes(16));
                    $stmt = $pdo->prepare("INSERT INTO walls (wall_uuid, creator_name, event_type, theme_id, event_time) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$wallUuid, $creatorName, $eventType, $themeRow['id'], $mysqlDateTime]);

                    header("Location: success.php?uuid=" . $wallUuid);
                    exit;
                } else {
                    $errorMessage = "Geçersiz tema seçimi!";
                }
            } catch (PDOException $e) {
                error_log("DB Error: " . $e->getMessage());
                $errorMessage = "Veritabanı hatası oluştu!";
            }
        } else {
            $errorMessage = "Lütfen gelecekte bir tarih seçin!";
        }
    } else {
        $errorMessage = "Lütfen tüm alanları doldurun!";
    }
}

$themes = [];
try {
    $stmt = $pdo->query("SELECT * FROM themes ORDER BY id ASC");
    $themes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Theme fetch error: " . $e->getMessage());
}

$pageTitle = "Sihirbaz - Duvarını Oluştur";
$bodyClass = "bg-dark text-white min-h-screen flex items-center justify-center p-4 bg-[url('https://source.unsplash.com/random/1920x1080/?abstract,dark')] bg-cover bg-center bg-no-repeat relative";
$customStyles = "
.wizard-step { display: none; }
.wizard-step.active { display: block; animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
";
include 'includes/header.php';
?>

<div class="absolute inset-0 bg-dark/80 backdrop-blur-sm"></div>

<!-- Home Link -->
<a href="index.php"
    class="fixed top-4 left-4 z-20 flex items-center gap-2 bg-dark/80 backdrop-blur-xl border border-white/10 px-4 py-2 rounded-full text-slate-300 hover:text-white hover:border-primary/50 transition-all">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    <span class="hidden sm:inline text-sm font-medium">Ana Sayfa</span>
</a>

<div class="relative w-full max-w-3xl z-10">
    <div class="flex items-center justify-between mb-8 relative px-4">
        <div class="absolute left-0 top-1/2 w-full h-1 bg-slate-700 -z-10 rounded"></div>
        <div class="step-dot w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-600 flex items-center justify-center font-bold text-slate-400 transition-all duration-300 active-step ring-4 ring-slate-900"
            data-step="1">1</div>
        <div class="step-dot w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-600 flex items-center justify-center font-bold text-slate-400 transition-all duration-300"
            data-step="2">2</div>
        <div class="step-dot w-10 h-10 rounded-full bg-slate-800 border-2 border-slate-600 flex items-center justify-center font-bold text-slate-400 transition-all duration-300"
            data-step="3">3</div>
    </div>

    <div class="bg-white/10 backdrop-blur-xl border border-white/20 rounded-3xl p-8 shadow-2xl">
        <?php if ($errorMessage): ?>
            <div class="bg-red-500/20 border border-red-500/50 rounded-xl p-4 mb-6 text-red-200">
                ⚠️ <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <form id="wizardForm" method="POST" action="">
            <input type="hidden" name="current_step" id="current_step" value="<?php echo $currentStep; ?>">
            <div class="wizard-step active" id="step1">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-2">Merhaba! 👋</h2>
                    <p class="text-slate-300">Seni arkadaşlarında nasıl tanıtalım? Adını yaz.</p>
                </div>
                <div class="mb-8">
                    <input type="text" name="creator_name" id="creator_name" placeholder="Örn: Melisa"
                        value="<?php echo htmlspecialchars($postedName); ?>"
                        class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-6 py-4 text-2xl text-center focus:outline-none focus:border-primary focus:ring-2 focus:ring-primary/50 transition-all"
                        onkeypress="if(event.key==='Enter'){event.preventDefault();nextStep(1)}">
                </div>
                <div class="flex justify-end">
                    <button type="button"
                        class="bg-primary hover:bg-orange-600 text-white px-8 py-3 rounded-full font-bold shadow-lg shadow-primary/30 transition-all hover:scale-105"
                        onclick="nextStep(1)">Devam Et 👉</button>
                </div>
            </div>

            <div class="wizard-step" id="step2">
                <div class="text-center mb-6">
                    <h2 class="text-3xl font-bold mb-2">Konseptini Seç 🎨</h2>
                    <p class="text-slate-300">Bu duvar hangi özel gün için?</p>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-h-[50vh] overflow-y-auto mb-8 p-1">
                    <?php foreach ($themes as $theme): ?>
                        <div class="theme-card cursor-pointer" onclick="selectTheme(this, '<?php echo $theme['slug']; ?>')">
                            <div
                                class="theme-card-inner bg-slate-800/50 border border-slate-600 hover:border-primary hover:bg-slate-700/50 rounded-xl p-4 text-center transition-all">
                                <div class="text-4xl mb-3">
                                    <?php
                                    $icons = ['birthday' => '🎂', 'christmas' => '🎄', 'love' => '❤️', 'halloween' => '🎃', 'baby' => '👶', 'graduation' => '🎓', 'farewell' => '👋', 'getwell' => '🩹'];
                                    echo $icons[$theme['slug']] ?? '🎉';
                                    ?>
                                </div>
                                <span
                                    class="font-medium text-sm text-slate-300"><?php echo htmlspecialchars($theme['name']); ?></span>
                                <div
                                    class="absolute top-2 right-2 w-6 h-6 bg-primary rounded-full text-white text-xs flex items-center justify-center opacity-0 scale-0 transition-all check-icon">
                                    ✓</div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="event_type" id="event_type"
                    value="<?php echo htmlspecialchars($postedEventType); ?>">
                <div class="flex justify-between">
                    <button type="button" class="text-slate-400 hover:text-white px-6 py-3 font-semibold"
                        onclick="prevStep(2)">Geri</button>
                    <button type="button"
                        class="bg-primary hover:bg-orange-600 text-white px-8 py-3 rounded-full font-bold shadow-lg shadow-primary/30 transition-all hover:scale-105"
                        onclick="nextStep(2)">Sonraki 👉</button>
                </div>
            </div>

            <div class="wizard-step" id="step3">
                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold mb-2">Büyük An Ne Zaman? ⏰</h2>
                    <p class="text-slate-300">Hediyeler bu tarihe kadar kilitli kalacak.</p>
                </div>
                <div class="mb-10">
                    <input type="datetime-local" name="event_time" id="event_time"
                        value="<?php echo htmlspecialchars($postedEventTime); ?>"
                        class="w-full bg-slate-800/50 border border-slate-600 rounded-xl px-6 py-4 text-xl text-center focus:outline-none focus:border-cyan-600 focus:ring-2 focus:ring-cyan-600/50 transition-all text-white">
                </div>
                <div class="flex justify-between">
                    <button type="button" class="text-slate-400 hover:text-white px-6 py-3 font-semibold"
                        onclick="prevStep(3)">Geri</button>
                    <button type="submit"
                        class="bg-gradient-to-r from-primary to-secondary hover:scale-105 text-white px-10 py-4 rounded-full font-bold text-lg shadow-lg shadow-primary/30 transition-all">✨
                        Duvarı Oluştur</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function updateSteps(step) {
        // Track current step for form resubmission
        document.getElementById('current_step').value = step;

        document.querySelectorAll('.step-dot').forEach(dot => {
            const s = parseInt(dot.dataset.step);
            dot.classList.remove('bg-primary', 'border-primary', 'text-white', 'active-step');
            if (s === step) {
                dot.classList.add('bg-primary', 'border-primary', 'text-white', 'active-step');
            } else if (s < step) {
                dot.classList.add('bg-primary', 'border-primary', 'text-white');
            }
        });
        document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
        document.getElementById(`step${step}`).classList.add('active');

        if (step === 3) {
            const now = new Date();
            now.setMinutes(now.getMinutes() + 30);
            const pad = n => n < 10 ? '0' + n : n;
            document.getElementById('event_time').min = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
        }
    }

    function nextStep(current) {
        if (current === 1 && !document.getElementById('creator_name').value.trim()) {
            alert('Lütfen ismini gir!');
            return;
        }
        if (current === 2 && !document.getElementById('event_type').value) {
            alert('Lütfen bir konsept seç!');
            return;
        }
        updateSteps(current + 1);
    }

    function prevStep(current) {
        updateSteps(current - 1);
    }

    function selectTheme(el, slug) {
        document.querySelectorAll('.theme-card-inner').forEach(div => {
            div.classList.remove('border-primary', 'bg-slate-700/80');
            div.querySelector('.check-icon').classList.remove('opacity-100', 'scale-100');
        });
        el.querySelector('.theme-card-inner').classList.add('border-primary', 'bg-slate-700/80');
        el.querySelector('.check-icon').classList.add('opacity-100', 'scale-100');
        document.getElementById('event_type').value = slug;

        const now = new Date();
        let target = null;
        const y = now.getFullYear();
        if (slug.includes('christmas')) {
            target = new Date(y, 11, 31, 23, 59);
            if (now > target) target = new Date(y + 1, 11, 31, 23, 59);
        } else if (slug.includes('halloween')) {
            target = new Date(y, 9, 31, 20, 0);
            if (now > target) target = new Date(y + 1, 9, 31, 20, 0);
        } else if (slug.includes('love')) {
            target = new Date(y, 1, 14, 20, 0);
            if (now > target) target = new Date(y + 1, 1, 14, 20, 0);
        }
        if (target) {
            const pad = n => n < 10 ? '0' + n : n;
            document.getElementById('event_time').value = `${target.getFullYear()}-${pad(target.getMonth() + 1)}-${pad(target.getDate())}T${pad(target.getHours())}:${pad(target.getMinutes())}`;
        }
    }

    // Restore step from PHP (after form error)
    const savedStep = <?php echo $currentStep; ?>;
    updateSteps(savedStep);

    // Restore theme selection if exists
    const savedTheme = '<?php echo htmlspecialchars($postedEventType); ?>';
    if (savedTheme) {
        const themeCard = document.querySelector(`.theme-card[onclick*="'${savedTheme}'"]`);
        if (themeCard) selectTheme(themeCard, savedTheme);
    }
</script>

<?php include 'includes/footer.php'; ?>