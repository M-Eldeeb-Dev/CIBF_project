<?php
require_once 'includes/auth-guard.php';
requireAuth('volunteer');

$volunteer_name = $_SESSION['user_name'] ?? 'متطوع';
$volunteer_code = $_SESSION['user_code'] ?? 'N/A';

$volunteer_loc1 = $_SESSION['user_loc1'] ?? 'N/A';
$volunteer_loc2 = $_SESSION['user_loc2'] ?? 'N/A';
$volunteer_loc3 = $_SESSION['user_loc3'] ?? 'N/A';
$volunteer_loc4 = $_SESSION['user_loc4'] ?? 'N/A';
$volunteer_period = $_SESSION['user_period'] ?? 'N/A';

// Read Notes
require_once __DIR__ . '/controllers/notes_loader.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>لوحة المتطوع - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: env(safe-area-inset-bottom, 120px);
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile-First Enhancements */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            button,
            a {
                min-height: 44px;
                /* Minimum touch target */
                touch-action: manipulation;
            }

            input {
                font-size: 16px !important;
                /* Prevent zoom on iOS */
            }
        }

        /* Safe Area Support */
        .safe-pb {
            padding-bottom: env(safe-area-inset-bottom, 20px);
        }

        .bg-primary {
            background-color: #2570d8;
        }

        .bg-dark-blue {
            background-color: #0643aa;
        }

        .bg-yellow {
            background-color: #dfd63e;
        }

        .bg-light {
            background-color: #e5e6e2;
        }

        .text-dark {
            color: #232a34;
        }

        /* Marquee Animation */
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-content {
            display: inline-block;
            animation: marquee 20s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }

        .presence-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.7;
            }
        }
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="assets/images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-primary text-white p-6 rounded-b-3xl shadow-lg">
        <div class="max-w-md mx-auto">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h1 class="text-2xl font-bold">مرحباً
                        <span id="volunteer-name"><?php echo htmlspecialchars($volunteer_name); ?></span>!
                    </h1>
                    <p class="text-sm opacity-90">الكود: <span
                            id="volunteer-code"><?php echo htmlspecialchars($volunteer_code); ?></span></p>
                </div>
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg overflow-hidden">
                    <img src="assets/images/logo.jpg" alt="Logo" class="w-full h-full object-cover">
                </div>
            </div>
            <!-- Presence Status -->
            <div id="presence-status" class="bg-white/20 rounded-xl p-3 flex items-center justify-between">
                <span>حالة التواجد</span>
                <span id="presence-badge"
                    class="presence-badge bg-yellow-400 text-dark px-3 py-1 rounded-full text-sm font-bold">
                    جاري التحميل...
                </span>
            </div>
        </div>
    </div>

    <!-- Notes Ticker -->
    <?php include 'includes/notes_ticker.php'; ?>

    <!-- Callback Status Banner (dynamically shown) -->
    <div id="callback-banner" class="max-w-md mx-auto px-4 hidden">
        <div id="callback-banner-content" class="rounded-xl p-4 mb-4 flex items-center gap-3 shadow-lg">
            <span id="callback-banner-icon"></span>
            <div>
                <p id="callback-banner-title" class="font-bold"></p>
                <p id="callback-banner-text" class="text-sm opacity-90"></p>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-md mx-auto px-4 py-6 pb-24">
        <!-- Google Drive Viewer -->
        <div class="bg-white rounded-3xl shadow-xl p-2 mb-6 overflow-hidden">
            <h2 class="text-2xl font-bold text-center text-blue-600 underline mb-4 p-2">خريطة القاعات</h2>
            <iframe src="https://drive.google.com/file/d/1yNG_rtMbvMkDPGxaw6MRewbTYNm4PXsO/preview" width="100%"
                height="480" allow="autoplay" class="rounded-2xl border-none">
            </iframe>
        </div>

        <!-- Profile Info Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-dark mb-4">معلومات المتطوع</h2>

            <div class="space-y-4" id="volunteer-info">
                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600">الحالة</span>
                    <span id="status-badge"
                        class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-sm font-semibold">نشط</span>
                </div>
            </div>

            <!-- Enhanced Location Display Card -->
            <div id="location-card-container" class="mt-4 hidden">
                <div id="location-card" class="rounded-2xl p-4 transition-all duration-300 shadow-lg">
                    <div class="flex items-center gap-4">
                        <div id="location-icon"
                            class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg bg-white/20"></div>
                        <div class="flex-1">
                            <p id="location-label" class="text-sm opacity-80">موقعك الحالي</p>
                            <p id="current-hall" class="font-bold text-xl">جاري التحميل...</p>
                            <p id="current-location" class="text-xs opacity-70 mt-1"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fallback No Location Message -->
            <div id="no-location-msg" class="mt-4 bg-gray-50 rounded-2xl p-4 text-center hidden">
                <svg class="w-10 h-10 mx-auto text-gray-300 mb-2" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <p class="text-gray-500 font-semibold">لم يتم تعيين موقع لك بعد</p>
            </div>

            <div class="space-y-4 mt-4" id="volunteer-extra-info">
                <div class="flex items-center justify-between py-3">
                    <span class="text-gray-600">تاريخ الانضمام</span>
                    <span class="text-dark font-semibold">21 يناير 2026</span>
                </div>
            </div>
        </div>

        <!-- Add New Tip Form -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-dark mb-4 flex items-center gap-2">
                <span class="bg-yellow-100 p-2 rounded-lg text-yellow-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                </span>
                إضافة نصيحة جديدة
            </h2>

            <?php
            // Load hall names from JSON
            $halls_advices_path = 'data/json_files/halls_advices.json';
            $halls_data = [];
            if (file_exists($halls_advices_path)) {
                $content = file_get_contents($halls_advices_path);
                if (strpos($content, "\xEF\xBB\xBF") === 0)
                    $content = substr($content, 3);
                $json = json_decode($content, true);
                $halls_data = $json['halls'] ?? [];
            }
            ?>

            <form id="addTipForm" class="space-y-4" onsubmit="submitAdvice(event)">
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">القاعة</label>
                    <select id="tipHall" required
                        class="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition bg-gray-50">
                        <option value="">اختر القاعة...</option>
                        <?php foreach ($halls_data as $key => $hall): ?>
                            <option value="<?php echo htmlspecialchars($key); ?>">
                                <?php echo htmlspecialchars($hall['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">دار النشر / النصيحة</label>
                    <input type="text" id="tipText" required placeholder="مثال: دار الشروق"
                        class="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition bg-gray-50">
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1">الكود (اختياري)</label>
                    <input type="text" id="tipCode" placeholder="مثال: A15"
                        class="w-full p-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition bg-gray-50">
                </div>

                <div id="tipMessage" class="hidden p-3 rounded-xl text-sm font-bold text-center"></div>

                <button type="submit"
                    class="w-full py-3 bg-primary text-white font-bold rounded-xl shadow-lg shadow-blue-200 hover:bg-blue-700 transition active:scale-95">
                    إضافة
                </button>
            </form>
        </div>

        <script>
            async function submitAdvice(e) {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                const msg = document.getElementById('tipMessage');
                const hall = document.getElementById('tipHall').value;
                const text = document.getElementById('tipText').value;
                const code = document.getElementById('tipCode').value;

                btn.disabled = true;
                btn.innerHTML = '<span class="animate-pulse">جاري الإضافة...</span>';
                msg.classList.add('hidden');

                try {
                    const response = await fetch('controllers/add_advice.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ hall, text, code })
                    });

                    const result = await response.json();

                    msg.textContent = result.message;
                    msg.classList.remove('hidden');

                    if (result.success) {
                        msg.className = 'p-3 rounded-xl text-sm font-bold text-center bg-green-100 text-green-700';
                        document.getElementById('addTipForm').reset();
                        setTimeout(() => msg.classList.add('hidden'), 3000);
                    } else {
                        msg.className = 'p-3 rounded-xl text-sm font-bold text-center bg-red-100 text-red-700';
                    }
                } catch (error) {
                    console.error(error);
                    msg.textContent = 'حدث خطأ في الاتصال';
                    msg.className = 'p-3 rounded-xl text-sm font-bold text-center bg-red-100 text-red-700';
                    msg.classList.remove('hidden');
                } finally {
                    btn.disabled = false;
                    btn.textContent = 'إضافة';
                }
            }
        </script>

        <!-- CTA Button -->
        <a href="halls.php"
            class="block w-full text-center bg-yellow text-dark font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 mb-4">
            تصفح قاعات المعرض
        </a>

        <a href="controllers/logout.php"
            class="block w-full text-center bg-red-100 text-red-700 font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
            تسجيل الخروج
        </a>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
        <div class="max-w-md mx-auto flex items-center justify-around py-3">
            <a href="volunteer-dashboard.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                </svg>
                <span class="text-xs font-semibold">الرئيسية</span>
            </a>

            <a href="halls.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-xs">القاعات</span>
            </a>

            <a href="guide.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                </svg>
                <span class="text-xs">الدليل</span>
            </a>

            <a href="profile.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                </svg>
                <span class="text-xs">الملف</span>
            </a>

            <a href="volunteer-map.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <span class="text-xs">موقعي</span>
            </a>

            <a href="links.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-xs">روابط</span>
            </a>
        </div>
    </div>

    <!-- Load real-time data from Supabase -->
    <script type="module">
        import { getVolunteerByCode, subscribeToVolunteers } from './assets/js/volunteers-service.js?v=<?php echo time(); ?>';
        import { getSession } from './assets/js/auth-service.js?v=<?php echo time(); ?>';

        const volunteerCode = '<?php echo addslashes($volunteer_code); ?>';

        async function loadVolunteerData() {
            try {
                const data = await getVolunteerByCode(volunteerCode);
                if (data) {
                    updateUI(data);
                }
            } catch (error) {
                console.error('Error loading volunteer data:', error);
            }
        }

        function updateUI(data) {
            // Configuration for Hall Styles
            const HALL_CONFIG = {
                101: {
                    name: 'البوابة الرئيسية',
                    label: 'موقعك الحالي',
                    desc: 'نقطة استقبال الزوار',
                    style: 'from-emerald-500 to-teal-600',
                    icon: '<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19 19V5c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2v14H3v2h18v-2h-2zm-6 0h-2v-2h2v2zm0-4h-2V9h2v6z"/></svg>'
                },
                102: {
                    name: 'غرفة المعلومات',
                    label: 'موقعك الحالي',
                    desc: 'مركز الدعم والمساعدة',
                    style: 'from-violet-500 to-purple-600',
                    icon: '<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>'
                },
                default: {
                    name: (id) => (id >= 1 && id <= 5) ? `قاعة ${id}` : 'غير محدد',
                    label: 'القاعة الحالية',
                    desc: (loc) => `موقعك: ${loc}`,
                    style: 'from-blue-500 to-blue-600',
                    icon: '<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>'
                }
            };

            // DOM Elements
            const els = {
                badge: document.getElementById('presence-badge'),
                container: document.getElementById('location-card-container'),
                noLocMsg: document.getElementById('no-location-msg'),
                card: document.getElementById('location-card'),
                icon: document.getElementById('location-icon'),
                label: document.getElementById('location-label'),
                hall: document.getElementById('current-hall'),
                loc: document.getElementById('current-location')
            };

            // Presence Logic
            // Only consider occupied if there is a valid location string
            const isOccupied = data.is_occupied === true && data.current_loc && data.current_loc !== '';
            const isPresent = data.is_present === true || isOccupied;

            if (isPresent) {
                els.badge.textContent = 'متواجد';
                els.badge.className = 'presence-badge bg-green-400 text-white px-3 py-1 rounded-full text-sm font-bold';
            } else {
                els.badge.textContent = 'غير متواجد';
                els.badge.className = 'presence-badge bg-yellow-400 text-dark px-3 py-1 rounded-full text-sm font-bold';
            }

            // Location Logic
            const hasLocation = data.hall_id && data.current_loc;

            if (hasLocation) {
                els.container.classList.remove('hidden');
                els.noLocMsg.classList.add('hidden');

                // Determine config ID: prioritize location code (101/102) over actual hall_id
                // This fixes cases where hall_id might be 1 but location is explicitly 101 (Gate)
                let configId = data.hall_id;
                if (data.current_loc == '101') configId = 101;
                else if (data.current_loc == '102') configId = 102;

                const config = HALL_CONFIG[configId] || HALL_CONFIG.default;

                // apply styles
                els.card.className = `rounded-2xl p-4 transition-all duration-300 shadow-lg bg-gradient-to-r text-white ${config.style}`;
                els.icon.className = 'w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg bg-white/20';
                els.icon.innerHTML = config.icon;

                // set text content
                els.hall.className = 'font-bold text-xl text-white';

                if (configId == 101 || configId == 102) {
                    els.hall.textContent = config.name;
                    els.label.textContent = config.label;
                    els.loc.textContent = config.desc;
                } else {
                    els.hall.textContent = typeof config.name === 'function' ? config.name(data.hall_id) : config.name;
                    els.label.textContent = config.label;
                    els.loc.textContent = typeof config.desc === 'function' ? config.desc(data.current_loc) : config.desc;
                }

            } else {
                els.container.classList.add('hidden');
                els.noLocMsg.classList.remove('hidden');
            }

            // Update callback banner
            updateCallbackBanner(data);
        }

        function updateCallbackBanner(data) {
            const banner = document.getElementById('callback-banner');
            const content = document.getElementById('callback-banner-content');
            const icon = document.getElementById('callback-banner-icon');
            const title = document.getElementById('callback-banner-title');
            const text = document.getElementById('callback-banner-text');

            const status = data.callback_comment_approval;

            if (!status) {
                banner.classList.add('hidden');
                return;
            }

            banner.classList.remove('hidden');

            if (status === 'pending') {
                content.className = 'rounded-xl p-4 mb-4 flex items-center gap-3 shadow-lg bg-yellow-500 text-white';
                icon.innerHTML = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>';
                title.textContent = 'طلب العودة قيد المراجعة';
                text.textContent = 'سيتم إشعارك عند الرد على طلبك';
            } else if (status === 'approved') {
                content.className = 'rounded-xl p-4 mb-4 flex items-center gap-3 shadow-lg bg-green-500 text-white';
                icon.innerHTML = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>';
                title.textContent = 'تمت الموافقة على طلبك! 🎉';
                text.textContent = 'تواصل مع الإدارة لتعيينك في موقع جديد';
            } else if (status === 'rejected') {
                content.className = 'rounded-xl p-4 mb-4 flex items-center gap-3 shadow-lg bg-red-500 text-white';
                icon.innerHTML = '<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>';
                title.textContent = 'تم رفض طلب العودة';
                text.textContent = 'يمكنك تقديم طلب جديد من الملف الشخصي';
            }
        }

        // Initial load
        loadVolunteerData();

        // Subscribe to real-time updates
        subscribeToVolunteers((payload) => {
            if (payload.new?.volunteerCode === volunteerCode) {
                updateUI(payload.new);
            }
        });
    </script>
</body>

</html>