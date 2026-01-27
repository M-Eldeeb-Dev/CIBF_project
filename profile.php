<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'volunteer') {
    header('Location: index.php');
    exit;
}
$volunteer_name = $_SESSION['user_name'] ?? 'متطوع';
$volunteer_code = $_SESSION['user_code'] ?? 'N/A';
$volunteer_group = $_SESSION['user_group'] ?? 'N/A';
$volunteer_period = $_SESSION['user_period'] ?? 'N/A';
$volunteer_sector = $_SESSION['user_sector'] ?? 'N/A';
$volunteer_break1 = $_SESSION['user_break1'] ?? 'N/A';
$volunteer_break2 = $_SESSION['user_break2'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الملف الشخصي - أنا متطوع</title>
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
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-primary text-white p-6 rounded-b-3xl shadow-lg mb-6">
        <h1 class="text-2xl font-bold text-center">الملف الشخصي</h1>
    </div>

    <div class="max-w-md mx-auto px-4">
        <!-- Profile Info -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <div class="w-24 h-24 bg-gray-200 rounded-full mx-auto mb-4 overflow-hidden border-4 border-yellow-400">
                <img src="images/logo.jpg" alt="Profile" class="w-full h-full object-cover">
            </div>
            <h2 class="text-xl font-bold text-center text-dark mb-1" id="profile-name">
                <?php echo htmlspecialchars($volunteer_name); ?>
            </h2>
            <p class="text-center text-gray-500 text-sm mb-2" id="presence-status">جاري التحميل...</p>

            <!-- Presence Badge -->
            <div class="flex justify-center mb-4">
                <span id="presence-badge" class="bg-gray-200 text-gray-600 px-4 py-1 rounded-full text-sm font-bold">
                    جاري التحميل...
                </span>
            </div>

            <div class="space-y-4" id="profile-details">
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">الكود</span>
                    <span class="text-dark font-semibold"><?php echo htmlspecialchars($volunteer_code); ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">المجموعة</span>
                    <span class="text-dark font-semibold"
                        id="profile-group"><?php echo htmlspecialchars($volunteer_group); ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">الفترة</span>
                    <span class="text-dark font-semibold"
                        id="profile-period"><?php echo htmlspecialchars($volunteer_period); ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">القطاع</span>
                    <span class="text-dark font-semibold"
                        id="profile-sector"><?php echo htmlspecialchars($volunteer_sector); ?></span>
                </div>
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">القاعة الحالية</span>
                    <span class="text-dark font-semibold" id="profile-hall">جاري التحميل...</span>
                </div>
                <div class="flex items-center justify-between py-2 border-b">
                    <span class="text-gray-600">Break 1</span>
                    <span class="text-dark font-semibold"><?php echo htmlspecialchars($volunteer_break1); ?></span>
                </div>
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-600">Break 2</span>
                    <span class="text-dark font-semibold"><?php echo htmlspecialchars($volunteer_break2); ?></span>
                </div>
            </div>
        </div>

        <a href="logout.php"
            class="block w-full text-center bg-red-100 text-red-700 font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
            تسجيل الخروج
        </a>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
        <div class="max-w-md mx-auto flex items-center justify-around py-3">
            <a href="volunteer-dashboard.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                </svg>
                <span class="text-xs">الرئيسية</span>
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

            <a href="profile.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                </svg>
                <span class="text-xs font-semibold">الملف</span>
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

        const volunteerCode = '<?php echo addslashes($volunteer_code); ?>';

        async function loadVolunteerData() {
            try {
                const data = await getVolunteerByCode(volunteerCode);
                if (data) {
                    updateUI(data);
                }
            } catch (error) {
                console.error('Error loading volunteer data:', error);
                document.getElementById('presence-status').textContent = 'متطوع نشط';
            }
        }

        function updateUI(data) {
            // Update status text
            document.getElementById('presence-status').textContent = 'متطوع نشط';

            // Update presence badge
            const presenceBadge = document.getElementById('presence-badge');
            const isPresent = data.is_present === true || data.is_occupied === true;

            if (isPresent) {
                presenceBadge.textContent = '✓ متواجد حالياً';
                presenceBadge.className = 'bg-green-100 text-green-700 px-4 py-1 rounded-full text-sm font-bold';
            } else {
                presenceBadge.textContent = 'غير متواجد';
                presenceBadge.className = 'bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-sm font-bold';
            }

            // Update hall
            document.getElementById('profile-hall').textContent = data.hall_id ? `قاعة ${data.hall_id}` : 'غير محدد';

            // Update group if different
            if (data.group) {
                document.getElementById('profile-group').textContent = data.group;
            }
            if (data.period) {
                document.getElementById('profile-period').textContent = data.period;
            }
            if (data.sector) {
                document.getElementById('profile-sector').textContent = data.sector;
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