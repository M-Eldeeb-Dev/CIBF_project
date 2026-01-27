<?php
// Set session cookie lifetime to 1 day (86400 seconds)
ini_set('session.cookie_lifetime', 86400);
ini_set('session.gc_maxlifetime', 86400);
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'admin') {
        header('Location: admin-dashboard.php');
        exit;
    } elseif ($_SESSION['user_type'] === 'volunteer') {
        header('Location: volunteer-dashboard.php');
        exit;
    }
}

$error = '';
$useSupabase = true; // Set to false to use PHP fallback

// PHP fallback authentication (used when Supabase is not available)
if (!$useSupabase && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once 'volunteers_data.php';
    $code = trim($_POST['code'] ?? '');
    $code = preg_replace('/^O-\s*/', 'O-', strtoupper($code));

    if ($code === 'O-9999') {
        $_SESSION['user_type'] = 'admin';
        $_SESSION['user_name'] = 'المسؤول';
        $_SESSION['user_code'] = $code;
        header('Location: admin-dashboard.php');
        exit;
    } elseif ($code && isset($volunteers[$code])) {
        $volunteer_data = $volunteers[$code];
        $_SESSION['user_type'] = 'volunteer';
        $_SESSION['user_name'] = $volunteer_data['name'];
        $_SESSION['user_code'] = $code;
        $_SESSION['user_group'] = $volunteer_data['group'];
        $_SESSION['user_period'] = $volunteer_data['period'];
        $_SESSION['user_sector'] = $volunteer_data['sector'];
        $_SESSION['user_break1'] = $volunteer_data['break1'];
        $_SESSION['user_break2'] = $volunteer_data['break2'];
        $_SESSION['user_loc1'] = $volunteer_data['loc1'] ?? 'N/A';
        $_SESSION['user_loc2'] = $volunteer_data['loc2'] ?? 'N/A';
        $_SESSION['user_loc3'] = $volunteer_data['loc3'] ?? 'N/A';
        $_SESSION['user_loc4'] = $volunteer_data['loc4'] ?? 'N/A';
        header('Location: volunteer-dashboard.php');
        exit;
    } else {
        $error = 'الكود غير موجود في النظام';
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - أنا متطوع</title>
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

        .loading-spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #2570d8;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            animation: spin 1s linear infinite;
            display: none;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="bg-light min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>
    <div class="max-w-md mx-auto px-4 py-8">
        <!-- Logo Section -->
        <div class="text-center mb-8">
            <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center">
                <img src="images/logo.jpg" alt="Logo" class="rounded-2xl shadow-lg w-full h-full object-cover">
            </div>
            <h1 class="text-3xl font-bold text-dark mb-2">أنا متطوع</h1>
            <p class="text-gray-600">انضم لمئات المتطوعين في مصر</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl p-6 mb-6">
            <h2 class="text-2xl font-bold text-dark mb-6">تسجيل الدخول</h2>

            <!-- Error Message -->
            <div id="error-message"
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4 hidden"
                role="alert">
                <span id="error-text"></span>
            </div>

            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                </div>
            <?php endif; ?>

            <form id="login-form" method="POST">
                <!-- Code Input -->
                <div class="mb-6">
                    <label class="block text-sm font-semibold text-dark mb-2">الكود</label>
                    <input type="password" name="code" id="code-input" required placeholder="الكود التعريفي" dir="ltr"
                        class="w-full px-4 py-3 rounded-xl border-2 border-gray-200 focus:border-primary outline-none transition text-left">
                </div>

                <!-- Login Button -->
                <button type="submit" id="submit-btn"
                    class="w-full bg-yellow text-dark font-bold py-4 rounded-xl shadow-lg hover:shadow-xl transition transform hover:scale-105 flex items-center justify-center gap-2">
                    <span id="btn-text">دخول</span>
                    <div class="loading-spinner" id="loading-spinner"></div>
                </button>
            </form>
        </div>

        <!-- Register Link -->
        <div class="text-center mb-6">
            <p class="text-gray-600">ليس لديك كود؟ <a href="https://wa.me/+201126941087"
                    class="text-primary font-bold">تواصل مع الإدارة</a></p>
        </div>

        <!-- Footer -->
        <div class="bg-white rounded-3xl shadow-xl text-center mt-8 mb-4 text-lg text-gray-500">
            <p>Created by <a href="https://www.linkedin.com/in/mh-deeb" target="_blank"
                    class="text-primary font-bold hover:underline">Mohamed Eldeeb</a></p>
            <p class="mt-1">
                <a href="https://wa.me/+201021325101" target="_blank" class="text-green-600 transition">
                    WhatsApp
                </a>
            </p>
        </div>
    </div>

    <!-- Supabase Authentication Script -->
    <script type="module">
        import { authenticateVolunteer, setSession } from './js/auth-service.js';

        const form = document.getElementById('login-form');
        const codeInput = document.getElementById('code-input');
        const submitBtn = document.getElementById('submit-btn');
        const btnText = document.getElementById('btn-text');
        const spinner = document.getElementById('loading-spinner');
        const errorDiv = document.getElementById('error-message');
        const errorText = document.getElementById('error-text');

        function showError(message) {
            errorText.textContent = message;
            errorDiv.classList.remove('hidden');
        }

        function hideError() {
            errorDiv.classList.add('hidden');
        }

        function setLoading(loading) {
            if (loading) {
                btnText.textContent = 'جاري التحقق...';
                spinner.style.display = 'block';
                submitBtn.disabled = true;
            } else {
                btnText.textContent = 'دخول';
                spinner.style.display = 'none';
                submitBtn.disabled = false;
            }
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            hideError();
            setLoading(true);

            const code = codeInput.value.trim();

            try {
                const result = await authenticateVolunteer(code);

                if (result.success) {
                    // Store session in localStorage
                    setSession(result.data, result.isAdmin);

                    // Submit form to PHP to create server session
                    const formData = new FormData();
                    formData.append('code', code);
                    formData.append('supabase_auth', 'true');
                    formData.append('volunteer_data', JSON.stringify(result.data));
                    formData.append('is_admin', result.isAdmin ? '1' : '0');

                    const response = await fetch('login-handler.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        window.location.href = data.redirect;
                    } else {
                        showError(data.error || 'حدث خطأ في تسجيل الدخول');
                        setLoading(false);
                    }
                } else {
                    showError(result.error);
                    setLoading(false);
                }
            } catch (error) {
                console.error('Login error:', error);
                showError('حدث خطأ في الاتصال، يرجى المحاولة مرة أخرى');
                setLoading(false);
            }
        });
    </script>
</body>

</html>