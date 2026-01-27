<?php if (!empty($notes)): ?>
    <?php
    $notes_string = implode(' | ', array_map('htmlspecialchars', $notes));
    // Calculate duration based on text length to maintain constant speed
    // e.g., 0.1 seconds per character
    $duration = max(20, strlen($notes_string) * 0.15);
    ?>
    <style>
        .marquee-container {
            overflow: hidden;
            white-space: nowrap;
        }

        .marquee-content {
            display: inline-block;
            animation: marquee <?php echo $duration; ?>s linear infinite;
        }

        @keyframes marquee {
            0% {
                transform: translateX(-100%);
            }

            100% {
                transform: translateX(100%);
            }
        }
    </style>
    <div class="bg-yellow-100 border-y border-yellow-200 py-2 mb-0 overflow-hidden relative shadow-sm z-40">
        <div class="marquee-container">
            <div class="marquee-content text-yellow-900 font-bold px-4">
                <?php echo $notes_string; ?>
            </div>
        </div>
    </div>
<?php endif; ?>