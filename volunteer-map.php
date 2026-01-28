<?php
require_once 'includes/auth-guard.php';
requireAuth('volunteer');

$volunteer_name = $_SESSION['user_name'] ?? 'متطوع';
$volunteer_code = $_SESSION['user_code'] ?? 'N/A';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="300">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>موقعي على الخريطة - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: calc(80px + env(safe-area-inset-bottom));
            min-height: 100vh;
        }

        /* Mobile First Improvements */
        button,
        a,
        input,
        select {
            min-height: 44px;
            touch-action: manipulation;
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

        .text-primary {
            color: #2570d8;
        }

        #map-container {
            height: 50vh;
            min-height: 350px;
            border-radius: 1rem;
            overflow: hidden;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: white;
            border-radius: 1.5rem;
            padding: 1.5rem;
            width: 90%;
            max-width: 400px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(50px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .pulse-dot {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                transform: scale(1);
                opacity: 1;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }

        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 10px;
            pointer-events: none;
        }

        .toast {
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: toast-in 0.4s ease forwards;
            pointer-events: auto;
        }

        .toast.success {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .toast.error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }

        .toast.warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
        }

        .toast.hiding {
            animation: toast-out 0.3s ease forwards;
        }

        @keyframes toast-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes toast-out {
            from {
                opacity: 1;
                transform: translateY(0);
            }

            to {
                opacity: 0;
                transform: translateY(-20px);
            }
        }
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="assets/images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-primary text-white p-4">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">موقعي على الخريطة</h1>
                <p class="text-sm opacity-90">
                    <?php echo htmlspecialchars($volunteer_name); ?>
                </p>
            </div>
            <a href="volunteer-dashboard.php" class="bg-white/20 px-4 py-2 rounded-xl hover:bg-white/30 transition">
                العودة
            </a>
        </div>
    </div>

    <div class="max-w-md mx-auto px-4 py-4">
        <!-- Status Card -->
        <div id="status-card" class="bg-white rounded-2xl shadow-lg p-4 mb-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-dark">حالة التواجد</h3>
                    <p id="presence-text" class="text-sm text-gray-600">جاري التحميل...</p>
                </div>
                <div id="presence-indicator" class="w-4 h-4 rounded-full bg-gray-300"></div>
            </div>
        </div>

        <!-- Location Display Card (dynamically styled based on location type) -->
        <div id="hall-info" class="rounded-2xl p-4 mb-4 hidden transition-all duration-300">
            <div id="location-card" class="flex items-center gap-4">
                <div id="location-icon" class="w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg">
                    <!-- Icon will be inserted dynamically -->
                </div>
                <div class="flex-1">
                    <p id="location-label" class="text-sm opacity-80">موقعك الحالي</p>
                    <p id="hall-number" class="font-bold text-xl">---</p>
                    <p id="location-desc" class="text-xs opacity-70 mt-1"></p>
                </div>
            </div>
        </div>

        <!-- Map Container -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-4">
            <div id="map-container"></div>
            <div id="no-location-message" class="text-center py-12 hidden">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <p class="text-gray-500 font-semibold">لم يتم تعيين موقع لك بعد</p>
                <p class="text-sm text-gray-400">سيظهر موقعك هنا عند تعيينك من قبل المسؤول</p>
            </div>
        </div>

        <!-- Remove Location Button (only shown when assigned) -->
        <button id="remove-btn" onclick="openRemoveModal()"
            class="w-full bg-red-100 text-red-700 font-bold py-4 rounded-xl shadow-lg hover:bg-red-200 transition hidden">
            <span class="flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                إزالة موقعي
            </span>
        </button>
    </div>

    <!-- Remove Location Modal -->
    <div id="remove-modal" class="modal">
        <div class="modal-content" dir="rtl">
            <h3 class="text-xl font-bold text-dark mb-4 text-center">إزالة الموقع</h3>
            <p class="text-gray-600 text-center mb-4">هل أنت متأكد من إزالة موقعك؟ يرجى تحديد السبب:</p>

            <div class="space-y-3 mb-6">
                <label
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="radio" name="reason" value="استراحة" class="w-5 h-5 text-primary">
                    <span>استراحة</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="radio" name="reason" value="انتهاء الوردية" class="w-5 h-5 text-primary">
                    <span>انتهاء الوردية</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="radio" name="reason" value="حالة طارئة" class="w-5 h-5 text-primary">
                    <span>حالة طارئة</span>
                </label>
                <label
                    class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer hover:bg-gray-100 transition">
                    <input type="radio" name="reason" value="other" class="w-5 h-5 text-primary" id="other-radio">
                    <span>سبب آخر</span>
                </label>
                <input type="text" id="other-reason" placeholder="اكتب السبب هنا..."
                    class="w-full p-3 border rounded-xl focus:outline-none focus:border-primary hidden">
            </div>

            <div class="flex gap-3">
                <button onclick="closeRemoveModal()"
                    class="flex-1 bg-gray-100 text-gray-700 font-bold py-3 rounded-xl hover:bg-gray-200 transition">
                    إلغاء
                </button>
                <button onclick="confirmRemove()" id="confirm-remove-btn"
                    class="flex-1 bg-red-500 text-white font-bold py-3 rounded-xl hover:bg-red-600 transition">
                    تأكيد الإزالة
                </button>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div id="success-modal" class="modal">
        <div class="modal-content text-center" dir="rtl">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-xl font-bold text-dark mb-2">تمت الإزالة بنجاح</h3>
            <p class="text-gray-600 mb-6">تم إزالة موقعك وتحديث حالتك.</p>

            <button onclick="closeSuccessModal()"
                class="w-full bg-primary text-white font-bold py-3 rounded-xl hover:bg-blue-700 transition">
                حسناً
            </button>
        </div>
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

            <a href="volunteer-map.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z" />
                </svg>
                <span class="text-xs font-semibold">موقعي</span>
            </a>

            <a href="halls.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
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

            <a href="links.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-xs">روابط</span>
            </a>
        </div>
    </div>

    <script type="module">
        import { getVolunteerByCode, subscribeToVolunteers } from './assets/js/volunteers-service.js?v=<?php echo time(); ?>';
        import { getSession } from './assets/js/auth-service.js?v=<?php echo time(); ?>';
        import { supabase } from './assets/js/supabase-client.js?v=<?php echo time(); ?>';

        const volunteerCode = '<?php echo addslashes($volunteer_code); ?>';
        let map = null;
        let marker = null;
        let volunteerData = null;

        // Hall map configurations
        const HALL_MAPS = {
            1: { image: 'assets/images/CIPF_Map/CIBF-map-1.jpg', width: 1200, height: 1600 },
            2: { image: 'assets/images/CIPF_Map/CIBF-map-2.png', width: 1200, height: 1600 },
            3: { image: 'assets/images/CIPF_Map/CIBF-map-3.png', width: 1200, height: 1600 },
            4: { image: 'assets/images/CIPF_Map/CIBF-map-4.png', width: 1200, height: 1600 },
            5: { image: 'assets/images/CIPF_Map/CIBF-map-5.png', width: 1200, height: 1600 }
        };

        async function loadVolunteerLocation() {
            try {
                console.log('Loading volunteer data for:', volunteerCode);
                volunteerData = await getVolunteerByCode(volunteerCode);
                console.log('Volunteer data:', volunteerData);
                updateUI(volunteerData);
            } catch (error) {
                console.error('Error loading volunteer location:', error);
                // Show "not assigned" state on error
                updateUI(null);
            }
        }

        function updateUI(data) {
            // Format hall name helper
            function formatHallName(hallId) {
                if (hallId == 101) return 'البوابة';
                if (hallId == 102) return 'غرفة المعلومات';
                if (hallId >= 1 && hallId <= 5) return `قاعة ${hallId}`;
                return 'غير محدد';
            }

            // Toast notification function
            function showToast(message, type = 'warning') {
                const container = document.getElementById('toast-container');
                const toast = document.createElement('div');
                toast.className = `toast ${type}`;
                const icons = { success: '✓', error: '✕', warning: '⚠' };
                toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
                container.appendChild(toast);
                setTimeout(() => { toast.classList.add('hiding'); setTimeout(() => toast.remove(), 300); }, 3000);
            }
            window.showToast = showToast;

            const presenceText = document.getElementById('presence-text');
            const presenceIndicator = document.getElementById('presence-indicator');
            const hallInfo = document.getElementById('hall-info');
            const hallNumber = document.getElementById('hall-number');
            const removeBtn = document.getElementById('remove-btn');
            const mapContainer = document.getElementById('map-container');
            const noLocationMessage = document.getElementById('no-location-message');

            // Check if data exists and has valid assignment
            const isAssigned = data && data.is_present === true && data.hall_id && data.current_loc;

            if (isAssigned) {
                // Volunteer is assigned
                presenceText.textContent = 'أنت متواجد حالياً في موقعك';
                presenceIndicator.className = 'w-4 h-4 rounded-full bg-green-500 pulse-dot';

                hallInfo.classList.remove('hidden');

                // Dynamic styling based on location type
                const locationIcon = document.getElementById('location-icon');
                const locationLabel = document.getElementById('location-label');
                const locationDesc = document.getElementById('location-desc');

                // Determine effective ID based on location
                let effectiveHallId = data.hall_id;
                if (data.current_loc == '101') effectiveHallId = 101;
                else if (data.current_loc == '102') effectiveHallId = 102;

                if (effectiveHallId == 101) {
                    // Gate - Special styling
                    hallInfo.className = 'rounded-2xl p-4 mb-4 transition-all duration-300 bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg';
                    locationIcon.className = 'w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg bg-white/20';
                    locationIcon.innerHTML = `<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M19 19V5c0-1.1-.9-2-2-2H7c-1.1 0-2 .9-2 2v14H3v2h18v-2h-2zm-6 0h-2v-2h2v2zm0-4h-2V9h2v6z"/></svg>`;
                    hallNumber.textContent = 'البوابة الرئيسية';
                    hallNumber.className = 'font-bold text-xl text-white';
                    locationLabel.textContent = 'موقعك الحالي';
                    locationDesc.textContent = 'أنت في نقطة استقبال الزوار';
                } else if (effectiveHallId == 102) {
                    // Info Room - Special styling
                    hallInfo.className = 'rounded-2xl p-4 mb-4 transition-all duration-300 bg-gradient-to-r from-violet-500 to-purple-600 text-white shadow-lg';
                    locationIcon.className = 'w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg bg-white/20';
                    locationIcon.innerHTML = `<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>`;
                    hallNumber.textContent = 'غرفة المعلومات';
                    hallNumber.className = 'font-bold text-xl text-white';
                    locationLabel.textContent = 'موقعك الحالي';
                    locationDesc.textContent = 'أنت في مركز الدعم والمساعدة';
                } else {
                    // Regular hall - Default styling
                    hallInfo.className = 'rounded-2xl p-4 mb-4 transition-all duration-300 bg-gradient-to-r from-blue-500 to-primary text-white shadow-lg';
                    locationIcon.className = 'w-14 h-14 rounded-2xl flex items-center justify-center shadow-lg bg-white/20';
                    locationIcon.innerHTML = `<svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>`;
                    hallNumber.textContent = formatHallName(data.hall_id);
                    hallNumber.className = 'font-bold text-xl text-white';
                    locationLabel.textContent = 'القاعة الحالية';
                    locationDesc.textContent = `موقعك: ${data.current_loc}`;
                }

                removeBtn.classList.remove('hidden');
                mapContainer.classList.remove('hidden');
                noLocationMessage.classList.add('hidden');

                // Initialize or update map
                initMap(data.hall_id, data.current_loc);
            } else {
                // Volunteer not assigned or data loading failed
                presenceText.textContent = 'لم يتم تعيين موقع لك بعد';
                presenceIndicator.className = 'w-4 h-4 rounded-full bg-yellow-400';

                hallInfo.classList.add('hidden');
                removeBtn.classList.add('hidden');
                mapContainer.classList.add('hidden');
                noLocationMessage.classList.remove('hidden');

                if (map) {
                    map.remove();
                    map = null;
                }
            }
        }

        function initMap(hallId, locationString) {
            const config = HALL_MAPS[hallId];
            if (!config) return;

            // Parse coordinates
            const coords = parseLocationCoords(locationString);
            if (!coords) return;

            const bounds = [[0, 0], [config.height, config.width]];

            if (map) {
                map.remove();
            }

            map = L.map('map-container', {
                crs: L.CRS.Simple,
                minZoom: -2,
                maxZoom: 2,
                zoomControl: true,
                attributionControl: false
            });

            L.imageOverlay(config.image, bounds).addTo(map);

            // Add marker for volunteer's location
            marker = L.circleMarker([coords.y, coords.x], {
                radius: 15,
                fillColor: '#22c55e',
                color: '#ffffff',
                weight: 4,
                opacity: 1,
                fillOpacity: 0.9
            }).addTo(map);

            // Add popup
            marker.bindPopup(`
                <div class="text-center p-2" dir="rtl">
                    <p class="font-bold">📍 موقعك هنا</p>
                    <p class="text-sm text-gray-600">قاعة ${hallId}</p>
                </div>
            `).openPopup();

            // Center on marker
            map.setView([coords.y, coords.x], 0);
        }

        function parseLocationCoords(locString) {
            if (!locString) return null;
            const match = locString.match(/x:(\d+),y:(\d+)/);
            if (match) {
                return { x: parseInt(match[1]), y: parseInt(match[2]) };
            }
            return null;
        }

        // Modal functions
        window.openRemoveModal = function () {
            document.getElementById('remove-modal').classList.add('active');
        };

        window.closeRemoveModal = function () {
            document.getElementById('remove-modal').classList.remove('active');
            // Reset form
            document.querySelectorAll('input[name="reason"]').forEach(r => r.checked = false);
            document.getElementById('other-reason').value = '';
            document.getElementById('other-reason').classList.add('hidden');
        };

        // Show/hide other reason input
        document.querySelectorAll('input[name="reason"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const otherInput = document.getElementById('other-reason');
                if (this.value === 'other') {
                    otherInput.classList.remove('hidden');
                } else {
                    otherInput.classList.add('hidden');
                }
            });
        });

        window.confirmRemove = async function () {
            const selectedReason = document.querySelector('input[name="reason"]:checked');
            if (!selectedReason) {
                showToast('يرجى اختيار سبب الإزالة', 'warning');
                return;
            }

            let reason = selectedReason.value;
            if (reason === 'other') {
                reason = document.getElementById('other-reason').value.trim();
                if (!reason) {
                    showToast('يرجى كتابة السبب', 'warning');
                    return;
                }
            }

            const btn = document.getElementById('confirm-remove-btn');
            btn.textContent = 'جاري الإزالة...';
            btn.disabled = true;

            try {
                const now = new Date();
                const formattedDate = now.toLocaleString('en-US', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit',
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: true
                });

                // Try updating with reason first
                let { error } = await supabase
                    .from('volunteers')
                    .update({
                        is_present: false,
                        is_occupied: false,
                        current_loc: null,
                        reason: reason,
                        reasons_date: formattedDate
                    })
                    .eq('volunteerCode', volunteerCode);

                // If error (likely missing columns), try basic update
                if (error) {
                    console.warn('Full update failed, trying basic removal...', error);
                    const { error: fallbackError } = await supabase
                        .from('volunteers')
                        .update({
                            is_present: false,
                            is_occupied: false,
                            current_loc: null
                        })
                        .eq('volunteerCode', volunteerCode);

                    if (fallbackError) throw fallbackError;
                }

                closeRemoveModal();
                // Show Success Modal
                document.getElementById('success-modal').classList.add('active');
                await loadVolunteerLocation();
            } catch (error) {
                console.error('Error removing location:', error);
                showToast('حدث خطأ في إزالة الموقع', 'error');
            } finally {
                btn.textContent = 'تأكيد الإزالة';
                btn.disabled = false;
            }
        };

        window.closeSuccessModal = function () {
            document.getElementById('success-modal').classList.remove('active');
        };

        // Initialize
        loadVolunteerLocation();

        // Subscribe to real-time updates
        subscribeToVolunteers((payload) => {
            if (payload.new?.volunteerCode === volunteerCode) {
                updateUI(payload.new);
            }
        });
    </script>
</body>

</html>