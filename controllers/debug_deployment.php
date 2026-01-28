<?php
// debug_deployment.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deployment Debugger</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-4 md:p-10 font-sans">
    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-2xl p-6">
        <h1 class="text-3xl font-bold mb-6 text-gray-800 border-b pb-4">🛠️ Deployment Debugger</h1>
        
        <!-- PHP Checks -->
        <div class="mb-8 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <h2 class="text-xl font-bold mb-4 text-blue-800">1. Server-Side Checks (PHP)</h2>
            <ul class="space-y-2">
                <li class="flex items-center gap-2">
                    <span class="font-semibold text-gray-700">PHP Version:</span>
                    <span class="bg-blue-200 text-blue-900 px-2 py-1 rounded text-sm font-mono"><?php echo phpversion(); ?></span>
                </li>
                
                <!-- Map Directory Check -->
                <li>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-semibold text-gray-700">Map Directory (CIPF_Map):</span>
                        <?php 
                        $dir = 'CIPF_Map';
                        if (is_dir($dir)) {
                            echo "<span class='text-green-600 font-bold flex items-center gap-1'>✅ Exists</span>";
                        } else {
                            echo "<span class='text-red-600 font-bold flex items-center gap-1'>❌ MISSING</span>";
                        }
                        ?>
                    </div>
                </li>

                 <!-- JS Directory Check -->
                 <li>
                    <div class="flex items-center gap-2 mb-2">
                        <span class="font-semibold text-gray-700">JS Directory (js):</span>
                        <?php 
                        $dir = 'js';
                        if (is_dir($dir)) {
                            echo "<span class='text-green-600 font-bold flex items-center gap-1'>✅ Exists</span>";
                        } else {
                            echo "<span class='text-red-600 font-bold flex items-center gap-1'>❌ MISSING</span>";
                        }
                        ?>
                    </div>
                </li>
            </ul>
        </div>

        <!-- JS Checks -->
        <div class="p-4 bg-gray-50 rounded-xl border border-gray-200">
            <h2 class="text-xl font-bold mb-4 text-gray-800">2. Client-Side Checks (JS)</h2>
            
            <!-- JS Modules -->
            <div class="mb-4">
                <span class="block font-semibold text-gray-700 mb-1">JS Modules:</span>
                <div id="js-status" class="text-sm">⏳ Waiting for JS...</div>
            </div>

            <!-- Supabase -->
            <div class="mb-4">
                <span class="block font-semibold text-gray-700 mb-1">Supabase Connection:</span>
                <div id="supabase-status" class="bg-white p-3 rounded border text-sm font-mono">⏳ Testing connection...</div>
            </div>

            <!-- Map Image -->
            <div>
                <span class="block font-semibold text-gray-700 mb-1">Map Image Access:</span>
                <div id="image-status" class="bg-white p-3 rounded border text-sm font-mono">⏳ Testing image load...</div>
            </div>
        </div>
        
        <div class="mt-6 text-xs text-gray-400 text-center">
            Run this file on your hosting usage: domain.com/debug_deployment.php
        </div>
    </div>

    <!-- Diagnostic Script -->
    <script type="module">
        // Basic Error Handling
        window.onerror = function(msg, url, line) {
            document.getElementById('js-status').innerHTML += 
                `<br><span class="text-red-600">Global Error: ${msg} <br> Line: ${line}</span>`;
        };

        const jsStatus = document.getElementById('js-status');
        const sbStatus = document.getElementById('supabase-status');
        const imgStatus = document.getElementById('image-status');
        
        jsStatus.innerHTML = "<span class='text-green-600 font-bold'>✅ ES Modules Supported</span>";

        // Helper to update status
        const setStatus = (el, success, msg) => {
            el.innerHTML = success 
                ? `<span class='text-green-600 font-bold'>✅ ${msg}</span>` 
                : `<span class='text-red-600 font-bold'>❌ ${msg}</span>`;
        };

        // 1. Test Supabase Import & Connection
        try {
            console.log("Importing Supabase...");
            const { testConnection, supabase } = await import('./js/supabase-client.js');
            
            setStatus(sbStatus, null, "Supabase Client Imported. Testing connection...");
            
            const isConnected = await testConnection();
            
            if (isConnected) {
                setStatus(sbStatus, true, "Supabase Connected Successfully!");
            } else {
                setStatus(sbStatus, false, "Connection Failed. Check Console (F12) for Network Errors.");
            }

        } catch (e) {
            console.error(e);
            setStatus(sbStatus, false, `Import/Runtime Error: ${e.message}. <br>Make sure 'assets/js/supabase-client.js' exists and is readable.`);
        }

        // 2. Test Map Image Loading
        const imgPath = "CIPF_Map/CIBF-map-1.jpg";
        const img = new Image();
        img.onload = () => {
             setStatus(imgStatus, true, `Image Loaded: ${imgPath}`);
        };
        img.onerror = () => {
             setStatus(imgStatus, false, `Failed to load: ${imgPath}. <br>Possible Causes: 404 Not Found, Permissions, or Case Sensitivity.`);
        };
        img.src = imgPath;

    </script>
</body>
</html>
