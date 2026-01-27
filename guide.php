<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit;
}
require_once 'notes_loader.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دليل الفعاليات - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: 80px;
        }

        .bg-primary {
            background-color: #2570d8;
        }

        pre {
            white-space: pre-wrap;
            font-family: 'Cairo', sans-serif;
            direction: rtl;
            unicode-bidi: embed;
        }
    </style>
</head>

<body class="bg-gray-50 relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>
    <div class="bg-primary text-white p-6 rounded-b-3xl shadow-lg mb-0">
        <h1 class="text-2xl font-bold text-center">دليل الفعاليات والملاحظات</h1>
    </div>

    <!-- Notes Ticker -->
    <?php include 'notes_ticker.php'; ?>

    <div class="max-w-2xl mx-auto px-4 mt-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4 border-b pb-2">تفاصيل الفعاليات</h2>

        <!-- Search Input -->
        <div class="relative mb-6">
            <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </span>
            <input type="text" id="searchInput" placeholder="ابحث عن فعالية أو قاعة..."
                class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition shadow-sm">
        </div>

        <?php
        $json_file_path = 'json_files/guide_data.json';
        $json_data = file_get_contents($json_file_path);

        // Remove BOM if present
        if (strpos($json_data, "\xEF\xBB\xBF") === 0) {
            $json_data = substr($json_data, 3);
        }

        $data = json_decode($json_data, true) ?? [];

        if (json_last_error() !== JSON_ERROR_NONE) {
            echo '<div class="text-red-500 text-center py-4">Error loading data: ' . json_last_error_msg() . '</div>';
        }

        $events = $data['events'] ?? [];
        $advices = $data['advices'] ?? [];
        ?>

        <!-- Tabs -->
        <div class="flex border-b border-gray-200 mb-6">
            <button id="tab-events"
                class="tab-bt flex-1 rounded-t-lg py-4 text-center text-blue-600 border-b-2 border-blue-600 font-bold focus:outline-none"
                onclick="switchTab('events')" data-tab="events" data-active="true">
                الفعاليات
            </button>
            <button id="tab-advices"
                class="tab-btn flex-1 bg-gray-100 rounded-t-lg py-4 text-center text-gray-500 font-medium hover:text-yellow-600 focus:outline-none"
                onclick="switchTab('advices')" data-tab="advices" data-active="false">
                نصائح هامة
            </button>
        </div>

        <!-- Events Container -->
        <div id="eventsContainer">
            <?php if (empty($events)): ?>
                <div class="text-center text-gray-500 py-10">لا توجد فعاليات متاحة حالياً.</div>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                    <?php
                    // Skip items that are just page numbers
                    if (empty($event['details']) && empty($event['time']) && is_numeric(trim($event['title']))) {
                        continue;
                    }
                    ?>
                    <div
                        class="event-card bg-white rounded-2xl shadow-md p-5 mb-4 border-r-4 border-blue-500 hover:shadow-lg transition duration-200">
                        <div class="flex justify-between items-start">
                            <h3 class="text-lg font-bold text-gray-900 mb-2 leading-tight">
                                <?php echo htmlspecialchars($event['title']); ?>
                            </h3>
                            <?php if (!empty($event['page'])): ?>
                                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">ص
                                    <?php echo htmlspecialchars($event['page']); ?></span>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($event['location'])): ?>
                            <div class="inline-block bg-blue-50 text-blue-700 text-xs px-3 py-1 rounded-full mb-3 font-medium">
                                <?php echo htmlspecialchars($event['location']); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($event['details'])): ?>
                            <ul class="text-gray-600 text-sm space-y-1 mb-3 pr-2">
                                <?php foreach ($event['details'] as $detail): ?>
                                    <li class="flex items-start">
                                        <span class="text-blue-500 ml-2">•</span>
                                        <span><?php echo htmlspecialchars($detail); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>

                        <?php if (!empty($event['time'])): ?>
                            <div class="flex items-center text-blue-600 text-sm font-bold border-t border-gray-100 pt-3 mt-2">
                                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span dir="ltr"><?php echo htmlspecialchars($event['time']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Advices Container -->
        <div id="advicesContainer" class="hidden">
            <?php if (!empty($advices)): ?>
                <?php foreach ($advices as $advice): ?>
                    <div class="bg-white rounded-2xl shadow-md p-6 mb-4 border-r-4 border-yellow-400">
                        <h3 class="text-xl font-bold text-gray-900 mb-4">
                            <?php echo ($advice['title'] === 'whatsapp_advices') ? 'نصائح هامة' : htmlspecialchars($advice['title']); ?>
                        </h3>
                        <?php if (!empty($advice['details']) && is_array($advice['details'])): ?>
                            <ul class="space-y-3">
                                <?php foreach ($advice['details'] as $detail): ?>
                                    <?php
                                    // Check if this is a hall/قاعة or plaza/بلازا reference
                                    $isHall = preg_match('/^-?قاعة\s*[١٢٣٤٥٦1-6]/u', trim($detail)) ||
                                        preg_match('/^-قاعة\s/u', trim($detail)) ||
                                        preg_match('/^-?بلازا\s*[١٢1-2]/u', trim($detail));
                                    ?>
                                    <?php if ($isHall): ?>
                                        <li class="flex items-start bg-blue-100 p-3 rounded-lg border-r-4 border-blue-500">
                                            <span class="text-blue-600 ml-3 text-xl">📍</span>
                                            <span class="text-blue-800 font-bold"><?php echo htmlspecialchars($detail); ?></span>
                                        </li>
                                    <?php else: ?>
                                        <li class="flex items-start bg-yellow-50 p-3 rounded-lg">
                                            <span class="text-yellow-600 ml-3 text-xl">💡</span>
                                            <span class="text-gray-700 font-medium"><?php echo htmlspecialchars($detail); ?></span>
                                        </li>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-500 py-10">لا توجد نصائح حالياً</div>
            <?php endif; ?>
        </div>

        <!-- No Results Message -->
        <div id="noResults" class="hidden text-center py-10 text-gray-500">
            <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9.172 9.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <p class="text-lg">لم يتم العثور على نتائج للبحث</p>
        </div>

        <!-- Back Link -->
        <a href="volunteer-dashboard.php"
            class="block w-full text-center bg-white text-primary font-bold py-4 rounded-xl shadow-lg mb-20">
            العودة
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
            <a href="guide.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z" />
                </svg>
                <span class="text-xs font-semibold">الدليل</span>
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

    <script>
        // Smart Arabic Normalization features
        function normalizeArabic(text) {
            if (!text) return '';
            text = text.toString();
            text = text.replace(/(آ|إ|أ)/g, 'ا');
            text = text.replace(/(ة)/g, 'ه');
            text = text.replace(/(ئ|ؤ)/g, 'ء');
            text = text.replace(/(ى)/g, 'ي');
            // Remove tashkeel
            text = text.replace(/[\u064B-\u065F\u0670]/g, '');
            return text;
        }

        document.getElementById('searchInput').addEventListener('input', function (e) {
            const rawTerm = e.target.value.trim();
            const searchTerm = normalizeArabic(rawTerm.toLowerCase());

            const eventsBtn = document.getElementById('tab-events');
            const activeTab = eventsBtn.dataset.active === "true" ? 'events' : 'advices';

            const containerId = activeTab === 'events' ? '#eventsContainer' : '#advicesContainer';
            const cards = document.querySelectorAll(containerId + ' > div');

            let hasResults = false;

            cards.forEach(card => {
                // Clear previous highlighting
                removeHighlight(card);

                if (searchTerm === '') {
                    card.classList.remove('hidden');
                    hasResults = true; // Show all if empty
                    return;
                }

                const originalText = card.textContent;
                const normalizedText = normalizeArabic(originalText.toLowerCase());

                if (normalizedText.includes(searchTerm)) {
                    card.classList.remove('hidden');
                    hasResults = true;
                    // Highlight logic
                    highlightText(card, rawTerm);
                } else {
                    card.classList.add('hidden');
                }
            });

            const noResults = document.getElementById('noResults');
            if (hasResults || searchTerm === '') {
                noResults.classList.add('hidden');
            } else {
                noResults.classList.remove('hidden');
            }
        });

        // Highlight helper functions
        function highlightText(element, term) {
            if (!term) return;
            // This is a simple implementation. For complex HTML structure, a tree walker is better.
            // Here we assume simple text blocks mostly. 
            // NOTE: Modifying innerHTML can break event listeners if any.
            // For safety in this app, we will skip complex highlighting to avoid breaking layout,
            // or just highlight specific known text containers if needed.
            // For now, simpler filtering is safer than aggressive DOM manipulation.
        }

        function removeHighlight(element) {
            // Placeholder for cleanup if we added highlighting spans
        }

        function switchTab(tabName) {
            const eventsBtn = document.getElementById('tab-events');
            const advicesBtn = document.getElementById('tab-advices');
            const eventsContainer = document.getElementById('eventsContainer');
            const advicesContainer = document.getElementById('advicesContainer');
            const searchInput = document.getElementById('searchInput');

            if (tabName === 'events') {
                // Events tab active
                eventsBtn.className = 'tab-btn flex-1 py-4 text-center text-blue-600 border-b-2 border-blue-600 font-bold focus:outline-none';
                advicesBtn.className = 'tab-btn bg-gray-100 rounded-t-lg  flex-1 py-4 text-center text-gray-500 font-medium hover:text-yellow-600 focus:outline-none';

                eventsBtn.dataset.active = "true";
                advicesBtn.dataset.active = "false";

                eventsContainer.classList.remove('hidden');
                advicesContainer.classList.add('hidden');
                searchInput.placeholder = "ابحث عن فعالية أو قاعة...";
            } else {
                // Advices tab active
                advicesBtn.className = 'tab-btn flex-1 py-4 text-center text-yellow-600 border-b-2 border-yellow-600 font-bold focus:outline-none';
                eventsBtn.className = 'tab-btn bg-gray-100 rounded-t-lg  flex-1 py-4 text-center text-gray-500 font-medium hover:text-blue-600 focus:outline-none';

                advicesBtn.dataset.active = "true";
                eventsBtn.dataset.active = "false";

                advicesContainer.classList.remove('hidden');
                eventsContainer.classList.add('hidden');
                searchInput.placeholder = "ابحث في النصائح...";
            }

            // Reset search
            searchInput.value = '';
            document.querySelectorAll('#eventsContainer > div, #advicesContainer > div').forEach(el => {
                el.classList.remove('hidden');
                // Remove highlighting if implemented
            });
            document.getElementById('noResults').classList.add('hidden');
        }
    </script>
</body>

</html>