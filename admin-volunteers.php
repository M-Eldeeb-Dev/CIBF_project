<?php
require_once 'includes/auth-guard.php';
requireAuth('admin');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="300">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>قائمة المتطوعين - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
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
            input,
            select {
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

        .status-badge {
            font-size: 0.7rem;
            padding: 4px 8px;
            border-radius: 9999px;
        }

        .status-present {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-absent {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Table Styles */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }

        .data-table th,
        .data-table td {
            padding: 10px 6px;
            text-align: right;
            border-bottom: 1px solid #e5e7eb;
            white-space: nowrap;
        }

        .data-table th {
            background-color: #1e3a5f;
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
            font-size: 0.75rem;
        }

        .data-table tbody tr:hover {
            background-color: #f0f9ff;
        }

        .data-table tbody tr:nth-child(even) {
            background-color: #f9fafb;
        }

        /* Location Cell Styling */
        .loc-hall {
            background-color: #dcfce7 !important;
            color: #166534;
            font-weight: 600;
            text-align: center;
        }

        .loc-gate {
            background-color: #fef9c3 !important;
            color: #854d0e;
            font-weight: 600;
            text-align: center;
        }

        .loc-na {
            color: #9ca3af;
            text-align: center;
        }

        /* Responsive Table Container */
        .table-container {
            overflow-x: auto;
            max-height: 70vh;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        /* Mobile Optimizations */
        @media (max-width: 1024px) {
            .data-table {
                font-size: 0.75rem;
            }

            .data-table th,
            .data-table td {
                padding: 8px 4px;
            }

            .data-table th {
                font-size: 0.65rem;
            }
        }

        @media (max-width: 768px) {
            .data-table {
                font-size: 0.7rem;
            }

            .data-table th,
            .data-table td {
                padding: 6px 3px;
            }

            /* Hide less important columns on mobile */
            .hide-mobile {
                display: none;
            }

            .status-badge {
                font-size: 0.6rem;
                padding: 2px 6px;
            }
        }

        /* Action Buttons */
        .action-btn {
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
        }

        .action-btn:hover {
            transform: scale(1.05);
        }

        .btn-edit {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .btn-delete {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Modal Styles */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 100;
            justify-content: center;
            align-items: center;
        }

        .modal-overlay.active {
            display: flex;
        }

        .modal-content {
            background-color: white;
            border-radius: 16px;
            padding: 24px;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }

        /* Data Management Card */
        .data-management-card {
            background: linear-gradient(135deg, #0643aa 0%, #2570d8 100%);
            color: white;
        }
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="assets/images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-dark-blue text-white p-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">قائمة المتطوعين</h1>
                <p class="text-sm opacity-90" id="volunteer-count">جاري التحميل...</p>
            </div>
            <a href="admin-dashboard.php" class="bg-white/20 px-4 py-2 rounded-xl hover:bg-white/30 transition">
                العودة
            </a>
        </div>
    </div>

    <div class="max-w-6xl mx-auto px-4 py-4 space-y-4">
        <!-- Data Management Section -->
        <div class="data-management-card rounded-2xl shadow-lg p-6">
            <h2 class="text-lg font-bold mb-4">إدارة البيانات</h2>
            <div class="flex flex-wrap gap-3">
                <label
                    class="bg-white/20 px-6 py-3 rounded-xl hover:bg-white/30 transition cursor-pointer flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                    </svg>
                    رفع ملف (PDF/CSV/Excel)
                    <input type="file" id="file-upload" class="hidden" accept=".pdf,.csv,.xlsx,.xls">
                </label>
                <a href="data/convertcsv.csv" download
                    class="bg-white/20 px-6 py-3 rounded-xl hover:bg-white/30 transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    تحميل البيانات (CSV)
                </a>
            </div>
            <div id="upload-status" class="mt-4 hidden">
                <div class="bg-white/10 rounded-full h-2 overflow-hidden">
                    <div id="upload-progress" class="bg-yellow h-full transition-all duration-300" style="width: 0%">
                    </div>
                </div>
                <p id="upload-message" class="text-sm mt-2 opacity-90"></p>
            </div>
        </div>

        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow-lg p-4">
            <div class="flex flex-col md:flex-row gap-3 flex-wrap">
                <input type="text" id="search-input" placeholder="بحث بالاسم أو الكود..."
                    class="flex-1 p-3 border rounded-xl focus:outline-none focus:border-primary"
                    oninput="filterVolunteers()">
                <select id="hall-filter" class="p-3 border rounded-xl focus:outline-none focus:border-primary"
                    onchange="filterVolunteers()">
                    <option value="">كل القاعات والمواقع</option>
                    <option value="hall1">قاعة 1 (قطاع A)</option>
                    <option value="hall2">قاعة 2 (قطاع B)</option>
                    <option value="hall3">قاعة 3 (قطاع C)</option>
                    <option value="hall4">قاعة 4 (قطاع D)</option>
                    <option value="hall5">قاعة 5 (قطاع C+D)</option>
                    <option value="gate1">بوابة 1 (قطاع A)</option>
                    <option value="gate2">بوابة 2 (قطاع B)</option>
                    <option value="gate3">بوابة 3 (قطاع C)</option>
                    <option value="gate4">بوابة 4 (قطاع D)</option>
                    <option value="102">غرفة المعلومات</option>
                </select>
                <select id="sector-filter" class="p-3 border rounded-xl focus:outline-none focus:border-primary"
                    onchange="filterVolunteers()">
                    <option value="">كل القطاعات</option>
                    <option value="A">قطاع A</option>
                    <option value="B">قطاع B</option>
                    <option value="C">قطاع C</option>
                    <option value="D">قطاع D</option>
                </select>
                <select id="group-filter" class="p-3 border rounded-xl focus:outline-none focus:border-primary"
                    onchange="filterVolunteers()">
                    <option value="">كل المجموعات</option>
                    <option value="theta">ثيتا</option>
                    <option value="delta">دلتا</option>
                </select>
                <select id="status-filter" class="p-3 border rounded-xl focus:outline-none focus:border-primary"
                    onchange="filterVolunteers()">
                    <option value="">كل الحالات</option>
                    <option value="present">متواجد</option>
                    <option value="absent">غير متواجد</option>
                </select>
            </div>
        </div>

        <!-- Volunteers Table -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
            <div class="table-container">
                <table class="data-table" id="volunteers-table">
                    <thead>
                        <tr>
                            <th>الاسم</th>
                            <th>الكود</th>
                            <th>المجموعة</th>
                            <th class="hide-mobile">الفترة</th>
                            <th>القطاع</th>
                            <th>10:11</th>
                            <th>11:03</th>
                            <th class="hide-mobile">3:06</th>
                            <th class="hide-mobile">6:07</th>
                            <th class="hide-mobile">Break1</th>
                            <th class="hide-mobile">Break2</th>
                            <th>الموقع الحالي</th>
                            <th>الحالة</th>
                            <th>إجراءات</th>
                        </tr>
                    </thead>
                    <tbody id="volunteers-tbody">
                        <!-- Data loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent">
            </div>
            <p class="mt-4 text-gray-600">جاري تحميل البيانات...</p>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="edit-modal" class="modal-overlay">
        <div class="modal-content">
            <h3 class="text-xl font-bold text-dark mb-4">تعديل بيانات المتطوع</h3>
            <input type="hidden" id="edit-code">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">الاسم</label>
                    <input type="text" id="edit-name"
                        class="w-full p-3 border rounded-xl focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">المجموعة</label>
                    <select id="edit-group"
                        class="w-full p-3 border rounded-xl focus:outline-none focus:border-primary">
                        <option value="ثيتا">ثيتا</option>
                        <option value="دلتا">دلتا</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">القاعة</label>
                    <select id="edit-hall" class="w-full p-3 border rounded-xl focus:outline-none focus:border-primary">
                        <option value="1">قاعة 1</option>
                        <option value="2">قاعة 2</option>
                        <option value="3">قاعة 3</option>
                        <option value="4">قاعة 4</option>
                        <option value="5">قاعة 5</option>
                        <option value="101">البوابة الرئيسية</option>
                        <option value="102">غرفة المعلومات</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">القطاع</label>
                    <select id="edit-sector"
                        class="w-full p-3 border rounded-xl focus:outline-none focus:border-primary">
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="saveEdit()"
                    class="flex-1 bg-primary text-white py-3 rounded-xl font-semibold hover:bg-blue-700 transition">
                    حفظ التعديلات
                </button>
                <button onclick="closeEditModal()"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                    إلغاء
                </button>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="delete-modal" class="modal-overlay">
        <div class="modal-content text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3 class="text-xl font-bold text-dark mb-2">حذف المتطوع</h3>
            <p class="text-gray-600 mb-2">هل أنت متأكد من حذف المتطوع:</p>
            <p id="delete-name" class="font-bold text-primary mb-4"></p>
            <input type="hidden" id="delete-code">
            <div class="flex gap-3">
                <button onclick="confirmDelete()"
                    class="flex-1 bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition">
                    حذف
                </button>
                <button onclick="closeDeleteModal()"
                    class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-semibold hover:bg-gray-300 transition">
                    إلغاء
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
            <a href="admin-map.php"
                class="flex flex-col items-center gap-1 text-gray-400 hover:text-primary transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M20.5 3l-.16.03L15 5.1 9 3 3.36 4.9c-.21.07-.36.25-.36.48V20.5c0 .28.22.5.5.5l.16-.03L9 18.9l6 2.1 5.64-1.9c.21-.07.36-.25.36-.48V3.5c0-.28-.22-.5-.5-.5zM15 19l-6-2.11V5l6 2.11V19z" />
                </svg>
                <span class="text-xs">الخرائط</span>
            </a>
            <a href="admin-volunteers.php" class="flex flex-col items-center gap-1 text-primary">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z" />
                </svg>
                <span class="text-xs font-semibold">المتطوعين</span>
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
        import { getAllVolunteers, subscribeToVolunteers, updateVolunteer, deleteVolunteer } from './assets/js/volunteers-service.js?v=<?php echo time(); ?>';

        let allVolunteers = [];

        // Format hall name helper
        function formatHallName(hallId) {
            if (hallId == 101) return 'البوابة الرئيسية';
            if (hallId == 102) return 'غرفة المعلومات';
            if (hallId >= 1 && hallId <= 5) return `قاعة ${hallId}`;
            return 'غير محدد';
        }

        // Format group name with badge styling
        function formatGroup(group) {
            if (!group) return '-';
            if (group === 'ثيتا' || group.toLowerCase() === 'theta') {
                return '<span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:4px;font-size:0.7rem">θ ثيتا</span>';
            }
            if (group === 'دلتا' || group.toLowerCase() === 'delta') {
                return '<span style="background:#dbeafe;color:#1e40af;padding:2px 8px;border-radius:4px;font-size:0.7rem">Δ دلتا</span>';
            }
            return group;
        }

        // Get location cell class based on value
        function getLocClass(loc) {
            if (!loc || loc === 'N/A' || loc === '-') return 'loc-na';
            if (loc === 'صالة') return 'loc-hall';
            if (loc === 'باب') return 'loc-gate';
            return '';
        }

        // Load volunteers
        async function loadVolunteers() {
            try {
                let volunteers = await getAllVolunteers();
                // Filter out admin account
                allVolunteers = volunteers.filter(v => v.volunteerCode !== 'O-9999');
                renderVolunteers(allVolunteers);
                document.getElementById('loading-state').style.display = 'none';
                updateCount(allVolunteers.length);
            } catch (error) {
                console.error('Error loading volunteers:', error);
                document.getElementById('loading-state').innerHTML = `
                    <p class="text-red-500">حدث خطأ في تحميل البيانات</p>
                    <button onclick="location.reload()" class="mt-4 bg-primary text-white px-6 py-2 rounded-xl">إعادة المحاولة</button>
                `;
            }
        }

        // Render volunteers as table rows
        function renderVolunteers(volunteers) {
            const tbody = document.getElementById('volunteers-tbody');

            if (volunteers.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="14" class="text-center py-12 text-gray-500">لا يوجد متطوعين مطابقين للبحث</td>
                    </tr>
                `;
                return;
            }

            tbody.innerHTML = volunteers.map(v => {
                const isOccupied = v.is_occupied === true && v.current_loc && v.current_loc !== '';
                const isPresent = v.is_present === true || isOccupied;
                
                // Format current location
                let currentLocDisplay = '-';
                let currentLocClass = 'text-gray-400';
                if (isPresent && v.current_loc) {
                    if (v.current_loc == '101') {
                        currentLocDisplay = '🚪 البوابة';
                        currentLocClass = 'bg-emerald-50 text-emerald-700 font-bold';
                    } else if (v.current_loc == '102') {
                        currentLocDisplay = 'ℹ️ غرفة المعلومات';
                        currentLocClass = 'bg-violet-50 text-violet-700 font-bold';
                    } else {
                        currentLocDisplay = `قاعة ${v.hall_id || '?'} - ${v.current_loc}`;
                        currentLocClass = 'bg-blue-50 text-blue-700 font-semibold';
                    }
                }

                return `
                <tr data-code="${v.volunteerCode}">
                    <td class="font-semibold" style="max-width:120px;overflow:hidden;text-overflow:ellipsis">${v.name}</td>
                    <td class="text-primary font-mono">${v.volunteerCode}</td>
                    <td>${formatGroup(v.group)}</td>
                    <td class="hide-mobile">${v.period || '-'}</td>
                    <td><span class="px-2 py-1 rounded text-xs font-bold" style="background:#e0e7ff;color:#3730a3">${v.sector || '-'}</span></td>
                    <td class="${getLocClass(v.loc1)}">${v.loc1 || '-'}</td>
                    <td class="${getLocClass(v.loc2)}">${v.loc2 || '-'}</td>
                    <td class="hide-mobile ${getLocClass(v.loc3)}">${v.loc3 || '-'}</td>
                    <td class="hide-mobile ${getLocClass(v.loc4)}">${v.loc4 || '-'}</td>
                    <td class="hide-mobile">${v.break1 || '-'}</td>
                    <td class="hide-mobile">${v.break2 || '-'}</td>
                    <td class="${currentLocClass} text-xs px-2 py-1 rounded">${currentLocDisplay}</td>
                    <td>
                        <span class="status-badge ${isPresent ? 'status-present' : 'status-absent'}">
                            ${isPresent ? 'متواجد' : 'غير متواجد'}
                        </span>
                    </td>
                    <td>
                        <div class="flex gap-2">
                            <button onclick="openEditModal('${v.volunteerCode}')" class="p-2 text-blue-500 hover:bg-blue-50 rounded-lg transition" title="تعديل">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button onclick="openDeleteModal('${v.volunteerCode}', '${v.name}')" class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition" title="حذف">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            `}).join('');
        }

        // Filter volunteers
        window.filterVolunteers = function () {
            const search = document.getElementById('search-input').value.toLowerCase();
            const hallFilter = document.getElementById('hall-filter').value;
            const sectorFilter = document.getElementById('sector-filter').value;
            const groupFilter = document.getElementById('group-filter').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;

            // Sector mapping for halls/gates
            const hallToSector = {
                'hall1': ['A'],
                'hall2': ['B'],
                'hall3': ['C'],
                'hall4': ['D'],
                'hall5': ['C', 'D'],
                'gate1': ['A'],
                'gate2': ['B'],
                'gate3': ['C'],
                'gate4': ['D'],
                '102': null // Info room - no sector filter
            };

            let filtered = allVolunteers.filter(v => {
                const matchesSearch = v.name.toLowerCase().includes(search) ||
                    v.volunteerCode.toLowerCase().includes(search);

                // Hall/Location filter - maps to sector
                let matchesHall = true;
                if (hallFilter) {
                    const allowedSectors = hallToSector[hallFilter];
                    if (allowedSectors === null) {
                        // Info room - check hall_id
                        matchesHall = v.hall_id == 102;
                    } else if (allowedSectors) {
                        const vSector = (v.sector || '').toUpperCase();
                        matchesHall = allowedSectors.includes(vSector);
                    }
                }

                const matchesSector = !sectorFilter || (v.sector || '').toUpperCase() === sectorFilter;

                let matchesGroup = true;
                if (groupFilter) {
                    const groupName = (v.group || '').toLowerCase();
                    if (groupFilter === 'theta') {
                        matchesGroup = groupName.includes('theta') || groupName.includes('ثيتا');
                    } else if (groupFilter === 'delta') {
                        matchesGroup = groupName.includes('delta') || groupName.includes('دلتا');
                    } else {
                        matchesGroup = groupName.includes(groupFilter);
                    }
                }

                const isOccupied = v.is_occupied === true && v.current_loc && v.current_loc !== '';
                const isPresent = v.is_present === true || isOccupied;

                const matchesStatus = !statusFilter ||
                    (statusFilter === 'present' && isPresent) ||
                    (statusFilter === 'absent' && !isPresent);
                return matchesSearch && matchesHall && matchesSector && matchesGroup && matchesStatus;
            });

            renderVolunteers(filtered);
            updateCount(filtered.length);
        };

        // Update count
        function updateCount(count) {
            document.getElementById('volunteer-count').textContent = `${count} متطوع`;
        }

        // Edit Modal Functions
        window.openEditModal = function (code) {
            const volunteer = allVolunteers.find(v => v.volunteerCode === code);
            if (!volunteer) return;

            document.getElementById('edit-code').value = code;
            document.getElementById('edit-name').value = volunteer.name;
            document.getElementById('edit-group').value = volunteer.group || 'ثيتا';
            document.getElementById('edit-hall').value = volunteer.hall_id || '1';
            document.getElementById('edit-sector').value = volunteer.sector || 'A';

            document.getElementById('edit-modal').classList.add('active');
        };

        window.closeEditModal = function () {
            document.getElementById('edit-modal').classList.remove('active');
        };

        window.saveEdit = async function () {
            const code = document.getElementById('edit-code').value;
            const data = {
                name: document.getElementById('edit-name').value,
                group: document.getElementById('edit-group').value,
                hall_id: parseInt(document.getElementById('edit-hall').value),
                sector: document.getElementById('edit-sector').value
            };

            try {
                const success = await updateVolunteer(code, data);
                if (success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم التعديل',
                        text: 'تم تحديث بيانات المتطوع بنجاح',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closeEditModal();
                    allVolunteers = await getAllVolunteers(true);
                    filterVolunteers();
                } else {
                    throw new Error('Update failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء تحديث البيانات'
                });
            }
        };

        // Delete Modal Functions
        window.openDeleteModal = function (code, name) {
            document.getElementById('delete-code').value = code;
            document.getElementById('delete-name').textContent = name;
            document.getElementById('delete-modal').classList.add('active');
        };

        window.closeDeleteModal = function () {
            document.getElementById('delete-modal').classList.remove('active');
        };

        window.confirmDelete = async function () {
            const code = document.getElementById('delete-code').value;

            try {
                const success = await deleteVolunteer(code);
                if (success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'تم الحذف',
                        text: 'تم حذف المتطوع بنجاح',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    closeDeleteModal();
                    allVolunteers = await getAllVolunteers(true);
                    filterVolunteers();
                } else {
                    throw new Error('Delete failed');
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء حذف المتطوع'
                });
            }
        };

        // File Upload Handler
        document.getElementById('file-upload').addEventListener('change', async function (e) {
            const file = e.target.files[0];
            if (!file) return;

            const statusDiv = document.getElementById('upload-status');
            const progressBar = document.getElementById('upload-progress');
            const messageEl = document.getElementById('upload-message');

            statusDiv.classList.remove('hidden');
            progressBar.style.width = '0%';
            messageEl.textContent = 'جاري رفع الملف...';

            const formData = new FormData();
            formData.append('file', file);

            try {
                progressBar.style.width = '30%';
                const response = await fetch('controllers/upload.php', {
                    method: 'POST',
                    body: formData
                });

                progressBar.style.width = '70%';
                const result = await response.json();

                if (result.success) {
                    progressBar.style.width = '100%';
                    messageEl.textContent = 'تم معالجة الملف بنجاح! جاري تحديث البيانات...';

                    // Reload volunteers data
                    allVolunteers = await getAllVolunteers(true);
                    filterVolunteers();

                    Swal.fire({
                        icon: 'success',
                        title: 'تم بنجاح',
                        text: result.message || 'تم رفع ومعالجة الملف بنجاح',
                        timer: 3000,
                        showConfirmButton: false
                    });

                    setTimeout(() => {
                        statusDiv.classList.add('hidden');
                    }, 3000);
                } else {
                    throw new Error(result.message || 'Upload failed');
                }
            } catch (error) {
                progressBar.style.width = '0%';
                messageEl.textContent = 'حدث خطأ: ' + error.message;
                Swal.fire({
                    icon: 'error',
                    title: 'خطأ',
                    text: 'حدث خطأ أثناء رفع الملف: ' + error.message
                });
            }

            // Reset file input
            e.target.value = '';
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', async () => {
            await loadVolunteers();

            // Subscribe to real-time updates
            subscribeToVolunteers(async () => {
                allVolunteers = await getAllVolunteers();
                filterVolunteers();
            });
        });
    </script>
</body>

</html>