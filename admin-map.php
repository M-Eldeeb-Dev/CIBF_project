<?php
require_once 'includes/auth-guard.php';
requireAuth('admin');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <!-- Removed auto-refresh - using Supabase realtime instead for better UX -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>إدارة الخرائط - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: env(safe-area-inset-bottom, 120px);
            -webkit-tap-highlight-color: transparent;
        }

        /* Mobile-First Enhancements */
        @media (max-width: 640px) {

            button,
            a,
            input[type="radio"]+label {
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

        .text-primary {
            color: #2570d8;
        }

        #map-container {
            height: 60vh;
            min-height: 400px;
            border-radius: 1rem;
            overflow: hidden;
        }

        .hall-tab {
            transition: all 0.3s ease;
        }

        .hall-tab.active {
            background-color: #2570d8;
            color: white;
        }

        .custom-popup .leaflet-popup-content-wrapper {
            border-radius: 1rem;
            padding: 0;
        }

        .custom-popup .leaflet-popup-content {
            margin: 0;
            min-width: 200px;
        }

        .legend-dot {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: inline-block;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }

        .legend-dot.red {
            background-color: #ef4444;
        }

        .legend-dot.yellow {
            background-color: #eab308;
        }

        .filter-btn {
            transition: all 0.2s ease;
        }

        .filter-btn.active {
            background-color: #2570d8 !important;
            color: white !important;
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

        .toast.info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
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
    <div class="bg-dark-blue text-white p-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">إدارة الخرائط التفاعلية</h1>
                <p class="text-sm opacity-90">إدارة مواقع المتطوعين</p>
            </div>
            <a href="admin-dashboard.php" class="bg-white/20 px-4 py-2 rounded-xl hover:bg-white/30 transition">
                العودة
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-4">
        <!-- Hall Tabs -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-4 flex gap-2 overflow-x-auto">
            <button onclick="switchToHall(1)" id="tab-1"
                class="hall-tab active flex-1 min-w-[80px] py-3 px-4 rounded-xl font-bold text-center">
                قاعة 1
            </button>
            <button onclick="switchToHall(2)" id="tab-2"
                class="hall-tab flex-1 min-w-[80px] py-3 px-4 rounded-xl font-bold text-center bg-gray-100">
                قاعة 2
            </button>
            <button onclick="switchToHall(3)" id="tab-3"
                class="hall-tab flex-1 min-w-[80px] py-3 px-4 rounded-xl font-bold text-center bg-gray-100">
                قاعة 3
            </button>
            <button onclick="switchToHall(4)" id="tab-4"
                class="hall-tab flex-1 min-w-[80px] py-3 px-4 rounded-xl font-bold text-center bg-gray-100">
                قاعة 4
            </button>
            <button onclick="switchToHall(5)" id="tab-5"
                class="hall-tab flex-1 min-w-[80px] py-3 px-4 rounded-xl font-bold text-center bg-gray-100">
                قاعة 5
            </button>
        </div>

        <!-- Legend -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-4 flex items-center justify-center gap-6">
            <div class="flex items-center gap-2">
                <span class="legend-dot red"></span>
                <span class="text-sm">متطوع معين</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="legend-dot yellow"></span>
                <span class="text-sm">موقع شاغر</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-primary font-bold">👆</span>
                <span class="text-sm">اضغط لإضافة موقع</span>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-4">
            <div class="flex flex-wrap gap-2 mb-3">
                <span class="text-sm font-bold text-gray-600 w-full mb-1">تصفية حسب القطاع:</span>
                <button onclick="filterBySector('all')" id="filter-all"
                    class="filter-btn active px-4 py-2 rounded-lg text-sm font-semibold bg-primary text-white">
                    الكل
                </button>
                <button onclick="filterBySector('A')" id="filter-A"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                    قطاع A
                </button>
                <button onclick="filterBySector('B')" id="filter-B"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                    قطاع B
                </button>
                <button onclick="filterBySector('C')" id="filter-C"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                    قطاع C
                </button>
                <button onclick="filterBySector('D')" id="filter-D"
                    class="filter-btn px-4 py-2 rounded-lg text-sm font-semibold bg-gray-100 hover:bg-gray-200">
                    قطاع D
                </button>
            </div>

            <div class="flex flex-wrap gap-2 mt-3">
                <span class="text-sm font-bold text-gray-600 w-full mb-1">تصفية حسب الفترة:</span>
                <button onclick="filterByPeriod('all')" id="filter-period-all"
                    class="filter-btn period-filter active px-4 py-2 rounded-lg text-sm font-semibold bg-primary text-white">
                    كل الفترات
                </button>
                <button onclick="filterByPeriod('10-11')" id="filter-period-10"
                    class="filter-btn period-filter px-4 py-2 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700 hover:bg-amber-200">
                    ⏰ 10-11
                </button>
                <button onclick="filterByPeriod('11-3')" id="filter-period-11"
                    class="filter-btn period-filter px-4 py-2 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700 hover:bg-amber-200">
                    ⏰ 11-3
                </button>
                <button onclick="filterByPeriod('3-6')" id="filter-period-3"
                    class="filter-btn period-filter px-4 py-2 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700 hover:bg-amber-200">
                    ⏰ 3-6
                </button>
                <button onclick="filterByPeriod('6-7')" id="filter-period-6"
                    class="filter-btn period-filter px-4 py-2 rounded-lg text-sm font-semibold bg-amber-100 text-amber-700 hover:bg-amber-200">
                    ⏰ 6-7
                </button>
            </div>
            <p id="filter-count" class="text-xs text-gray-500 mt-3 text-center"></p>
        </div>

        <!-- Find Volunteer Search Bar -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-4 relative">
            <div class="flex gap-2">
                <input type="text" id="map-find-vol-search" placeholder="ابحث عن متطوع معين على الخريطة..."
                    class="flex-1 p-3 bg-gray-50 border-none rounded-xl focus:ring-2 focus:ring-primary outline-none transition text-sm">
                <button class="bg-primary text-white p-3 rounded-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </button>
            </div>
            <div id="map-find-vol-list"
                class="absolute z-[1000] left-2 right-2 mt-1 bg-white border border-gray-100 rounded-xl shadow-2xl max-h-[300px] overflow-y-auto hidden">
                <!-- Search results -->
            </div>
        </div>

        <!-- Map Container -->
        <div class="bg-white rounded-2xl shadow-lg p-2 mb-4">
            <div id="map-container"></div>
        </div>

        <!-- Instructions -->
        <div class="bg-blue-50 rounded-2xl p-4 mb-4">
            <h3 class="font-bold text-primary mb-2">📌 تعليمات الاستخدام</h3>
            <ul class="text-sm text-gray-700 space-y-1 list-disc list-inside">
                <li>اضغط على أي مكان فارغ في الخريطة لإضافة موقع جديد</li>
                <li>اضغط على النقطة الصفراء لتعيين متطوع</li>
                <li>اضغط على النقطة الحمراء لرؤية المتطوع المعين وإزالته</li>
                <li>التحديثات تظهر مباشرة لجميع المستخدمين</li>
            </ul>
        </div>

        <!-- Manual Volunteer Assignment Section -->
        <div class="bg-white rounded-3xl shadow-2xl p-6 mb-6 border border-gray-100 relative overflow-hidden">
            <!-- Decorative Background Element -->
            <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 opacity-50"></div>

            <div class="relative">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="font-bold text-xl text-dark flex items-center gap-2">
                        <span class="bg-primary/10 p-2 rounded-xl text-primary">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </span>
                        تعيين متطوع يدوي
                    </h3>
                </div>

                <div class="space-y-6">
                    <!-- Volunteer Selection -->
                    <div>
                        <label class="block text-sm font-bold text-gray-500 mb-2 mr-1">اختر المتطوع</label>
                        <div class="relative">
                            <input type="text" id="manual-vol-search" placeholder="ابحث بالاسم أو الكود..."
                                class="w-full p-4 bg-gray-50 border-2 border-transparent rounded-2xl focus:border-primary focus:bg-white outline-none transition-all shadow-inner text-lg">

                            <!-- Search Icon Overlay -->
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </div>

                            <div id="manual-vol-list"
                                class="absolute z-20 w-full mt-2 bg-white border border-gray-100 rounded-2xl shadow-2xl max-h-[250px] overflow-y-auto hidden animate-slide-up">
                                <!-- Options will be rendered here -->
                            </div>
                        </div>
                        <input type="hidden" id="selected-manual-vol-code" value="">
                    </div>

                    <!-- Location Selection -->
                    <div>
                        <label class="block text-sm font-bold text-gray-500 mb-2 mr-1">الموقع</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label
                                class="relative flex items-center justify-center p-4 border-2 border-gray-50 rounded-2xl cursor-pointer hover:bg-gray-50 transition-all [&:has(input:checked)]:border-primary [&:has(input:checked)]:bg-blue-50 group">
                                <input type="radio" name="assignment-loc" value="101" class="hidden" checked>
                                <span
                                    class="font-bold text-gray-700 group-[&:has(input:checked)]:text-primary">البوابة</span>
                                <div class="absolute top-2 left-2 opacity-0 group-[&:has(input:checked)]:opacity-100">
                                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
                            <label
                                class="relative flex items-center justify-center p-4 border-2 border-gray-100 rounded-2xl cursor-pointer hover:bg-gray-50 transition-all [&:has(input:checked)]:border-primary [&:has(input:checked)]:bg-blue-50 group">
                                <input type="radio" name="assignment-loc" value="102" class="hidden">
                                <span class="font-bold text-gray-700 group-[&:has(input:checked)]:text-primary">غرفة
                                    المعلومات</span>
                                <div class="absolute top-2 left-2 opacity-0 group-[&:has(input:checked)]:opacity-100">
                                    <svg class="w-4 h-4 text-primary" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </label>
                        </div>
                    </div>

                    <button id="manual-assign-btn" disabled
                        class="w-full py-5 bg-primary text-white font-bold rounded-2xl shadow-xl shadow-blue-100 hover:bg-blue-700 hover:scale-[1.02] active:scale-95 transition-all disabled:opacity-30 disabled:grayscale disabled:scale-100 disabled:cursor-not-allowed text-lg">
                        تأكيد التعيين
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Reasons Log -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-4">
            <h3 class="font-bold text-lg text-dark mb-4 flex items-center gap-2">
                <span class="bg-red-100 p-2 rounded-lg text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </span>
                سجل أسباب الإزالة
            </h3>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead class="bg-gray-50 text-gray-700 font-bold border-b">
                        <tr>
                            <th class="p-3 rounded-tr-xl">المتطوع</th>
                            <th class="p-3">المجموعة</th>
                            <th class="p-3">السبب</th>
                            <th class="p-3 text-left">التاريخ</th>
                            <th class="p-3 rounded-tl-xl"></th>
                        </tr>
                    </thead>
                    <tbody id="reasons-log-body">
                        <tr>
                            <td colspan="4" class="p-4 text-center text-gray-500">جاري التحميل...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="remove-confirm-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1001] p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-slide-up">
            <div class="p-6 text-center">
                <div
                    class="w-16 h-16 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">تأكيد الإزالة</h3>
                <p class="text-gray-600">هل أنت متأكد من إزالة المتطوع من هذا الموقع؟</p>
            </div>
            <div class="flex border-t border-gray-100">
                <button id="cancel-remove-btn" class="flex-1 py-4 text-gray-500 font-bold hover:bg-gray-50 transition">
                    إلغاء
                </button>
                <button id="confirm-remove-btn"
                    class="flex-1 py-4 text-red-600 font-bold border-r border-gray-100 hover:bg-red-50 transition">
                    تأكيد الإزالة
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes slide-up {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .animate-slide-up {
            animation: slide-up 0.3s ease-out forwards;
        }

        @keyframes bounce-in {
            0% {
                transform: scale(0.9);
                opacity: 0;
            }

            70% {
                transform: scale(1.05);
                opacity: 1;
            }

            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-bounce-in {
            animation: bounce-in 0.4s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
    </style>

    <!-- Success Feedback Modal -->
    <div id="success-feedback-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[1002] p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden animate-bounce-in">
            <div class="p-6 text-center">
                <div
                    class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">تمت الإزالة بنجاح</h3>
                <p class="text-gray-600">تم تحديث حالة المتطوع وإخلاء الموقع بنجاح.</p>
            </div>
            <div class="p-4 border-t border-gray-100">
                <button onclick="location.reload()"
                    class="w-full py-3 bg-primary text-white font-bold rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                    حسناً
                </button>
            </div>
        </div>
    </div>

    <!-- Bottom Navigation -->
    <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg z-50">
        <div class="max-w-4xl mx-auto flex items-center justify-around py-3">
            <a href="admin-dashboard.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                </svg>
                <span class="text-xs">الرئيسية</span>
            </a>

            <a href="admin-map.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" />
                </svg>
                <span class="text-xs font-semibold">الخرائط</span>
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

    <script type="module">
        import { initMap, switchHall, enableSpotCreation, getCurrentHall } from './assets/js/leaflet-map.js?v=<?php echo time(); ?>';
        import { getAllVolunteers, subscribeToVolunteers } from './assets/js/volunteers-service.js?v=<?php echo time(); ?>';

        // Expose function to global scope for HTML onclick with tab styling
        window.switchToHall = async function(hallId) {
            // Update tab styling
            document.querySelectorAll('.hall-tab').forEach(tab => {
                tab.classList.remove('active', 'bg-primary', 'text-white');
                tab.classList.add('bg-gray-100');
            });
            
            const activeTab = document.getElementById(`tab-${hallId}`);
            if (activeTab) {
                activeTab.classList.add('active', 'bg-primary', 'text-white');
                activeTab.classList.remove('bg-gray-100');
            }
            
            // Call the actual hall switch function
            await switchHall(hallId);
            currentActiveTab = hallId;
            
            // Show toast notification
            showToast(`تم التبديل إلى قاعة ${hallId}`, 'info');
        };
        window.getCurrentHall = getCurrentHall;

        let currentActiveTab = 1;
        let allVolunteers = [];

        // Toast notification function
        function showToast(message, type = 'info') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;

            const icons = {
                success: '✓',
                error: '✕',
                info: 'ℹ'
            };

            toast.innerHTML = `<span>${icons[type] || ''}</span><span>${message}</span>`;
            container.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('hiding');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
        window.showToast = showToast;

        // Format hall name helper
        function formatHallName(hallId) {
            if (hallId == 101) return 'البوابة';
            if (hallId == 102) return 'غرفة المعلومات';
            if (hallId >= 1 && hallId <= 5) return `قاعة ${hallId}`;
            return hallId || 'غير محدد';
        }
        window.formatHallName = formatHallName;

        // Current period filter state
        let currentPeriodFilter = 'all';

        // Filter by period function
        window.filterByPeriod = function(period) {
            currentPeriodFilter = period;
            
            // Update button styles
            document.querySelectorAll('.period-filter').forEach(btn => {
                btn.classList.remove('active', 'bg-primary', 'text-white');
                if (!btn.classList.contains('bg-amber-100')) {
                    btn.classList.add('bg-gray-100');
                }
            });
            
            const activeBtn = document.getElementById(`filter-period-${period === 'all' ? 'all' : period.split('-')[0]}`);
            if (activeBtn) {
                activeBtn.classList.add('active', 'bg-primary', 'text-white');
                activeBtn.classList.remove('bg-gray-100', 'bg-amber-100', 'text-amber-700');
            }
            
            // Update the manual volunteer list with filter
            if (window.updateManualVolList) {
                window.updateManualVolList();
            }
            
            // Show toast
            const periodLabels = {
                'all': 'كل الفترات',
                '10-11': 'فترة 10-11',
                '11-3': 'فترة 11-3',
                '3-6': 'فترة 3-6',
                '6-7': 'فترة 6-7'
            };
            showToast(`تصفية: ${periodLabels[period] || period}`, 'info');
        };

        // Get current period filter
        window.getCurrentPeriodFilter = () => currentPeriodFilter;

        // Initialize map when page loads
        document.addEventListener('DOMContentLoaded', async () => {
            await initMap('map-container', 1);
            enableSpotCreation();
            await loadReasonsLog();
            await initManualAssignment();

            // Subscribe to updates with visual feedback
            subscribeToVolunteers(async (payload) => {
                await loadReasonsLog();
                allVolunteers = await getAllVolunteers();
                window.updateManualVolList();

                // Show realtime update notification
                if (payload.eventType === 'UPDATE') {
                    showToast('تم تحديث بيانات المتطوعين', 'info');
                }
            });
        });

        async function initManualAssignment() {
            const searchInput = document.getElementById('manual-vol-search');
            const volList = document.getElementById('manual-vol-list');
            const assignBtn = document.getElementById('manual-assign-btn');

            allVolunteers = await getAllVolunteers();

            const updateList = (filter = '') => {
                const q = filter.toLowerCase();
                const filtered = allVolunteers.filter(v =>
                    v.name.toLowerCase().includes(q) ||
                    v.volunteerCode.toLowerCase().includes(q)
                );

                if (filtered.length === 0) {
                    volList.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm italic">لا يوجد نتائج تطابق بحثك</div>';
                } else {
                    volList.innerHTML = filtered.map(v => {
                        const isAssigned = v.is_present || v.is_occupied;
                        const statusColor = isAssigned ? 'bg-red-50 text-red-500' : 'bg-green-50 text-green-600';
                        const statusLabel = isAssigned ? `${v.current_loc == '101' ? 'بوابة' : v.current_loc == '102' ? 'غرفة معلومات' : 'معين'}` : 'متاح';

                        return `
                            <div class="vol-item p-4 cursor-pointer hover:bg-gray-50 border-b border-gray-50 flex justify-between items-center transition-all group" 
                                onclick="selectManualVolunteer('${v.volunteerCode}', '${v.name.replace(/'/g, "\\'")}')">
                                <div class="flex items-center gap-3">
                                    <div class="w-2 h-2 rounded-full ${isAssigned ? 'bg-red-400' : 'bg-green-400'}"></div>
                                    <span class="font-bold text-dark group-hover:text-primary transition-colors text-sm">${v.name}</span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] px-2 py-1 rounded-lg font-bold ${statusColor}">
                                        ${statusLabel}
                                    </span>
                                    <span class="text-[10px] text-gray-400 font-mono bg-gray-100 px-2 py-0.5 rounded-md">${v.volunteerCode}</span>
                                </div>
                            </div>
                        `;
                    }).join('');
                }
                volList.classList.remove('hidden');
            };

            window.selectManualVolunteer = (code, name) => {
                document.getElementById('selected-manual-vol-code').value = code;
                searchInput.value = name;
                volList.classList.add('hidden');
                assignBtn.disabled = false;
            };

            searchInput.addEventListener('focus', () => {
                updateList(searchInput.value);
                volList.classList.remove('hidden');
            });

            searchInput.addEventListener('input', (e) => {
                updateList(e.target.value);
                volList.classList.remove('hidden');
                assignBtn.disabled = true;
            });

            // Close list when clicking outside
            document.addEventListener('click', (e) => {
                if (!searchInput.contains(e.target) && !volList.contains(e.target)) {
                    volList.classList.add('hidden');
                }
            });

            assignBtn.addEventListener('click', async () => {
                const code = document.getElementById('selected-manual-vol-code').value;
                const locValue = document.querySelector('input[name="assignment-loc"]:checked').value;
                const hallId = getCurrentHall();

                assignBtn.disabled = true;
                assignBtn.textContent = 'جاري التعيين...';

                const success = await assignVolunteer(code, hallId, locValue);

                if (success) {
                    showToast('تم تعيين المتطوع بنجاح', 'success');
                    searchInput.value = '';
                    document.getElementById('selected-manual-vol-code').value = '';
                    assignBtn.textContent = 'تأكيد التعيين';
                    allVolunteers = await getAllVolunteers();
                    updateList();
                } else {
                    showToast('حدث خطأ أثناء التعيين', 'error');
                    assignBtn.disabled = false;
                    assignBtn.textContent = 'تأكيد التعيين';
                }
            });

            window.updateManualVolList = () => updateList(searchInput.value);
        }

        /**
         * Find Volunteer on Map Logic
         */
        import { findVolunteerOnMap } from './assets/js/leaflet-map.js?v=<?php echo time(); ?>';

        async function initMapSearch() {
            const findInput = document.getElementById('map-find-vol-search');
            const findList = document.getElementById('map-find-vol-list');

            const updateFindList = (filter = '') => {
                if (!filter) {
                    findList.classList.add('hidden');
                    return;
                }

                const q = filter.toLowerCase();
                const assigned = allVolunteers.filter(v => (v.is_present || v.is_occupied) && v.current_loc);
                const filtered = assigned.filter(v =>
                    v.name.toLowerCase().includes(q) ||
                    v.volunteerCode.toLowerCase().includes(q)
                );

                if (filtered.length === 0) {
                    findList.innerHTML = '<div class="p-4 text-center text-gray-400 text-sm">لا يوجد متطوعين معينين بهذا الاسم</div>';
                } else {
                    findList.innerHTML = filtered.map(v => {
                        const hallDisplay = formatHallName(v.hall_id);
                        const locDisplay = v.current_loc == '101' ? 'البوابة' : v.current_loc == '102' ? 'غرفة المعلومات' : v.current_loc;
                        return `
                        <div class="vol-item p-3 cursor-pointer hover:bg-blue-50 border-b border-gray-50 flex justify-between items-center" 
                            onclick="focusVolunteerOnMap('${v.volunteerCode}', ${v.hall_id})">
                            <div>
                                <span class="font-bold block text-dark">${v.name}</span>
                                <span class="text-xs text-primary font-semibold">${hallDisplay} - ${locDisplay}</span>
                            </div>
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    `}).join('');
                }
                findList.classList.remove('hidden');
            };

            window.focusVolunteerOnMap = async (code, hallId) => {
                findList.classList.add('hidden');
                findInput.value = '';

                // If the volunteer is in a different hall, switch hall first
                if (window.getCurrentHall() !== hallId) {
                    await window.switchToHall(hallId);
                }

                // Zoom to volunteer
                findVolunteerOnMap(code);
            };

            findInput.addEventListener('input', (e) => updateFindList(e.target.value));

            document.addEventListener('click', (e) => {
                if (!findInput.contains(e.target) && !findList.contains(e.target)) {
                    findList.classList.add('hidden');
                }
            });
        }

        // Add to DOMContentLoaded
        document.addEventListener('DOMContentLoaded', initMapSearch);

        // Load Reasons Log
        async function loadReasonsLog() {
            const volunteers = await getAllVolunteers();
            // Filter volunteers who have a reason (indicating they left)
            const removedVolunteers = volunteers
                .filter(v => v.reason)
                // Sort by date if available, otherwise keep order
                .sort((a, b) => {
                    const dateA = a.reasons_date ? new Date(a.reasons_date) : new Date(0);
                    const dateB = b.reasons_date ? new Date(b.reasons_date) : new Date(0);
                    return dateB - dateA;
                })
                .slice(0, 50); // Show last 50

            renderReasonsLog(removedVolunteers);
        }

        function renderReasonsLog(volunteers) {
            const tbody = document.getElementById('reasons-log-body');

            if (volunteers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="5" class="p-4 text-center text-gray-500">لا توجد سجلات حالياً</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = volunteers.map(v => {
                const date = v.reasons_date ? new Date(v.reasons_date).toLocaleString('ar-EG') : 'غير مسجل';
                return `
                    <tr class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 font-semibold text-dark">${v.name}</td>
                        <td class="p-3 text-gray-600">${v.group || '-'}</td>
                        <td class="p-3">
                            <span class="bg-red-50 text-red-700 px-2 py-1 rounded text-xs font-bold">
                                ${v.reason}
                            </span>
                        </td>
                        <td class="p-3 text-gray-500 text-xs" dir="ltr">${date}</td>
                        <td class="p-3">
                            <button onclick="handleDeleteReason('${v.volunteerCode}')" 
                                class="text-gray-400 hover:text-red-600 p-2 rounded-lg hover:bg-red-50 transition"
                                title="حذف السجل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        window.handleDeleteReason = async (volunteerCode) => {
            const result = await Swal.fire({
                title: 'تأكيد الحذف',
                text: 'هل أنت متأكد من حذف هذا السجل؟',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'نعم، حذف',
                cancelButtonText: 'إلغاء'
            });

            if (!result.isConfirmed) return;

            const success = await clearDeleteReason(volunteerCode);
            if (success) {
                await loadReasonsLog();
                Swal.fire({
                    icon: 'success',
                    title: 'تم الحذف',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('خطأ', 'حدث خطأ أثناء حذف السجل', 'error');
            }
        };

        // Switch hall function (exposed globally)
        window.switchToHall = async function (hallId) {
            // Update tab styles
            document.querySelectorAll('.hall-tab').forEach(tab => {
                tab.classList.remove('active');
                tab.classList.add('bg-gray-100');
            });
            document.getElementById(`tab-${hallId}`).classList.add('active');
            document.getElementById(`tab-${hallId}`).classList.remove('bg-gray-100');

            // Switch map
            await switchHall(hallId);
            currentActiveTab = hallId;
        };
    </script>
</body>

</html>