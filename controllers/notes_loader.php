<?php
// notes_loader.php
// Ensures robust loading of notes.json from any context

$notes_file = __DIR__ . '/../data/json_files/notes.json';
$notes = [];

if (file_exists($notes_file)) {
    $content = file_get_contents($notes_file);
    if ($content !== false) {
        $notes = json_decode($content, true);
        if (!is_array($notes)) {
            $notes = [];
        }
    }
}
?>