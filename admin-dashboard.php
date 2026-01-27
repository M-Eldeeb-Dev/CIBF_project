<?php
require_once 'includes/auth-guard.php';
requireAuth('admin');

// Handle Notes Logic
$notes_file = __DIR__ . '/json_files/notes.json';
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
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: env(safe-area-inset-bottom, 80px);
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
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-dark-blue text-white p-6">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">لوحة الإدارة</h1>
                <p class="text-sm opacity-90">مرحباً بك في لوحة التحكم</p>
            </div>
            <div class="w-12 h-12 bg-white rounded-full flex items-center justify-center overflow-hidden">
                <img src="images/logo.jpg" alt="Logo" class="w-full h-full object-cover">
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
                                    onclick="return confirm('هل أنت متأكد من الحذف؟')">
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

        <a href="logout.php"
            class="block w-full text-center bg-red-100 text-red-700 font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 mb-6">
            تسجيل الخروج
        </a>
    </div>

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

            <a href="logout.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-red-500 transition">
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