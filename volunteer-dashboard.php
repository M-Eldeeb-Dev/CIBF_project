<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header('Location: index.php');
    exit;
}
$volunteer_name = $_SESSION['user_name'] ?? 'متطوع';
$volunteer_code = $_SESSION['user_code'] ?? 'N/A';

$volunteer_loc1 = $_SESSION['user_loc1'] ?? 'N/A';
$volunteer_loc2 = $_SESSION['user_loc2'] ?? 'N/A';
$volunteer_loc3 = $_SESSION['user_loc3'] ?? 'N/A';
$volunteer_loc4 = $_SESSION['user_loc4'] ?? 'N/A';
$volunteer_period = $_SESSION['user_period'] ?? 'N/A';

// Read Notes
require_once 'notes_loader.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>لوحة المتطوع - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: 80px;
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
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
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
                    <img src="images/logo.jpg" alt="Logo" class="w-full h-full object-cover">
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
    <?php include 'notes_ticker.php'; ?>

    <!-- Main Content -->
    <div class="max-w-md mx-auto px-4 py-6">
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

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600">القاعة الحالية</span>
                    <span class="text-dark font-semibold" id="current-hall">جاري التحميل...</span>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-100">
                    <span class="text-gray-600">الموقع</span>
                    <span class="text-dark font-semibold" id="current-location">القاهرة، مصر</span>
                </div>

                <div class="flex items-center justify-between py-3">
                    <span class="text-gray-600">تاريخ الانضمام</span>
                    <span class="text-dark font-semibold">21 يناير 2026</span>
                </div>
            </div>
        </div>

        <!-- Current Tasks -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-dark mb-4">المهام الموكلة (التدوير)</h2>

            <div class="space-y-3" id="tasks-container">
                <?php
                $tasks = [
                    ['name' => $volunteer_loc1, 'time' => $volunteer_period, 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
                    ['name' => $volunteer_loc2, 'time' => 'التدوير التالي', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                    ['name' => $volunteer_loc3, 'time' => 'التدوير التالي', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
                    ['name' => $volunteer_loc4, 'time' => 'التدوير الأخير', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2']
                ];

                foreach ($tasks as $task):
                    if ($task['name'] === 'N/A')
                        continue;
                    ?>
                    <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-xl">
                        <div class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="<?php echo $task['icon']; ?>"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-dark"><?php echo htmlspecialchars($task['name']); ?></h3>
                            <p class="text-sm text-gray-600"><?php echo htmlspecialchars($task['time']); ?></p>
                        </div>
                    </div>
                <?php endforeach; ?>

                <?php if ($volunteer_loc1 == 'N/A'): ?>
                    <p class="text-center text-gray-500">لا توجد مهام محددة حالياً</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- CTA Button -->
        <a href="halls.php"
            class="block w-full text-center bg-yellow text-dark font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 mb-4">
            تصفح قاعات المعرض
        </a>

        <a href="logout.php"
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
        import { getVolunteerByCode, subscribeToVolunteers } from './js/volunteers-service.js';
        import { getSession } from './js/auth-service.js';

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
            // Update presence badge
            const presenceBadge = document.getElementById('presence-badge');
            const isPresent = data.is_present === true || data.is_occupied === true;

            if (isPresent) {
                presenceBadge.textContent = 'متواجد';
                presenceBadge.className = 'presence-badge bg-green-400 text-white px-3 py-1 rounded-full text-sm font-bold';
            } else {
                presenceBadge.textContent = 'غير متواجد';
                presenceBadge.className = 'presence-badge bg-yellow-400 text-dark px-3 py-1 rounded-full text-sm font-bold';
            }

            // Update hall info
            const hallElement = document.getElementById('current-hall');
            hallElement.textContent = data.hall_id ? `قاعة ${data.hall_id}` : 'غير محدد';

            // Update location
            const locationElement = document.getElementById('current-location');
            locationElement.textContent = data.current_loc || 'القاهرة، مصر';
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