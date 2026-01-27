<?php
session_start();
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="300">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>قائمة المتطوعين - أنا متطوع</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            padding-bottom: 80px;
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

        .volunteer-card {
            transition: all 0.3s ease;
        }

        .volunteer-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
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
    </style>
</head>

<body class="bg-light relative min-h-screen">
    <div class="fixed inset-0 z-[-1] opacity-20 pointer-events-none">
        <img src="images/logo.jpg" alt="Background Logo" class="w-full h-full object-cover">
    </div>

    <!-- Header -->
    <div class="bg-dark-blue text-white p-4">
        <div class="max-w-4xl mx-auto flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold">قائمة المتطوعين</h1>
                <p class="text-sm opacity-90" id="volunteer-count">جاري التحميل...</p>
            </div>
            <a href="admin-dashboard.php" class="bg-white/20 px-4 py-2 rounded-xl hover:bg-white/30 transition">
                العودة
            </a>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 py-4">
        <!-- Search & Filter -->
        <div class="bg-white rounded-2xl shadow-lg p-4 mb-4">
            <div class="flex flex-col md:flex-row gap-3">
                <input type="text" id="search-input" placeholder="بحث بالاسم أو الكود..."
                    class="flex-1 p-3 border rounded-xl focus:outline-none focus:border-primary"
                    oninput="filterVolunteers()">
                <select id="hall-filter" class="p-3 border rounded-xl focus:outline-none focus:border-primary"
                    onchange="filterVolunteers()">
                    <option value="">كل القاعات</option>
                    <option value="1">قاعة 1</option>
                    <option value="2">قاعة 2</option>
                    <option value="3">قاعة 3</option>
                    <option value="4">قاعة 4</option>
                    <option value="5">قاعة 5</option>
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

        <!-- Volunteers Grid -->
        <div id="volunteers-grid" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Volunteers will be loaded here -->
        </div>

        <!-- Loading State -->
        <div id="loading-state" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-primary border-t-transparent">
            </div>
            <p class="mt-4 text-gray-600">جاري تحميل البيانات...</p>
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

            <a href="logout.php" class="flex flex-col items-center gap-1 text-gray-400 hover:text-red-500 transition">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path
                        d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z" />
                </svg>
                <span class="text-xs">خروج</span>
            </a>
        </div>
    </div>

    <script type="module">
        import { getAllVolunteers, subscribeToVolunteers } from './js/volunteers-service.js';

        let allVolunteers = [];

        // Load volunteers
        async function loadVolunteers() {
            try {
                allVolunteers = await getAllVolunteers();
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

        // Render volunteers
        function renderVolunteers(volunteers) {
            const grid = document.getElementById('volunteers-grid');

            if (volunteers.length === 0) {
                grid.innerHTML = `
                    <div class="col-span-full text-center py-12 text-gray-500">
                        لا يوجد متطوعين مطابقين للبحث
                    </div>
                `;
                return;
            }

            grid.innerHTML = volunteers.map(v => `
                <div class="volunteer-card bg-white rounded-2xl shadow-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <h3 class="font-bold text-dark">${v.name}</h3>
                            <p class="text-sm text-primary">${v.volunteerCode}</p>
                        </div>
                        <span class="status-badge ${v.is_present ? 'status-present' : 'status-absent'}">
                            ${v.is_present ? 'متواجد' : 'غير متواجد'}
                        </span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <span class="text-gray-500">المجموعة:</span>
                            <span class="font-semibold">${v.group || 'N/A'}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <span class="text-gray-500">الفترة:</span>
                            <span class="font-semibold">${v.period || 'N/A'}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <span class="text-gray-500">القطاع:</span>
                            <span class="font-semibold">${v.sector || 'N/A'}</span>
                        </div>
                        <div class="bg-gray-50 p-2 rounded-lg">
                            <span class="text-gray-500">القاعة:</span>
                            <span class="font-semibold">${v.hall_id || 'غير محدد'}</span>
                        </div>
                    </div>
                    ${v.current_loc ? `
                        <div class="mt-3 bg-blue-50 p-2 rounded-lg text-sm">
                            <span class="text-gray-500">الموقع الحالي:</span>
                            <span class="font-semibold text-primary">${v.current_loc}</span>
                        </div>
                    ` : ''}
                </div>
            `).join('');
        }

        // Filter volunteers
        window.filterVolunteers = function () {
            const search = document.getElementById('search-input').value.toLowerCase();
            const hallFilter = document.getElementById('hall-filter').value;
            const groupFilter = document.getElementById('group-filter').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;

            let filtered = allVolunteers.filter(v => {
                const matchesSearch = v.name.toLowerCase().includes(search) ||
                    v.volunteerCode.toLowerCase().includes(search);
                const matchesHall = !hallFilter || v.hall_id == hallFilter;

                // Robust group matching (English & Arabic)
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

                const matchesStatus = !statusFilter ||
                    (statusFilter === 'present' && (v.is_present || v.is_occupied)) ||
                    (statusFilter === 'absent' && (!v.is_present && !v.is_occupied));
                return matchesSearch && matchesHall && matchesGroup && matchesStatus;
            });

            renderVolunteers(filtered);
            updateCount(filtered.length);
        };

        // Update count
        function updateCount(count) {
            document.getElementById('volunteer-count').textContent = `${count} متطوع`;
        }

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