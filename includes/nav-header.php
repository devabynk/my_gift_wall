<!-- Navigation Header -->
<header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" id="main-header">
    <div class="bg-dark/80 backdrop-blur-xl border-b border-white/10">
        <div class="max-w-6xl mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <!-- Logo -->
                <a href="index.php" class="flex items-center gap-3 group">
                    <div
                        class="w-10 h-10 bg-gradient-to-br from-primary to-secondary rounded-xl flex items-center justify-center text-white text-xl shadow-lg group-hover:scale-110 transition-transform">
                        ✨
                    </div>
                    <span
                        class="text-xl font-bold bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent hidden sm:block">
                        Magical Moments
                    </span>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-6">
                    <a href="index.php" class="text-slate-300 hover:text-white transition-colors font-medium">
                        Ana Sayfa
                    </a>
                    <a href="create.php"
                        class="px-5 py-2.5 bg-gradient-to-r from-primary to-secondary rounded-full text-white font-semibold shadow-lg shadow-primary/30 hover:shadow-primary/50 hover:scale-105 transition-all">
                        Duvar Oluştur
                    </a>
                </nav>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-btn" class="md:hidden p-2 text-slate-300 hover:text-white transition-colors"
                    onclick="toggleMobileMenu()">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4 border-t border-white/10 pt-4">
                <nav class="flex flex-col gap-3">
                    <a href="index.php" class="text-slate-300 hover:text-white transition-colors font-medium py-2">
                        Ana Sayfa
                    </a>
                    <a href="create.php"
                        class="px-5 py-3 bg-gradient-to-r from-primary to-secondary rounded-xl text-white font-semibold text-center shadow-lg">
                        Duvar Oluştur
                    </a>
                </nav>
            </div>
        </div>
    </div>
</header>

<!-- Spacer for fixed header -->
<div class="h-16"></div>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }

    // Header scroll effect
    window.addEventListener('scroll', () => {
        const header = document.getElementById('main-header');
        if (window.scrollY > 50) {
            header.classList.add('shadow-xl');
        } else {
            header.classList.remove('shadow-xl');
        }
    });
</script>