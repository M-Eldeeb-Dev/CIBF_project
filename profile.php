<?php
require_once 'includes/auth-guard.php';
requireAuth('volunteer');

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>الملف الشخصي - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="images/logo.jpg">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                touch-action: manipulation;
            }
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

    <div class="max-w-md mx-auto px-4 pb-24">
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
                    <span class="text-gray-600" id="profile-hall-label">القاعة الحالية</span>
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

        <!-- Callback Request Section (shown when volunteer has no location) -->
        <div id="callback-request-section" class="bg-white rounded-3xl shadow-xl p-6 mb-6 hidden">
            <h3 class="text-lg font-bold text-dark mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                طلب العودة
            </h3>

            <!-- Status Display -->
            <div id="callback-status-display" class="hidden mb-4">
                <div id="callback-pending" class="hidden bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2 text-yellow-700 font-bold">
                            <svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            طلبك قيد المراجعة
                        </div>
                        <button id="cancel-callback-btn" onclick="cancelCallbackRequest()"
                            class="text-red-500 hover:text-red-700 text-sm flex items-center gap-1 bg-red-50 px-3 py-1 rounded-lg hover:bg-red-100 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            إلغاء الطلب
                        </button>
                    </div>
                    <p class="text-gray-600 text-sm" id="callback-pending-comment"></p>
                    <p class="text-gray-400 text-xs mt-2" id="callback-pending-date"></p>
                </div>

                <div id="callback-approved" class="hidden bg-green-50 border border-green-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-green-700 font-bold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        تمت الموافقة على طلبك!
                    </div>
                    <p class="text-gray-600 text-sm mt-2">يرجى إنتظار الإدارة لتعيينك في موقع جديد.</p>
                </div>

                <div id="callback-rejected" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-red-700 font-bold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                        تم رفض طلبك
                    </div>
                    <p class="text-gray-600 text-sm mt-2">يمكنك تقديم طلب جديد.</p>
                </div>
            </div>

            <!-- Request Form -->
            <div id="callback-form" class="hidden">
                <p class="text-gray-600 text-sm mb-4">لم يتم تعيينك في موقع حالياً. يمكنك إرسال طلب للإدارة للعودة:</p>
                <textarea id="callback-comment-input"
                    class="w-full p-4 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none transition resize-none"
                    rows="3" placeholder="اكتب رسالتك للإدارة هنا..."></textarea>
                <button id="submit-callback-btn"
                    class="w-full mt-3 py-4 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg">
                    إرسال طلب العودة
                </button>
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
        import { getVolunteerByCode, subscribeToVolunteers, submitCallbackRequest, deleteCallbackRequest } from './js/volunteers-service.js';

        const volunteerCode = '<?php echo addslashes($volunteer_code); ?>';

        async function loadVolunteerData() {
            try {
                const data = await getVolunteerByCode(volunteerCode);
                if (data) {
                    updateUI(data);
                    updateCallbackSection(data);
                }
            } catch (error) {
                console.error('Error loading volunteer data:', error);
                document.getElementById('presence-status').textContent = 'متطوع نشط';
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

            // Update status text
            document.getElementById('presence-status').textContent = 'متطوع نشط';

            // Update presence badge
            const presenceBadge = document.getElementById('presence-badge');
            // Fix: Only consider occupied if there is a valid location
            const isOccupied = data.is_occupied === true && data.current_loc && data.current_loc !== '';
            const isPresent = data.is_present === true || isOccupied;

            if (isPresent) {
                presenceBadge.textContent = '✓ متواجد حالياً';
                presenceBadge.className = 'bg-green-100 text-green-700 px-4 py-1 rounded-full text-sm font-bold';
            } else {
                presenceBadge.textContent = 'غير متواجد';
                presenceBadge.className = 'bg-yellow-100 text-yellow-700 px-4 py-1 rounded-full text-sm font-bold';
            }

            // Determine effective ID based on location
            let effectiveHallId = data.hall_id;
            if (data.current_loc == '101') effectiveHallId = 101;
            else if (data.current_loc == '102') effectiveHallId = 102;

            // Update hall with formatted name
            document.getElementById('profile-hall').textContent = formatHallName(effectiveHallId);

            // Update label
            const isSpecial = effectiveHallId == 101 || effectiveHallId == 102;
            document.getElementById('profile-hall-label').textContent = isSpecial ? 'الموقع الحالي' : 'القاعة الحالية';

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

        function updateCallbackSection(data) {
            const section = document.getElementById('callback-request-section');
            const statusDisplay = document.getElementById('callback-status-display');
            const form = document.getElementById('callback-form');
            const pendingDiv = document.getElementById('callback-pending');
            const approvedDiv = document.getElementById('callback-approved');
            const rejectedDiv = document.getElementById('callback-rejected');

            // Check if volunteer has no current location
            const hasLocation = data.is_present || data.is_occupied || (data.current_loc && data.current_loc !== '');

            if (hasLocation) {
                // If volunteer has a location, hide the entire callback section
                section.classList.add('hidden');
                return;
            }

            // Show callback section
            section.classList.remove('hidden');

            // Hide all status divs first
            pendingDiv.classList.add('hidden');
            approvedDiv.classList.add('hidden');
            rejectedDiv.classList.add('hidden');

            // Check callback status
            const status = data.callback_comment_approval;

            if (status === 'pending') {
                statusDisplay.classList.remove('hidden');
                form.classList.add('hidden');
                pendingDiv.classList.remove('hidden');
                document.getElementById('callback-pending-comment').textContent = data.callback_comment || '';
                if (data.callback_comment_date) {
                    const date = new Date(data.callback_comment_date);
                    document.getElementById('callback-pending-date').textContent = `تاريخ الطلب: ${date.toLocaleDateString('ar-EG')} ${date.toLocaleTimeString('ar-EG')}`;
                }
            } else if (status === 'approved') {
                statusDisplay.classList.remove('hidden');
                form.classList.add('hidden');
                approvedDiv.classList.remove('hidden');
            } else if (status === 'rejected') {
                statusDisplay.classList.remove('hidden');
                form.classList.remove('hidden');
                rejectedDiv.classList.remove('hidden');
            } else {
                // No request yet - show form
                statusDisplay.classList.add('hidden');
                form.classList.remove('hidden');
            }
        }

        // Handle callback form submission
        document.getElementById('submit-callback-btn').addEventListener('click', async () => {
            const comment = document.getElementById('callback-comment-input').value.trim();
            if (!comment) {
                Swal.fire('تنبيه', 'يرجى كتابة رسالة للإدارة', 'warning');
                return;
            }

            const btn = document.getElementById('submit-callback-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الإرسال...';

            const success = await submitCallbackRequest(volunteerCode, comment);

            if (success) {
                btn.textContent = 'تم الإرسال ✓';
                btn.className = 'w-full mt-3 py-4 bg-green-600 text-white font-bold rounded-xl shadow-lg';
                // Reload data to update UI
                await loadVolunteerData();
                Swal.fire({
                    icon: 'success',
                    title: 'تم الإرسال',
                    text: 'تم إرسال طلبك للإدارة بنجاح',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                btn.disabled = false;
                btn.textContent = 'إرسال طلب العودة';
                Swal.fire('خطأ', 'حدث خطأ أثناء إرسال الطلب. يرجى المحاولة مرة أخرى.', 'error');
            }
        });

        // Handle callback cancellation (delete own pending request)
        window.cancelCallbackRequest = async function () {
            const result = await Swal.fire({
                title: 'تأكيد الإلغاء',
                text: 'هل أنت متأكد من إلغاء طلب العودة؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'نعم، إلغاء الطلب',
                cancelButtonText: 'تراجع'
            });

            if (!result.isConfirmed) {
                return;
            }

            const btn = document.getElementById('cancel-callback-btn');
            btn.disabled = true;
            btn.textContent = 'جاري الإلغاء...';

            const success = await deleteCallbackRequest(volunteerCode);

            if (success) {
                // Reload data to update UI
                await loadVolunteerData();
                Swal.fire({
                    icon: 'success',
                    title: 'تم الإلغاء',
                    text: 'تم إلغاء طلبك بنجاح',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                btn.disabled = false;
                btn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg> إلغاء الطلب`;
                Swal.fire('خطأ', 'حدث خطأ أثناء إلغاء الطلب. يرجى المحاولة مرة أخرى.', 'error');
            }
        };

        // Initial load
        loadVolunteerData();

        // Subscribe to real-time updates
        subscribeToVolunteers((payload) => {
            if (payload.new?.volunteerCode === volunteerCode) {
                updateUI(payload.new);
                updateCallbackSection(payload.new);
            }
        });
    </script>
</body>

</html>