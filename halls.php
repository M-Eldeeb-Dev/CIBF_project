<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit;
}

require_once 'halls_data.php';
require_once 'notes_loader.php';

// Define Main Halls
$main_halls = [
    'Hall 1' => ['Hall 1A', 'Hall 1B', 'Hall 1C'],
    'Hall 2' => ['Hall 2A', 'Hall 2B', 'Hall 2C'],
    'Hall 3' => ['Hall 3A', 'Hall 3B', 'Hall 3C'],
    'Hall 4' => ['Hall 4A', 'Hall 4B', 'Hall 4C'],
    'Hall 5' => ['Hall 5A', 'Hall 5B', 'Hall 5C'],
    'Hall 6' => ['Hall 6A', 'Hall 6B', 'Hall 6C']
];

// Add "All" option to $main_halls for logic
$display_halls = array_merge(['All' => []], $main_halls);

$selected_main_hall_key = $_GET['hall'] ?? 'All';

// If specific hall (e.g. Hall 1A) is requested, redirect or find parent
if (isset($halls[$selected_main_hall_key])) {
    // It's a sub-hall, find parent
    foreach ($main_halls as $main => $subs) {
        if (in_array($selected_main_hall_key, $subs)) {
            $selected_main_hall_key = $main;
            break;
        }
    }
}

if (!array_key_exists($selected_main_hall_key, $display_halls)) {
    $selected_main_hall_key = 'All';
}

$search = $_GET['search'] ?? '';

// Collect publishers
$current_hall_name = $selected_main_hall_key === 'All' ? 'كل القاعات' : str_replace('Hall ', 'قاعة ', $selected_main_hall_key);
$grouped_publishers = [];
$total_publishers = 0;

if ($selected_main_hall_key === 'All') {
    foreach ($halls as $sub_hall_key => $sub_data) {
        $sub_publishers = $sub_data['publishers'] ?? [];
        if (!empty($sub_publishers)) {
            $sub_hall_name = $sub_data['name'];
            $grouped_publishers[$sub_hall_name] = $sub_publishers;
            $total_publishers += count($sub_publishers);
        }
    }
} else {
    foreach ($main_halls[$selected_main_hall_key] as $sub_hall_key) {
        if (isset($halls[$sub_hall_key])) {
            $sub_publishers = $halls[$sub_hall_key]['publishers'] ?? [];

            if (!empty($sub_publishers)) {
                $sub_hall_name = $halls[$sub_hall_key]['name'];
                $grouped_publishers[$sub_hall_name] = $sub_publishers;
                $total_publishers += count($sub_publishers);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قاعات المعرض - معرض القاهرة الدولي للكتاب 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
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

        .border-primary {
            border-color: #2570d8;
        }

        .text-primary {
            color: #2570d8;
        }

        .tab-active {
            background-color: #2570d8;
            color: white;
        }

        .publisher-card {
            transition: all 0.3s ease;
        }

        .publisher-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(37, 112, 216, 0.15);
        }

        .search-input:focus {
            border-color: #2570d8;
            box-shadow: 0 0 0 3px rgba(37, 112, 216, 0.1);
        }
    </style>
</head>

<body class="bg-light min-h-screen relative">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>
    <!-- Header -->
    <div class="bg-primary text-white p-6 rounded-b-3xl shadow-lg">
        <div class="max-w-4xl mx-auto">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold">قاعات المعرض</h1>
                    <p class="text-sm opacity-90">معرض القاهرة الدولي للكتاب 2026</p>
                </div>
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center shadow-lg overflow-hidden">
                    <img src="images/logo.jpg" alt="Logo" class="w-full h-full object-cover">
                </div>
            </div>
        </div>
    </div>

    <!-- Notes Ticker -->
    <?php include 'notes_ticker.php'; ?>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Hall Tabs -->
        <div class="flex flex-wrap gap-2 mb-6">
            <?php foreach ($display_halls as $main_key => $subs): ?>
                <?php $is_active = $main_key === $selected_main_hall_key; ?>
                <a href="?hall=<?php echo urlencode($main_key); ?>"
                    class="px-4 py-2 rounded-xl font-semibold text-sm transition-all <?php echo $is_active ? 'tab-active shadow-lg' : 'bg-white text-dark hover:bg-gray-100'; ?>">
                    <?php
                    if ($main_key === 'All')
                        echo 'الكل';
                    else
                        echo str_replace('Hall ', 'قاعة ', $main_key);
                    ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Search Box -->
        <div class="bg-white rounded-2xl shadow-md p-4 mb-6">
            <div class="flex gap-3">
                <input type="text" id="hallSearchInput" placeholder="ابحث عن دار نشر..."
                    class="search-input flex-1 px-4 py-3 rounded-xl border-2 border-gray-200 outline-none transition">
            </div>
        </div>

        <!-- Hall Info Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-dark">
                        <?php echo htmlspecialchars($current_hall_name); ?>
                    </h2>
                    <span
                        class="inline-block mt-1 bg-blue-100 text-primary px-3 py-1 rounded-full text-sm font-semibold">
                        <?php echo $selected_main_hall_key === 'All' ? 'جميع الناشرين في المعرض' : 'مجمع القاعات (A, B, C)'; ?>
                    </span>
                </div>
                <div class="text-primary text-3xl font-bold">
                    <?php echo $total_publishers; ?>
                    <span class="text-sm text-gray-600 font-normal">ناشر</span>
                </div>
            </div>



            <!-- Publishers List Grouped -->
            <div class="space-y-6">
                <?php foreach ($grouped_publishers as $sub_name => $publishers): ?>
                    <div class="border-b border-gray-100 pb-4 last:border-0">
                        <h3 class="text-lg font-bold text-gray-700 mb-3 bg-gray-50 p-2 rounded-lg inline-block">
                            <?php echo htmlspecialchars($sub_name); ?>
                        </h3>
                        <div class="space-y-3">
                            <?php foreach ($publishers as $publisher): ?>
                                <div
                                    class="publisher-card flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-white border border-gray-100">
                                    <div
                                        class="w-12 h-12 bg-primary rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                        <?php echo $publisher['num']; ?>
                                    </div>
                                    <div class="flex-1">
                                        <h3 class="font-semibold text-dark">
                                            <?php echo htmlspecialchars($publisher['name']); ?>
                                        </h3>
                                    </div>
                                    <div class="text-left">
                                        <span class="bg-yellow text-dark px-3 py-1 rounded-full text-sm font-bold">
                                            <?php echo $publisher['booth']; ?> م²
                                        </span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <a href="volunteer-dashboard.php"
            class="block w-full text-center bg-white text-primary font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 mb-20">
            <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            العودة للرئيسية
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

            <a href="halls.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span class="text-xs font-semibold">القاعات</span>
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
</body>
<script>
    document.getElementById('hallSearchInput').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase().trim();
        const publishers = document.querySelectorAll('.publisher-card');
        const groups = document.querySelectorAll('.space-y-6 > div'); // The section containers

        let totalResults = 0;

        publishers.forEach(card => {
            const name = card.querySelector('h3').textContent.toLowerCase();
            const num = card.querySelector('.text-white').textContent.trim();

            if (name.includes(searchTerm) || num.includes(searchTerm)) {
                card.classList.remove('hidden');
                totalResults++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Hide empty groups
        groups.forEach(group => {
            const visibleCards = group.querySelectorAll('.publisher-card:not(.hidden)');
            if (visibleCards.length === 0) {
                group.classList.add('hidden');
            } else {
                group.classList.remove('hidden');
            }
        });

        // Update count or show no results
        // Note: We might want to add a 'no results' div similar to guide.php
    });
</script>

</html>