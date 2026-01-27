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
            padding-bottom: 120px;
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
            <input type="text" id="searchInput" placeholder="ابحث في النصائح..."
                class="w-full pr-10 pl-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-primary focus:border-transparent outline-none transition shadow-sm">
        </div>

        <?php
        // Load halls advices from structured JSON
        $halls_advices_path = 'json_files/halls_advices.json';
        $halls_advices_data = file_get_contents($halls_advices_path);
        if (strpos($halls_advices_data, "\xEF\xBB\xBF") === 0) {
            $halls_advices_data = substr($halls_advices_data, 3);
        }
        $halls_advices = json_decode($halls_advices_data, true) ?? [];
        $halls = $halls_advices['halls'] ?? [];

        // Load event images from guide_data.json
        $event_images = $halls_advices['event_images'] ?? [];
        ?>

        <!-- Tabs (2 tabs only: نصائح هامة and البرنامج) -->
        <div class="flex border-b border-gray-200 mb-6">
            <button id="tab-advices"
                class="tab-btn flex-1 rounded-t-lg py-4 text-center text-yellow-600 border-b-2 border-yellow-600 font-bold focus:outline-none"
                onclick="switchTab('advices')" data-tab="advices" data-active="true">
                نصائح هامة
            </button>
            <button id="tab-images"
                class="tab-btn flex-1 bg-gray-100 rounded-t-lg py-4 text-center text-gray-500 font-medium hover:text-green-600 focus:outline-none"
                onclick="switchTab('images')" data-tab="images" data-active="false">
                📅 البرنامج
            </button>
        </div>


        <!-- Advices Container (visible by default) -->
        <div id="advicesContainer" class="">
            <?php if (!empty($halls)): ?>
                <?php foreach ($halls as $hallKey => $hall): ?>
                    <div class="advice-hall-card bg-white rounded-2xl shadow-md p-6 mb-4 border-r-4 border-yellow-400"
                        data-hall-key="<?php echo htmlspecialchars($hallKey); ?>"
                        data-hall-name="<?php echo htmlspecialchars($hall['name']); ?>">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                            <span class="text-blue-500 text-2xl">📍</span>
                            <?php echo htmlspecialchars($hall['name']); ?>
                        </h3>
                        <?php if (!empty($hall['tips'])): ?>
                            <ul class="space-y-2">
                                <?php foreach ($hall['tips'] as $tip): ?>
                                    <li class="tip-item flex items-start bg-yellow-50 p-3 rounded-lg hover:bg-yellow-100 transition"
                                        data-tip-text="<?php echo htmlspecialchars(is_array($tip) ? $tip['text'] : $tip); ?>">
                                        <span class="text-yellow-600 ml-3 text-lg">💡</span>
                                        <div class="flex-1">
                                            <span class="text-gray-700 font-medium">
                                                <?php echo htmlspecialchars(is_array($tip) ? $tip['text'] : $tip); ?>
                                            </span>
                                            <?php if (is_array($tip) && !empty($tip['code'])): ?>
                                                <span
                                                    class="inline-block bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded mr-2 font-mono">
                                                    <?php echo htmlspecialchars($tip['code']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center text-gray-500 py-10">لا توجد نصائح حالياً</div>
            <?php endif; ?>
        </div>

        <!-- Event Images Gallery Container -->
        <div id="imagesContainer" class="hidden">
            <?php if (!empty($event_images)): ?>
                <div class="mb-4 text-center">
                    <p class="text-gray-600 text-sm">اضغط على الصورة للتكبير • اسحب للتنقل</p>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <?php foreach ($event_images as $img): ?>
                        <div class="event-image-card bg-white rounded-xl shadow-md overflow-hidden cursor-pointer transform hover:scale-105 transition duration-200"
                            onclick="openImageModal('<?php echo htmlspecialchars($img['image']); ?>', '<?php echo htmlspecialchars($img['title']); ?>')">
                            <img src="<?php echo htmlspecialchars($img['image']); ?>"
                                alt="<?php echo htmlspecialchars($img['title']); ?>" class="w-full h-32 object-cover"
                                loading="lazy">
                            <div class="p-2">
                                <p class="text-xs text-gray-700 font-medium text-center truncate">
                                    <?php echo htmlspecialchars($img['title']); ?>
                                </p>
                                <span class="block text-center text-xs text-gray-400">ص
                                    <?php echo htmlspecialchars($img['page']); ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center text-gray-500 py-10">لا توجد صور متاحة</div>
            <?php endif; ?>
        </div>

        <!-- Image Modal -->
        <div id="imageModal"
            class="fixed inset-0 z-[100] bg-black bg-opacity-90 hidden items-center justify-center p-4">
            <button onclick="closeImageModal()"
                class="absolute top-4 left-4 text-white text-3xl z-10 bg-black bg-opacity-50 rounded-full w-12 h-12 flex items-center justify-center">
                ×
            </button>
            <div class="absolute top-4 right-4 text-white text-lg bg-black bg-opacity-50 px-4 py-2 rounded-lg"
                id="imageModalTitle"></div>
            <img id="imageModalImage" src="" alt=""
                class="max-w-full max-h-[85vh] object-contain rounded-lg shadow-2xl">
            <div class="absolute bottom-4 left-1/2 transform -translate-x-1/2 flex gap-4">
                <button onclick="navigateImage(-1)"
                    class="bg-white text-gray-800 px-6 py-3 rounded-xl shadow-lg hover:bg-gray-100 transition">
                    ← السابق
                </button>
                <button onclick="navigateImage(1)"
                    class="bg-white text-gray-800 px-6 py-3 rounded-xl shadow-lg hover:bg-gray-100 transition">
                    التالي →
                </button>
            </div>
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

            // Advices search - enhanced with hall key matching
            const hallCards = document.querySelectorAll('#advicesContainer > .advice-hall-card');
            let hasResults = false;

            // Convert Arabic numerals to western
            const hallNumberMap = { '١': '1', '٢': '2', '٣': '3', '٤': '4', '٥': '5', '٦': '6' };
            let normalizedSearchTerm = searchTerm;
            for (const [ar, en] of Object.entries(hallNumberMap)) {
                normalizedSearchTerm = normalizedSearchTerm.replace(new RegExp(ar, 'g'), en);
            }

            hallCards.forEach(hallCard => {
                const hallKey = hallCard.dataset.hallKey || '';
                const hallName = normalizeArabic(hallCard.dataset.hallName || '').toLowerCase();
                const tips = hallCard.querySelectorAll('.tip-item');

                if (searchTerm === '') {
                    hallCard.classList.remove('hidden');
                    tips.forEach(tip => tip.classList.remove('hidden', 'bg-green-100'));
                    hasResults = true;
                    return;
                }

                // Check if search matches hall key or name
                const matchesHall = hallKey.includes(normalizedSearchTerm) ||
                    hallName.includes(normalizedSearchTerm) ||
                    normalizedSearchTerm.includes(hallKey);

                // Check individual tips
                let matchingTips = 0;
                tips.forEach(tip => {
                    const tipText = normalizeArabic(tip.dataset.tipText || '').toLowerCase();
                    const fullText = normalizeArabic(tip.textContent).toLowerCase();

                    if (matchesHall || tipText.includes(normalizedSearchTerm) || fullText.includes(normalizedSearchTerm)) {
                        tip.classList.remove('hidden');
                        tip.classList.add('bg-green-100'); // Highlight matching tips
                        matchingTips++;
                    } else {
                        tip.classList.add('hidden');
                        tip.classList.remove('bg-green-100');
                    }
                });

                if (matchesHall || matchingTips > 0) {
                    hallCard.classList.remove('hidden');
                    hasResults = true;
                    // If hall matches, show all tips in that hall
                    if (matchesHall) {
                        tips.forEach(tip => {
                            tip.classList.remove('hidden');
                            tip.classList.add('bg-green-100');
                        });
                    }
                } else {
                    hallCard.classList.add('hidden');
                }
            });

            const noResults = document.getElementById('noResults');
            noResults.classList.toggle('hidden', hasResults || searchTerm === '');
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
            const advicesBtn = document.getElementById('tab-advices');
            const imagesBtn = document.getElementById('tab-images');
            const advicesContainer = document.getElementById('advicesContainer');
            const imagesContainer = document.getElementById('imagesContainer');
            const searchInput = document.getElementById('searchInput');

            // Reset all tabs to inactive
            const inactiveClass = 'tab-btn flex-1 bg-gray-100 rounded-t-lg py-4 text-center text-gray-500 font-medium focus:outline-none';
            advicesBtn.className = inactiveClass + ' hover:text-yellow-600';
            imagesBtn.className = inactiveClass + ' hover:text-green-600';

            advicesBtn.dataset.active = "false";
            imagesBtn.dataset.active = "false";

            // Hide all containers
            advicesContainer.classList.add('hidden');
            imagesContainer.classList.add('hidden');

            if (tabName === 'advices') {
                advicesBtn.className = 'tab-btn flex-1 rounded-t-lg py-4 text-center text-yellow-600 border-b-2 border-yellow-600 font-bold focus:outline-none';
                advicesBtn.dataset.active = "true";
                advicesContainer.classList.remove('hidden');
                searchInput.placeholder = "ابحث في النصائح...";
                searchInput.parentElement.classList.remove('hidden');
            } else if (tabName === 'images') {
                imagesBtn.className = 'tab-btn flex-1 rounded-t-lg py-4 text-center text-green-600 border-b-2 border-green-600 font-bold focus:outline-none';
                imagesBtn.dataset.active = "true";
                imagesContainer.classList.remove('hidden');
                // Hide search for images tab
                searchInput.parentElement.classList.add('hidden');
            }

            // Reset search
            searchInput.value = '';
            document.querySelectorAll('#advicesContainer > div').forEach(el => {
                el.classList.remove('hidden');
            });
            // Reset tip highlighting
            document.querySelectorAll('.tip-item').forEach(tip => {
                tip.classList.remove('hidden', 'bg-green-100');
            });
            document.getElementById('noResults').classList.add('hidden');
        }

        // Image Modal Functions
        let currentImageIndex = 0;
        const eventImages = <?php echo json_encode($event_images); ?>;

        function openImageModal(imageSrc, title) {
            const modal = document.getElementById('imageModal');
            const img = document.getElementById('imageModalImage');
            const titleEl = document.getElementById('imageModalTitle');

            img.src = imageSrc;
            titleEl.textContent = title;
            modal.classList.remove('hidden');
            modal.classList.add('flex');

            // Find current index
            currentImageIndex = eventImages.findIndex(e => e.image === imageSrc);

            // Prevent body scroll
            document.body.style.overflow = 'hidden';
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }

        function navigateImage(direction) {
            if (!eventImages || eventImages.length === 0) return;

            currentImageIndex += direction;
            if (currentImageIndex < 0) currentImageIndex = eventImages.length - 1;
            if (currentImageIndex >= eventImages.length) currentImageIndex = 0;

            const img = document.getElementById('imageModalImage');
            const titleEl = document.getElementById('imageModalTitle');
            const currentImage = eventImages[currentImageIndex];

            img.src = currentImage.image;
            titleEl.textContent = currentImage.title;
        }

        // Close modal on escape key
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') closeImageModal();
            if (e.key === 'ArrowLeft') navigateImage(1);
            if (e.key === 'ArrowRight') navigateImage(-1);
        });

        // Close modal on background click
        document.getElementById('imageModal').addEventListener('click', function (e) {
            if (e.target === this) closeImageModal();
        });
    </script>
</body>

</html>