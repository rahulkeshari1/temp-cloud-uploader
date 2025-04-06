<?php
// Configuration
$maxFileSize = 20 * 1024 * 1024; // 20MB
$uploadDir = __DIR__ . '/temp_uploads/';
$passwordFile = __DIR__ . '/passwords.json';

// Create upload directory if needed
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Create password file if it doesn't exist
if (!file_exists($passwordFile)) {
    file_put_contents($passwordFile, json_encode([]));
}

function generateToken($length = 10) {
    return bin2hex(random_bytes($length));
}

function encryptPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

$error = '';
$success = '';
$passwordProtected = false;
$password = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];
        $passwordProtected = isset($_POST['password_protect']) && $_POST['password_protect'] === 'on';
        $password = $_POST['password'] ?? '';

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = 'Upload error: ' . $file['error'];
        } elseif ($file['size'] > $maxFileSize) {
            $error = 'File too large (max 20MB)';
        } else {
            // Determine file icon based on type
            $fileIcon = getFileIcon($file['type'], $file['name']);
            
            $filename = time() . '_' . basename($file['name']);
            $token = generateToken();
            $safeFilename = $token . '.dat';
            $targetFile = $uploadDir . $safeFilename;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
                // Set timezone to Indian Standard Time
                date_default_timezone_set('Asia/Kolkata');
                $uploadTime = time();
                $expiryTime = $uploadTime + (24 * 60 * 60); // 24 hours from now
                
                $meta = [
                    'original' => $filename,
                    'stored' => $safeFilename,
                    'uploaded_at' => $uploadTime,
                    'expires_at' => $expiryTime,
                    'size' => $file['size'],
                    'type' => $file['type'],
                    'icon' => $fileIcon,
                    'password_protected' => $passwordProtected
                ];
                
                // Store password if enabled
                if ($passwordProtected && !empty($password)) {
                    $passwords = json_decode(file_get_contents($passwordFile), true);
                    $passwords[$token] = encryptPassword($password);
                    file_put_contents($passwordFile, json_encode($passwords));
                }
                
                file_put_contents("$uploadDir/$token.json", json_encode($meta));

                // Format dates for display
                $uploadDate = date('d M Y h:i A', $uploadTime);
                $expiryDate = date('d M Y h:i A', $expiryTime);

                // Get the correct base URL
                $baseUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
                $downloadUrl = rtrim($baseUrl . $scriptPath, '/') . '/download.php?file=' . $token;

                $success = "
                <div class='upload-success-card'>
                    <div class='upload-success-content'>
                        <div class='file-icon-display'>$fileIcon</div>
                        <div class='upload-details'>
                            <h3 class='upload-success-title'>
                                <i class='fas fa-check-circle'></i> Upload Successful!
                            </h3>
                            <div class='file-info-grid'>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Name:</span>
                                    <span class='file-info-value'>".htmlspecialchars($file['name'])."</span>
                                </div>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Size:</span>
                                    <span class='file-info-value'>".formatFileSize($file['size'])."</span>
                                </div>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Type:</span>
                                    <span class='file-info-value'>{$file['type']}</span>
                                </div>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Uploaded:</span>
                                    <span class='file-info-value'>$uploadDate (IST)</span>
                                </div>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Expires:</span>
                                    <span class='file-info-value'>$expiryDate (IST)</span>
                                </div>
                                <div class='file-info-item'>
                                    <span class='file-info-label'>Protection:</span>
                                    <span class='file-info-value'>".($passwordProtected ? '🔒 Password protected' : '🔓 No password')."</span>
                                </div>
                            </div>
                            <div class='upload-actions'>
                                <a href='download.php?file=$token' class='download-link-btn' id='downloadBtn'>
                                    <i class='fas fa-download'></i> Download
                                </a>
                                <button class='share-link-btn' data-token='$token' id='shareBtn'>
                                    <i class='fas fa-share-alt'></i> Share
                                </button>
                                <button class='copy-link-btn' data-token='$token' data-url='$downloadUrl'>
                                    <i class='fas fa-copy'></i> Copy Link
                                </button>
                            </div>
                            ".($passwordProtected ? "
                            <div class='password-note'>
                                <i class='fas fa-info-circle'></i> This file is password protected. Share the password separately.
                            </div>
                            " : "")."
                        </div>
                    </div>
                    <div class='upload-progress-bar'></div>
                </div>";
            } else {
                $error = "Upload failed. Please try again.";
            }
        }
    }
}


function getFileIcon($mimeType, $filename) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    
    // Map of common file extensions to emojis
    $extensionIcons = [
        // Documents
        'pdf' => '📄',
        'doc' => '📄', 'docx' => '📄',
        'xls' => '📊', 'xlsx' => '📊',
        'ppt' => '📊', 'pptx' => '📊',
        'txt' => '📝', 'rtf' => '📝',
        'csv' => '📊',
        
        // Images
        'jpg' => '🖼️', 'jpeg' => '🖼️', 
        'png' => '🖼️', 'gif' => '🖼️',
        'svg' => '🖼️', 'webp' => '🖼️',
        'bmp' => '🖼️', 'tiff' => '🖼️',
        
        // Audio
        'mp3' => '🎵', 'wav' => '🎵',
        'ogg' => '🎵', 'm4a' => '🎵',
        'flac' => '🎵',
        
        // Video
        'mp4' => '🎬', 'mov' => '🎬',
        'avi' => '🎬', 'mkv' => '🎬',
        'webm' => '🎬', 'wmv' => '🎬',
        
        // Archives
        'zip' => '🗄️', 'rar' => '🗄️',
        '7z' => '🗄️', 'tar' => '🗄️',
        'gz' => '🗄️',
        
        // Code
        'html' => '📄', 'htm' => '📄',
        'js' => '📄', 'json' => '🔣',
        'css' => '📄', 'php' => '📄',
        'py' => '📄', 'java' => '📄',
        'c' => '📄', 'cpp' => '📄',
        'h' => '📄', 'sh' => '📄',
        'sql' => '📄',
        
        // Other
        'exe' => '⚙️', 'dmg' => '💽',
        'apk' => '📱', 'iso' => '💽'
    ];
    
    // Check by extension first
    if (isset($extensionIcons[$extension])) {
        return $extensionIcons[$extension];
    }
    
    // Fallback to mime type
    if (strpos($mimeType, 'image/') === 0) return '🖼️';
    if (strpos($mimeType, 'text/') === 0) return '📝';
    if (strpos($mimeType, 'audio/') === 0) return '🎵';
    if (strpos($mimeType, 'video/') === 0) return '🎬';
    if (strpos($mimeType, 'application/pdf') === 0) return '📄';
    if (strpos($mimeType, 'application/json') === 0) return '🔣';
    if (strpos($mimeType, 'application/zip') === 0) return '🗄️';
    
    return '📁'; // Default file icon
}

function formatFileSize($bytes) {
    if ($bytes === 0) return '0 Bytes';
    $k = 1024;
    $sizes = ['Bytes', 'KB', 'MB', 'GB'];
    $i = floor(log($bytes) / log($k));
    return round($bytes / pow($k, $i), 1) . ' ' . $sizes[$i];
}

// Include header
require 'header.php';
?>

<!-- Dark Theme Styles -->
<style>
:root {
    --bg-dark: #121212;
    --bg-darker: #0d0d0d;
    --bg-card: #1e1e1e;
    --bg-card-hover: #252525;
    --primary: #6c5ce7;
    --primary-hover: #5649d1;
    --secondary: #00cec9;
    --success: #00b894;
    --success-hover: #00a884;
    --error: #d63031;
    --text-primary: #f8f9fa;
    --text-secondary: #adb5bd;
    --border-color: #333;
    --shadow-color: rgba(0, 0, 0, 0.3);
}

body {
    background-color: var(--bg-dark);
    color: var(--text-primary);
    transition: all 0.3s ease;
    font-family: 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
}

/* Upload Container */
.upload-container {
    background: var(--bg-card);
    border-radius: 16px;
    padding: 1.5rem;
    box-shadow: 0 8px 32px var(--shadow-color);
    border: 1px solid var(--border-color);
    max-width: 800px;
    margin: 1rem auto;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    width: calc(100% - 2rem);
}

.upload-container:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 40px var(--shadow-color);
}

/* Error Message */
.error-message {
    background: rgba(214, 48, 49, 0.15);
    color: var(--error);
    padding: 1rem;
    border-radius: 12px;
    margin-bottom: 1.5rem;
    border-left: 4px solid var(--error);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    animation: fadeIn 0.4s ease;
    font-size: 0.9rem;
}

/* Success Card */
.upload-success-card {
    /* background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
    border-radius: 16px; */
    padding: 10px;
    margin: 0 0;
    /* border-left: 4px solid var(--success); */
    position: relative;
    overflow: hidden;
    animation: slideUp 0.5s ease;
}

.password-generator {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.5rem;
}

.password-generator .password-input {
    flex: 1;
    margin-bottom: 0;
}

.generate-password-btn, .copy-password-btn {
    padding: 0.75rem;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.generate-password-btn {
    background: rgba(0, 184, 148, 0.2);
    color: var(--success);
    border: 1px solid var(--success);
}

.generate-password-btn:hover {
    background: rgba(0, 184, 148, 0.3);
}

.copy-password-btn {
    background: rgba(108, 92, 231, 0.2);
    color: var(--primary);
    border: 1px solid var(--primary);
}

.copy-password-btn:hover {
    background: rgba(108, 92, 231, 0.3);
}

.copy-password-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.password-strength {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.strength-meter {
    flex: 1;
    height: 4px;
    background: var(--bg-dark);
    border-radius: 2px;
    overflow: hidden;
}

.strength-level {
    height: 100%;
    width: 0%;
    background: var(--error);
    transition: width 0.3s ease, background 0.3s ease;
}

.upload-success-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    /* background: radial-gradient(circle at 20% 30%, rgba(0, 184, 148, 0.1) 0%, transparent 70%); */
}

.upload-success-content {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    position: relative;
    z-index: 1;
    flex-direction: column;
}

.file-icon-display {
    font-size: 2.5rem;
    background: rgba(108, 92, 231, 0.1);
    padding: 1rem;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 60px;
    min-height: 60px;
    border: 1px solid rgba(108, 92, 231, 0.3);
}

.upload-details {
    width: 100%;
}

.upload-success-title {
    color: var(--success);
    margin-bottom: 1rem;
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    justify-content: center;
}

.file-info-grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 0.75rem;
    margin-bottom: 1.25rem;
}

.file-info-item {
    background: rgba(30, 30, 30, 0.7);
    padding: 0.75rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
}

.file-info-label {
    display: block;
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-bottom: 0.25rem;
}

.file-info-value {
    font-weight: 500;
    word-break: break-word;
    font-size: 0.9rem;
}

.upload-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    gap: 0.75rem;
    margin-bottom:30px;

}

.password-note {
    background: rgba(108, 92, 231, 0.1);
    padding: 0.75rem;
    border-radius: 8px;
    margin-top: 1rem;
    font-size: 0.85rem;
    color: var(--secondary);
    border: 1px solid rgba(108, 92, 231, 0.3);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.download-link-btn, .share-link-btn, .copy-link-btn {
    padding: 0.6rem 1rem;
    font-size: 0.9rem;
    text-align: center;
    box-sizing: border-box;
    border-radius: 8px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    cursor: pointer;
    font-weight: 500;
    justify-content: center;
}

.download-link-btn {
    background: var(--primary);
    color: white;
    border: none;
}

.download-link-btn:hover {
    background: var(--primary-hover);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(108, 92, 231, 0.3);
}

.share-link-btn {
    background: rgba(0, 206, 201, 0.2);
    color: var(--secondary);
    border: 1px solid var(--secondary);
}

.share-link-btn:hover {
    background: rgba(0, 206, 201, 0.3);
    transform: translateY(-2px);
}

.copy-link-btn {
    background: rgba(108, 92, 231, 0.2);
    color: var(--primary);
    border: 1px solid var(--primary);
}

.copy-link-btn:hover {
    background: rgba(108, 92, 231, 0.3);
    transform: translateY(-2px);
}

.upload-progress-bar {
    
    position: absolute;
    bottom: 0;
    left: 0;
    height: 4px;
    width: 100%;
    background: linear-gradient(90deg, var(--primary) 0%, var(--secondary) 100%);
    animation: progressBar 2s ease-in-out;
}

/* Upload Form */
.upload-header {
    margin-bottom: 1.5rem;
    margin-top: 1.5rem;

    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    font-size: 1.5rem;
    font-weight: 600;
    justify-content: center;
    text-align: center;
}

.upload-header i {
    color: var(--primary);
}

.upload-form {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.file-upload-label {
    background: var(--bg-darker);
    color: var(--text-primary);
    padding: 1.5rem;
    border-radius: 12px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px dashed var(--border-color);
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.75rem;
    position: relative;
}

.file-upload-label:hover, .file-upload-label.drag-over {
    background: var(--bg-card-hover);
    border-color: var(--primary);
}

.file-upload-label i {
    font-size: 2rem;
    color: var(--primary);
}

.file-upload-label span {
    font-size: 1rem;
    font-weight: 500;
}

.file-upload-label input {
    display: none;
}

.file-name-display {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.85rem;
    padding: 0.6rem;
    background: var(--bg-darker);
    border-radius: 8px;
    border: 1px solid var(--border-color);
    margin-top: -5px;
    word-break: break-all;
}

.password-section {
    background: var(--bg-darker);
    padding: 1rem;
    border-radius: 12px;
    border: 1px solid var(--border-color);
    margin-top: -1rem;
}

.password-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    /* margin-bottom: 0.75rem; */
}

.password-toggle input[type="checkbox"] {
    width: 18px;
    height: 18px;
    accent-color: var(--primary);
}

.password-toggle label {
    font-size: 0.95rem;
    cursor: pointer;
}

.password-fields {
    display: none;
    margin-top: 0.75rem;
    animation: fadeIn 0.3s ease;
}

.password-fields.show {
    display: block;
}

.password-input {
    width: 100%;
    padding: 0.75rem;
    border-radius: 8px;
    border: 1px solid var(--border-color);
    background: var(--bg-dark);
    color: var(--text-primary);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.password-input:focus {
    outline: none;
    border-color: var(--primary);
}

.password-note-small {
    font-size: 0.8rem;
    color: var(--text-secondary);
    margin-top: 0.50rem;
}

.upload-submit-btn {
    background: var(--success);
    color: white;
    border: none;
    padding: 1rem;
    border-radius: 12px;
    font-size: 1rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.upload-submit-btn:hover {
    background: var(--success-hover);
    transform: translateY(-3px);
    box-shadow: 0 6px 16px rgba(0, 184, 148, 0.3);
}

.upload-submit-btn:disabled {
    background: var(--bg-darker);
    color: var(--text-secondary);
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.upload-info {
    text-align: center;
    color: var(--text-secondary);
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    margin-top: -0.5rem;
    line-height: 1.4;
}

/* Upload Overlay */
.upload-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(18, 18, 18, 0.95);
    z-index: 9999;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    gap: 1.5rem;
    backdrop-filter: blur(5px);
}

.upload-overlay.active {
    display: flex;
}

.spinner {
    border: 5px solid rgba(108, 92, 231, 0.1);
    border-top: 5px solid var(--primary);
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
}

.upload-status {
    color: var(--text-primary);
    font-size: 1.1rem;
    font-weight: 500;
    text-align: center;
    max-width: 80%;
    margin: 0 auto;
}

/* Toast Notification */
.toast {
    position: fixed;
    bottom: 20px;
    left: 20px;
    right: 20px;
    transform: none;
    background: var(--error);
    color: white;
    padding: 0.8rem 1.2rem;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 0.75rem;
    z-index: 10000;
    opacity: 0;
    transition: opacity 0.3s ease;
    max-width: calc(100% - 40px);
    box-sizing: border-box;
}

.toast.show {
    opacity: 1;
}

.toast.success {
    background: var(--success);
}

.toast i {
    font-size: 1.1rem;
}

.toast-message {
    flex: 1;
}

/* Animations */
@keyframes spin {
    to { transform: rotate(360deg); }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-10px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes fadeOut {
    to { opacity: 0; }
}

@keyframes progressBar {
    from { width: 0; }
    to { width: 100%; }
}

/* Responsive */
@media (min-width: 480px) {
    .upload-container {
        padding: 2rem;
    }
    
    .upload-success-content {
        flex-direction: row;
        align-items: flex-start;
        text-align: left;
    }
    
    .upload-success-title {
        justify-content: flex-start;
    }
    
    .upload-actions {
        margin-bottom:30px;
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
    }
    
    .file-info-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }
    
    .download-link-btn, .share-link-btn, .copy-link-btn {
        flex: none;
        padding: 0.75rem 1.5rem;
    }
    
    .toast {
        left: 50%;
        right: auto;
        width: auto;
        max-width: 80%;
        transform: translateX(-50%);
    }
}

@media (min-width: 768px) {
    .upload-container {
        padding: 2.5rem;
        margin: 2rem auto;
    }
    
    .file-icon-display {
        font-size: 3.5rem;
        min-width: 80px;
        min-height: 80px;
    }
    
    .upload-success-title {
        font-size: 1.5rem;
    }
    
    .upload-header {
        font-size: 1.8rem;
    }
    
    .file-upload-label {
        padding: 2rem;
    }
    
    .file-upload-label i {
        font-size: 2.5rem;
    }
    
    .upload-submit-btn {
        padding: 1.25rem;
    }
}

/* Mobile-specific adjustments */
@media (max-width: 359px) {
    .upload-container {
        padding: 1rem;
        width: calc(100% - 1rem);
    }
    
    .upload-header {
        font-size: 1.3rem;
    }
    
    .file-upload-label {
        padding: 1rem;
    }
    
    .download-link-btn, .share-link-btn, .copy-link-btn {
        min-width: 100%;
    }
}
.passBtns {
    display: flex;
    gap: 1rem; /* Space between buttons */
    width: 100%;
    margin: 1rem 0;
}
</style>

<div class="upload-container">
    <?php if ($error): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> 
            <div><?php echo $error; ?></div>
        </div>
    <?php endif; ?>

    <?php echo $success; ?>

    <h2 class="upload-header">
        <i class="fas fa-cloud-upload-alt"></i> File Upload
    </h2>

    <form method="post" enctype="multipart/form-data" class="upload-form" id="uploadForm">
        <div>
            <label for="file-upload" class="file-upload-label" id="dropZone">
                <i class="fas fa-file-upload"></i>
                <span>Drag & drop files or click to browse</span>
                <input id="file-upload" type="file" name="file" required>
            </label>
            <div id="file-name" class="file-name-display">
                <i class="fas fa-info-circle"></i> No file selected (Max 20MB)
            </div>
        </div>

        <div class="password-section">
    <div class="password-toggle">
        <input type="checkbox" id="password-protect" name="password_protect">
        <label for="password-protect">Password protect this file</label>
    </div>
    <div class="password-fields" id="passwordFields">
        <div class="password-generator">
            <input type="password" class="password-input" name="password" placeholder="Enter password" id="passwordInput">
            </div>
            <div class = "passBtns"> 
              <button type="button" id="generatePassword" class="generate-password-btn">
                <i class="fas fa-key"></i> Generate
            </button>
            <button type="button" id="copyPassword" class="copy-password-btn" disabled>
                <i class="fas fa-copy"></i> Copy
            </button>
           </div>
       
       
        <div class="password-strength">
            <span>Strength:</span>
            <div class="strength-meter">
                <div class="strength-level"></div>
            </div>
        </div>
        <div class="password-note-small">
            <i class="fas fa-info-circle"></i> Password must be shared separately with recipients
        </div>
    </div>
</div>

        <button type="submit" class="upload-submit-btn" id="submitBtn" disabled>
            <i class="fas fa-upload"></i> Upload File
        </button>

        <div class="upload-info">
            <i class="fas fa-shield-alt"></i> Files are automatically deleted after 24 hours
        </div>
    </form>
</div>

<!-- Upload loading overlay -->
<div class="upload-overlay" id="uploadOverlay">
    <div class="spinner"></div>
    <div class="upload-status">Processing your file...</div>
</div>

<!-- Toast Notification -->
<div class="toast" id="toast">
    <i class="fas fa-exclamation-circle"></i>
    <div class="toast-message" id="toastMessage"></div>
</div>

<!-- JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // File upload handling
    const fileInput = document.getElementById('file-upload');
    const fileNameDisplay = document.getElementById('file-name');
    const submitBtn = document.getElementById('submitBtn');
    const dropZone = document.getElementById('dropZone');
    const uploadForm = document.getElementById('uploadForm');
    const maxFileSize = <?php echo $maxFileSize; ?>;
    const toast = document.getElementById('toast');
    const toastMessage = document.getElementById('toastMessage');
    const passwordToggle = document.getElementById('password-protect');
    const passwordFields = document.getElementById('passwordFields');
    const passwordInput = document.getElementById('passwordInput');
    const generatePasswordBtn = document.getElementById('generatePassword');
    const copyPasswordBtn = document.getElementById('copyPassword');



    // clear post data 
    if (window.performance && window.performance.navigation.type === window.performance.navigation.TYPE_RELOAD) {
        if (window.history.replaceState) {
            // Remove the POST data from the history
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        
        // Clear form fields
        document.getElementById('uploadForm').reset();
        document.getElementById('file-name').innerHTML = '<i class="fas fa-info-circle"></i> No file selected (Max 20MB)';
        document.getElementById('passwordInput').value = '';
        
        // Hide password fields if shown
        document.getElementById('passwordFields').classList.remove('show');
        document.getElementById('password-protect').checked = false;
        
        // Hide any success messages
        const successCard = document.querySelector('.upload-success-card');
        if (successCard) {
            successCard.remove();
        }
    }

    // Toggle password fields
    passwordToggle.addEventListener('change', function() {
        if (this.checked) {
            passwordFields.classList.add('show');
        } else {
            passwordFields.classList.remove('show');
        }
    });

    // Handle file selection
    fileInput.addEventListener('change', function() {
        if (this.files.length > 0) {
            const file = this.files[0];
            updateFileDisplay(file);
        }
    });

    // Handle drag and drop
    ['dragover', 'dragenter'].forEach(event => {
        dropZone.addEventListener(event, (e) => {
            e.preventDefault();
            dropZone.classList.add('drag-over');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach(event => {
        dropZone.addEventListener(event, () => {
            dropZone.classList.remove('drag-over');
        });
    });

    dropZone.addEventListener('drop', (e) => {
        e.preventDefault();
        
        if (e.dataTransfer.files.length > 0) {
            const file = e.dataTransfer.files[0];
            fileInput.files = e.dataTransfer.files;
            updateFileDisplay(file);
        }
    });

    // Update file display and validate
    function updateFileDisplay(file) {
        if (file.size > maxFileSize) {
            showToast('File exceeds maximum size of 20MB');
            submitBtn.disabled = true;
            fileNameDisplay.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${file.name} (Too large)`;
            fileNameDisplay.style.color = 'var(--error)';
        } else {
            submitBtn.disabled = false;
            fileNameDisplay.innerHTML = `<i class="fas fa-file"></i> ${file.name} (${formatFileSize(file.size)})`;
            fileNameDisplay.style.color = 'var(--text-secondary)';
        }
    }

    // Generate random password
    function generatePassword() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()';
        let password = '';
        
        // Generate 12-character password
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        passwordInput.value = password;
        copyPasswordBtn.disabled = false;
        updatePasswordStrength(password);
    }

    // Copy password to clipboard
    function copyPassword() {
        if (!passwordInput.value) return;
        
        navigator.clipboard.writeText(passwordInput.value).then(() => {
            showToast('Password copied to clipboard!', true);
            
            // Visual feedback
            copyPasswordBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyPasswordBtn.style.background = 'var(--success)';
            copyPasswordBtn.style.color = 'white';
            copyPasswordBtn.style.borderColor = 'var(--success)';
            
            setTimeout(() => {
                copyPasswordBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                copyPasswordBtn.style.background = 'rgba(108, 92, 231, 0.2)';
                copyPasswordBtn.style.color = 'var(--primary)';
                copyPasswordBtn.style.borderColor = 'var(--primary)';
            }, 2000);
        }).catch(err => {
            showToast('Failed to copy password');
            console.error('Failed to copy:', err);
        });
    }

    // Update password strength indicator
    function updatePasswordStrength(password) {
        const strengthMeter = document.querySelector('.strength-level');
        if (!strengthMeter) return;
        
        let strength = 0;
        
        // Length check
        if (password.length >= 8) strength += 1;
        if (password.length >= 12) strength += 1;
        
        // Character diversity checks
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[^A-Za-z0-9]/.test(password)) strength += 1;
        
        // Update meter
        const width = (strength / 6) * 100;
        strengthMeter.style.width = width + '%';
        
        // Update color based on strength
        if (width < 40) {
            strengthMeter.style.background = 'var(--error)';
        } else if (width < 70) {
            strengthMeter.style.background = '#f39c12';
        } else {
            strengthMeter.style.background = 'var(--success)';
        }
    }

    // Show toast notification
    function showToast(message, isSuccess = false) {
        toastMessage.textContent = message;
        toast.className = isSuccess ? 'toast success' : 'toast';
        toast.innerHTML = `<i class="fas ${isSuccess ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                          <div class="toast-message">${message}</div>`;
        
        toast.classList.add('show');
        
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Form submission
    uploadForm.addEventListener('submit', function(e) {
        const file = fileInput.files[0];
        
        if (file && file.size > maxFileSize) {
            e.preventDefault();
            showToast('File exceeds maximum size of 20MB');
            return false;
        }
        
        // Validate password if enabled
        if (passwordToggle.checked) {
            if (!passwordInput.value) {
                e.preventDefault();
                showToast('Please enter a password');
                return false;
            }
            
            if (passwordInput.value.length < 4) {
                e.preventDefault();
                showToast('Password must be at least 4 characters');
                return false;
            }
        }
        
        document.getElementById('uploadOverlay').classList.add('active');
        return true;
    });

    // Copy link functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('copy-link-btn') || e.target.closest('.copy-link-btn')) {
            const btn = e.target.classList.contains('copy-link-btn') ? e.target : e.target.closest('.copy-link-btn');
            const url = btn.getAttribute('data-url');
            
            navigator.clipboard.writeText(url).then(() => {
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
                btn.style.background = 'var(--success)';
                btn.style.color = 'white';
                btn.style.borderColor = 'var(--success)';
                
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.style.background = 'rgba(108, 92, 231, 0.2)';
                    btn.style.color = 'var(--primary)';
                    btn.style.borderColor = 'var(--primary)';
                }, 2000);
                
                showToast('Link copied to clipboard!', true);
            }).catch(err => {
                showToast('Failed to copy link');
                console.error('Failed to copy:', err);
            });
        }
        
        // Share link functionality
        if (e.target.classList.contains('share-link-btn') || e.target.closest('.share-link-btn')) {
            const btn = e.target.classList.contains('share-link-btn') ? e.target : e.target.closest('.share-link-btn');
            const token = btn.getAttribute('data-token');
            const basePath = window.location.pathname.substring(0, window.location.pathname.lastIndexOf('/') + 1);
            const url = window.location.origin + basePath + 'download.php?file=' + token;
            
            // Use Web Share API if available
            if (navigator.share) {
                navigator.share({
                    title: 'Download this file',
                    text: 'Check out this file I shared with you',
                    url: url
                }).then(() => {
                    showToast('Shared successfully!', true);
                }).catch(err => {
                    if (err.name !== 'AbortError') {
                        showToast('Could not share the file');
                        console.error('Share failed:', err);
                    }
                });
            } else {
                // Fallback for browsers without Web Share API
                navigator.clipboard.writeText(url).then(() => {
                    showToast('Link copied to clipboard!', true);
                }).catch(err => {
                    showToast('Failed to copy link');
                    console.error('Failed to copy:', err);
                });
            }
        }

        // Generate password button
        if (e.target.classList.contains('generate-password-btn') || e.target.closest('.generate-password-btn')) {
            generatePassword();
        }

        // Copy password button
        if (e.target.classList.contains('copy-password-btn') || e.target.closest('.copy-password-btn')) {
            copyPassword();
        }
    });

    // Initialize password input events
    passwordInput.addEventListener('input', function() {
        copyPasswordBtn.disabled = !this.value;
        updatePasswordStrength(this.value);
    });

    // Better mobile viewport height handling
    function setViewportHeight() {
        let vh = window.innerHeight * 0.01;
        document.documentElement.style.setProperty('--vh', `${vh}px`);
    }

    setViewportHeight();
    window.addEventListener('resize', setViewportHeight);
});
</script>

<?php require 'faq.php'; ?>

<?php require 'footer.php'; ?>