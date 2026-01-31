<?php
require_once 'includes/auth-guard.php';
requireAuth('admin');

// Handle Notes Logic
$notes_file = __DIR__ . '/data/json_files/notes.json';
$notes = [];
if (file_exists($notes_file)) {
    $notes = json_decode(file_get_contents($notes_file), true) ?? [];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_note'])) {
        $new_note = trim($_POST['note_content']);
        if (!empty($new_note)) {
            $notes[] = $new_note;
            file_put_contents($notes_file, json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    } elseif (isset($_POST['delete_note'])) {
        $index = $_POST['note_index'];
        if (isset($notes[$index])) {
            array_splice($notes, $index, 1);
            file_put_contents($notes_file, json_encode($notes, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
    header('Location: admin-dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>لوحة الإدارة - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: calc(100px + env(safe-area-inset-bottom, 20px));
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

            input {
                font-size: 16px !important;
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

        .nav-card {
            transition: all 0.3s ease;
        }

        .nav-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="assets/images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-dark-blue text-white p-6">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">لوحة الإدارة</h1>
                <p class="text-sm opacity-90">مرحباً بك في لوحة التحكم</p>
            </div>
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center overflow-hidden">
                <img src="assets/images/logo.jpg" alt="Logo" class="w-full h-full object-cover">
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-6">
        <!-- Navigation Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <!-- Map Management Card -->
            <a href="admin-map.php"
                class="nav-card bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl shadow-xl p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">إدارة الخرائط</h3>
                        <p class="text-sm opacity-90">تعيين المتطوعين على القاعات</p>
                    </div>
                </div>
            </a>

            <!-- Volunteers List Card -->
            <a href="admin-volunteers.php"
                class="nav-card bg-gradient-to-br from-green-500 to-green-700 rounded-3xl shadow-xl p-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">قائمة المتطوعين</h3>
                        <p class="text-sm opacity-90">عرض وإدارة جميع المتطوعين</p>
                    </div>
                </div>
            </a>
            <!-- Attendance App Card -->
            <a href="https://v0-volunteer-attendance-app.vercel.app/" target="_blank"
                class="nav-card bg-gradient-to-br from-yellow-500 to-yellow-700 rounded-3xl shadow-xl p-6 text-white md:col-span-2">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">تسجيل الحضور</h3>
                        <p class="text-sm opacity-90">رابط تطبيق تسجيل الحضور والإنصراف</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Notes Management Section -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-dark mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" />
                </svg>
                إدارة التنبيهات
            </h2>

            <!-- Add Note Form -->
            <form method="POST" class="mb-6 flex gap-2">
                <input type="text" name="note_content" placeholder="اكتب التنبيه هنا..." required
                    class="flex-1 p-3 border border-gray-300 rounded-xl focus:outline-none focus:border-primary">
                <button type="submit" name="add_note"
                    class="bg-primary text-white px-6 py-2 rounded-xl font-bold hover:bg-blue-700 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    إضافة
                </button>
            </form>

            <!-- Current Notes List -->
            <div class="space-y-3">
                <?php if (empty($notes)): ?>
                    <p class="text-gray-500 text-center py-4">لا توجد تنبيهات حالياً</p>
                <?php else: ?>
                    <?php foreach ($notes as $index => $note): ?>
                        <div class="flex items-center justify-between bg-gray-50 p-3 rounded-xl border border-gray-100">
                            <p class="text-gray-800"><?php echo htmlspecialchars($note); ?></p>
                            <form method="POST" class="mr-2">
                                <input type="hidden" name="note_index" value="<?php echo $index; ?>">
                                <button type="submit" name="delete_note"
                                    class="text-red-500 hover:text-red-700 bg-red-50 p-2 rounded-lg transition"
                                    onclick="confirmNoteDelete(event, this)">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- Callback Requests Section -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-xl font-bold text-dark mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                </svg>
                طلبات العودة
                <span id="callback-count"
                    class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full text-sm hidden">0</span>
            </h2>

            <div id="callback-requests-container" class="space-y-3">
                <div class="text-center text-gray-500 py-4">
                    <svg class="w-8 h-8 mx-auto mb-2 animate-spin text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    جاري التحميل...
                </div>
            </div>
        </div>

        <a href="controllers/logout.php"
            class="block w-full text-center bg-red-100 text-red-700 font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 mb-6">
            تسجيل الخروج
        </a>
    </div>

    <!-- Callback Management Script -->
    <script type="module">
        import { getCallbackRequests, approveCallbackRequest, rejectCallbackRequest, clearCallbackRequest, assignVolunteer, subscribeToVolunteers } from './assets/js/volunteers-service.js?v=<?php echo time(); ?>';

        window.confirmNoteDelete = function (e, btn) {
            e.preventDefault();
            Swal.fire({
                title: 'هل أنت متأكد؟',
                text: "سيتم حذف التنبيه نهائياً",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'نعم، احذف',
                cancelButtonText: 'إلغاء'
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = btn.closest('form');
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'delete_note';
                    hiddenInput.value = '1';
                    form.appendChild(hiddenInput);
                    form.submit();
                }
            });
        };

        async function loadCallbackRequests() {
            const container = document.getElementById('callback-requests-container');
            const countBadge = document.getElementById('callback-count');

            try {
                const requests = await getCallbackRequests();

                if (requests.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-gray-500 py-6">
                            <svg class="w-12 h-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            لا توجد طلبات عودة حالياً
                        </div>
                    `;
                    countBadge.classList.add('hidden');
                    return;
                }

                countBadge.textContent = requests.length;
                countBadge.classList.remove('hidden');

                container.innerHTML = requests.map(r => {
                    const date = r.callback_comment_date ? new Date(r.callback_comment_date).toLocaleString('ar-EG') : 'غير محدد';
                    return `
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100" data-code="${r.volunteerCode}">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <h4 class="font-bold text-dark">${r.name}</h4>
                                    <span class="text-xs text-gray-500 bg-gray-200 px-2 py-0.5 rounded">${r.volunteerCode}</span>
                                    <span class="text-xs text-gray-400 mr-2">${r.group || ''}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400">${date}</span>
                                    <button onclick="handleDelete('${r.volunteerCode}')" title="حذف الطلب"
                                        class="text-gray-400 hover:text-red-500 p-1 rounded-lg hover:bg-red-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <p class="text-gray-700 text-sm bg-white p-3 rounded-lg border mb-3">${r.callback_comment || 'لا توجد رسالة'}</p>
                            <div class="flex gap-2">
                                <button onclick="handleApprove('${r.volunteerCode}')"
                                    class="flex-1 py-2 bg-green-500 text-white font-bold rounded-lg hover:bg-green-600 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                    موافقة
                                </button>
                                <button onclick="handleReject('${r.volunteerCode}')"
                                    class="flex-1 py-2 bg-red-500 text-white font-bold rounded-lg hover:bg-red-600 transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                    رفض
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
            } catch (error) {
                console.error('Error loading callback requests:', error);
                container.innerHTML = '<p class="text-red-500 text-center py-4">حدث خطأ أثناء تحميل الطلبات</p>';
            }
        }

        window.handleApprove = async (volunteerCode) => {
            const result = await Swal.fire({
                title: 'تأكيد الموافقة',
                text: 'هل تريد الموافقة على هذا الطلب؟',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10B981',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'نعم، موافقة',
                cancelButtonText: 'إلغاء'
            });

            if (!result.isConfirmed) return;

            const success = await approveCallbackRequest(volunteerCode);
            if (success) {
                await loadCallbackRequests();
                Swal.fire({
                    icon: 'success',
                    title: 'تم بنجاح',
                    text: 'تمت الموافقة على الطلب',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('خطأ', 'حدث خطأ أثناء الموافقة على الطلب', 'error');
            }
        };

        window.handleReject = async (volunteerCode) => {
            const result = await Swal.fire({
                title: 'تأكيد الرفض',
                text: 'هل تريد رفض هذا الطلب؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'نعم، رفض',
                cancelButtonText: 'إلغاء'
            });

            if (!result.isConfirmed) return;

            const success = await rejectCallbackRequest(volunteerCode);
            if (success) {
                await loadCallbackRequests();
                Swal.fire({
                    icon: 'success',
                    title: 'تم الرفض',
                    text: 'تم رفض الطلب بنجاح',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('خطأ', 'حدث خطأ أثناء رفض الطلب', 'error');
            }
        };

        window.handleDelete = async (volunteerCode) => {
            const result = await Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل تريد حذف هذا الطلب نهائياً؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'نعم، حذف',
                cancelButtonText: 'إلغاء'
            });

            if (!result.isConfirmed) return;

            const success = await clearCallbackRequest(volunteerCode);
            if (success) {
                await loadCallbackRequests();
                Swal.fire({
                    icon: 'success',
                    title: 'تم الحذف',
                    text: 'تم حذف الطلب بنجاح',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('خطأ', 'حدث خطأ أثناء حذف الطلب', 'error');
            }
        };

        // Initial load
        document.addEventListener('DOMContentLoaded', loadCallbackRequests);

        // Subscribe to realtime updates
        subscribeToVolunteers((payload) => {
            // Reload if callback status changed
            if (payload.new?.callback_comment_approval !== payload.old?.callback_comment_approval) {
                loadCallbackRequests();
            }
        });
    </script>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-around py-3">
            <a href="admin-dashboard.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                </svg>
                <span class="text-xs font-semibold">الرئيسية</span>
            </a>

            <a href="admin-map.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" />
                </svg>
                <span class="text-xs">الخرائط</span>
            </a>

            <a href="admin-volunteers.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span class="text-xs">المتطوعين</span>
            </a>

            <a href="controllers/logout.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-red-500 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                </svg>
                <span class="text-xs">خروج</span>
            </a>
        </div>
    </div>
</body>

</html>