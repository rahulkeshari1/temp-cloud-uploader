<?php
// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// File to store statistics
$statsFile = __DIR__ . '/file_stats.json';

// Initialize statistics
$stats = [
    'total_uploaded' => 0,
    'total_deleted' => 0,
    'remaining' => 0,
    'last_cleanup' => date('Y-m-d H:i:s') // Now in IST
];

// Load existing statistics if available
if (file_exists($statsFile)) {
    $existingStats = json_decode(file_get_contents($statsFile), true);
    if ($existingStats) {
        $stats['total_uploaded'] = $existingStats['total_uploaded'] ?? 0;
        $stats['total_deleted'] = $existingStats['total_deleted'] ?? 0;
    }
}

// Cleanup expired files (older than 24 hours)
$dir = __DIR__ . '/temp_uploads/';
$expirationTime = 24 * 60 * 60; // 24 hours in seconds

// Count files before cleanup (excluding . and ..)
$filesBefore = array_diff(scandir($dir), ['.', '..']);
$stats['remaining'] = count($filesBefore) / 2; // Each upload has 2 files

$files = scandir($dir);
$deletedPairs = 0;

foreach ($files as $file) {
    if (in_array($file, ['.', '..'])) continue;

    $filePath = $dir . $file;
    $fileExt = pathinfo($filePath, PATHINFO_EXTENSION);

    // Skip if not a file or recently modified
    if (!is_file($filePath) || filemtime($filePath) >= (time() - $expirationTime)) {
        continue;
    }

    // Delete the file
    if (unlink($filePath)) {
        // Handle associated files
        $baseName = str_replace(['.json', '.dat'], '', $file);
        
        if ($fileExt === 'json') {
            // Delete corresponding .dat file
            $dataFile = $dir . $baseName . '.dat';
            if (file_exists($dataFile) && unlink($dataFile)) {
                $deletedPairs++;
            }
        } elseif ($fileExt === 'dat') {
            // Delete corresponding .json file
            $jsonFile = $dir . $baseName . '.json';
            if (file_exists($jsonFile) && unlink($jsonFile)) {
                $deletedPairs++;
            }
        } else {
            // Single file deletion (not part of a pair)
            $deletedPairs += 0.5; // Count as half a pair
        }
    }
}

// Update deletion count
$stats['total_deleted'] += $deletedPairs;

// Cleanup expired passwords (keep only for existing files)
$passwordFile = __DIR__ . '/passwords.json';
if (file_exists($passwordFile)) {
    $passwords = json_decode(file_get_contents($passwordFile), true);
    $updatedPasswords = [];
    
    foreach ($passwords as $token => $hash) {
        if (file_exists($dir . $token . '.json')) {
            $updatedPasswords[$token] = $hash;
        }
    }
    
    // Update total uploaded count based on current passwords
    $stats['total_uploaded'] = count($updatedPasswords) + $stats['total_deleted'];
    
    file_put_contents($passwordFile, json_encode($updatedPasswords));
}

// Count remaining files after cleanup
$filesAfter = array_diff(scandir($dir), ['.', '..']);
$stats['remaining'] = count($filesAfter) / 2;
$stats['last_cleanup'] = date('Y-m-d H:i:s'); // Updated timestamp in IST

// Save statistics
file_put_contents($statsFile, json_encode($stats, JSON_PRETTY_PRINT));

// Original functionality remains unchanged below
?>