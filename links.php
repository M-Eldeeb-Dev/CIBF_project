<?php
session_start();
if (!isset($_SESSION['user_type'])) {
    header('Location: index.php');
    exit;
}
require_once __DIR__ . '/controllers/notes_loader.php';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روابط هامة - معرض الكتاب</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="icon" type="image/jpeg" href="assets/images/logo.jpg">

<body class="bg-gray-50 font-[Cairo]">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="assets/images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <div class="bg-blue-600 text-white p-6 rounded-b-3xl shadow-lg mb-0 relative z-10">
        <h1 class="text-2xl font-bold text-center">روابط هامة</h1>
    </div>

    <!-- Notes Ticker -->
    <?php include 'includes/notes_ticker.php'; ?>

    <div class="max-w-md mx-auto px-4 mt-6">
        <div class="bg-white rounded-3xl shadow-lg p-6">
            <div class="grid gap-4">
                <!-- Instagram -->
                <a href="https://www.instagram.com/ivolunteeregypt/" target="_blank"
                    class="flex items-center gap-4 p-4 bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-2xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
                    </svg>
                    <span class="text-lg font-bold">تابعنا على انستجرام</span>
                </a>

                <!-- Facebook -->
                <a href="https://www.facebook.com/ivolunteeregypt?locale=ar_AR" target="_blank"
                    class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
                    </svg>
                    <span class="text-lg font-bold">صفحتنا على فيسبوك</span>
                </a>

                <!-- Google Play -->
                <a href="https://play.google.com/store/apps/details?id=com.maarad&hl=en" target="_blank"
                    class="flex items-center gap-4 p-4 bg-green-600 text-white rounded-2xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                        <path
                            d="M3,20.5V3.5C3,2.91 3.34,2.39 3.84,2.15L13.69,12L3.84,21.85C3.34,21.6 3,21.09 3,20.5M16.81,15.12L6.05,21.34L14.54,12.85L16.81,15.12M20.36,13.08L17.73,16.03L15.39,13.7L20.09,9L20.36,13.08M17.73,7.97L20.36,10.92L20.09,15L15.39,10.3L17.73,7.97M16.81,8.88L14.54,11.15L6.05,2.66L16.81,8.88Z" />
                    </svg>
                    <span class="text-lg font-bold">تطبيق المعرض (Google Play)</span>
                </a>

                <!-- BookFairGo -->
                <a href="https://bookfairgo.com/" target="_blank"
                    class="flex items-center gap-4 p-4 bg-blue-600 text-white rounded-2xl shadow-lg hover:shadow-xl transition transform hover:scale-105">
                    <div class="w-12 h-12 flex items-center justify-center bg-white rounded-lg p-1">
                        <img src="assets/images/book-fair-go.png" alt="BookFairGo" class="w-full h-full object-contain">
                    </div>
                    <div class="flex-1">
                        <span class="text-lg font-bold block">موقع BookFairGo</span>
                    </div>
                    <svg class="w-6 h-6 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                    </svg>
                </a>
            </div>
        </div>
        <div class="mb-8"> <!-- Spacer for bottom nav -->
            <a href="volunteer-dashboard.php" class="block mt-6 text-center text-blue-600 font-bold">
                العودة للصفحة الرئيسية
            </a>

            <!-- Footer -->
            <div class="text-center mt-8 mb-4 text-sm text-gray-500">
                <p>Created by <a href="https://www.linkedin.com/in/mh-deeb" target="_blank"
                        class="text-primary font-bold hover:underline">Mohamed Eldeeb</a></p>
                <p class="mt-1">
                    <a href="https://wa.me/+201021325101" target="_blank" class="hover:text-green-600 transition">
                        WhatsApp: +201021325101
                    </a>
                </p>
            </div>

            <div class="h-16"></div> <!-- Extra spacer to ensure content isn't hidden by fixed nav -->
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

            <a href="links.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <span class="text-xs font-semibold">روابط</span>
            </a>
        </div>
    </div>
</body>

</html>