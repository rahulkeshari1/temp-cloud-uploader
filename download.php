<?php
include 'cleanup.php';
session_start();

// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

$token = $_GET['file'] ?? null;
$metaFile = __DIR__ . "/temp_uploads/{$token}.json";
$passwordFile = __DIR__ . '/passwords.json';

// Validate file token and meta
if (!$token || !file_exists($metaFile)) {
    include 'header.php';
    echo "<div class='box error NOTFOUND'>
    <i class='fas fa-exclamation-triangle'></i> Invalid or expired link.</div>";
    include 'footer.php';
    exit;
}

$meta = json_decode(file_get_contents($metaFile), true);
$filepath = __DIR__ . "/temp_uploads/" . $meta['stored'];

// Validate file existence
if (!file_exists($filepath)) {
    include 'header.php';
    echo "<div class='box error'><i class='fas fa-file-excel'></i> File not found or expired.</div>";
    include 'footer.php';
    exit;
}

// Check if password is required and validate
$passwordRequired = $meta['password_protected'] ?? false;
$passwords = file_exists($passwordFile) ? json_decode(file_get_contents($passwordFile), true) : [];

if ($passwordRequired) {
    // Check if password was already verified in this session
    $passwordVerified = $_SESSION['password_verified'][$token] ?? false;
    
    if (!$passwordVerified) {
        // Handle password submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
            $submittedPassword = $_POST['password'];
            
            if (isset($passwords[$token])) {
                if (password_verify($submittedPassword, $passwords[$token])) {
                    $_SESSION['password_verified'][$token] = true;
                    header("Location: download.php?file=$token");
                    exit;
                } else {
                    $error = "Incorrect password. Please try again.";
                }
            } else {
                $error = "Password verification failed. Please contact the file owner.";
            }
        }
        
        if (!$passwordVerified) {
            include 'header.php';
            ?>
            <style>
                .password-form {
                    max-width: 400px;
                    margin: 2rem auto;
                    background-color: #1e1e1e;
                    border: 1px solid #333;
                    border-radius: 8px;
                    padding: 1.5rem;
                    color: #f1f1f1;
                }
                
                .password-form h2 {
                    text-align: center;
                    margin-bottom: 1.5rem;
                    font-size: 1.4rem;
                }
     
                .password-input {
                    width: 100%;
                    padding: 0.8rem;
                    margin-bottom: 1rem;
                    border-radius: 6px;
                    border: 1px solid #444;
                    background-color: #2a2a2a;
                    color: #fff;
                    font-size: 1rem;
                }
                
                .password-submit {
                    width: 100%;
                    padding: 0.8rem;
                    background-color: #4361ee;
                    color: white;
                    border: none;
                    border-radius: 6px;
                    font-size: 1rem;
                    cursor: pointer;
                    transition: background-color 0.3s;
                }
                
                .password-submit:hover {
                    background-color: #3a56d4;
                }
                
                .password-error {
                    color: #f72585;
                    margin-bottom: 1rem;
                    text-align: center;
                }
                
                .password-note {
                    margin-top: 1rem;
                    font-size: 0.9rem;
                    color: #aaa;
                    text-align: center;
                }

                .NOTFOUND {
                    display: flex !important;
                    justify-content: center !important;
                    align-items: center;
                    background-color: black;
                    color: white;
                    font-size: 2rem;
                }
            </style>
            
            <div class="password-form">
                <h2><i class="fas fa-lock"></i> Password Required</h2>
                
                <?php if (isset($error)): ?>
                    <div class="password-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST">
                    <input type="password" class="password-input" name="password" placeholder="Enter password" required>
                    <button type="submit" class="password-submit">
                        <i class="fas fa-unlock"></i> Unlock File
                    </button>
                </form>
                
                <div class="password-note">
                    <i class="fas fa-info-circle"></i> This file is password protected. Please enter the password provided by the sender.
                </div>
            </div>
            <?php
            include 'footer.php';
            exit;
        }
    }
}

// Handle the download
if (isset($_GET['download'])) {
    if (time() > $meta['expires_at']) {
        include 'header.php';
        echo "<div class='box error'><i class='fas fa-clock'></i> This file has expired and can no longer be downloaded.</div>";
        include 'footer.php';
        exit;
    }
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($meta['original']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    readfile($filepath);
    exit;
}

include 'header.php';
$originalName = $meta['original'];
$displayName = strlen($originalName) > 10 ? '...' . substr($originalName, -10) : $originalName;
$filename = htmlspecialchars($displayName);
$fullFilename = htmlspecialchars($originalName);$hoursLeft = round(($meta['expires_at'] - time()) / 3600);
?>

<style>
    .box {
        max-width: 600px;
        margin: 2rem auto;
        background-color: #1e1e1e;
        border: 1px solid #333;
        border-radius: 8px;
        padding: 1.5rem;
        color: #f1f1f1;
        text-align: center;
    }

    .box h2 {
        font-size: 1.6rem;
        margin-bottom: 0.8rem;
    }

    .file-info {
        margin: 1rem 0;
        text-align: left;
        background-color: #252525;
        padding: 1rem;
        border-radius: 6px;
    }
    
    .file-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .file-info-label {
        color: #aaa;
    }
    
    .file-info-value {
        color: #f1f1f1;
        font-weight: 500;
    }
    
    .progress-container {
        width: 100%;
        background-color: #2e2e2e;
        border-radius: 5px;
        margin: 1.5rem 0;
        height: 12px;
        overflow: hidden;
        opacity: 1;
        transition: opacity 0.6s ease;
    }

    .progress-container.hide {
        opacity: 0;
        pointer-events: none;
    }

    .progress-bar {
        height: 100%;
        width: 0%;
        background: linear-gradient(to right, #4cc9f0, #4361ee);
        transition: width 0.2s linear;
    }

    .download-btn {
        display: none;
        margin-top: 1.8rem;
    }

    .download-btn a button {
        background-color: #4361ee;
        border: none;
        color: white;
        padding: 0.7rem 1.5rem;
        font-size: 1rem;
        border-radius: 6px;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .error {
        color: #f72585;
        background-color: #2c1e2f;
        max-width: 600px;
        margin: 2rem auto;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
    }

    .protection-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 0.3rem 0.6rem;
        border-radius: 4px;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }
    
    .protection-badge.locked {
        background-color: rgba(247, 37, 133, 0.1);
        color: #f72585;
        border: 1px solid rgba(247, 37, 133, 0.3);
    }
    
    .protection-badge.unlocked {
        background-color: rgba(0, 184, 148, 0.1);
        color: #00b894;
        border: 1px solid rgba(0, 184, 148, 0.3);
    }
    
    .expiry-warning {
        color: #f39c12;
        margin-top: 1rem;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .expired-badge {
        background-color: rgba(243, 156, 18, 0.1);
        color: #f39c12;
        border: 1px solid rgba(243, 156, 18, 0.3);
        padding: 0.5rem;
        border-radius: 4px;
        margin: 1rem 0;
    }

   
    .adsSection {
    display: flex;
    max-width: 600px;
    min-height: 60px;
    margin: 2rem auto;
    /* padding: 1.5rem; */
    background-color: #1e1e1e;
    border: 1px solid #333;
    border-radius: 8px;
    color: #f1f1f1;
    text-align: center;
    padding: 1rem;
    justify-content: center;  
    align-items: center;   
} 

@media (max-width: 480px) {
        .box {
            margin: 1rem;
            padding: 1rem;
        }
        .adsSection {
            padding: 1rem;

    margin: 2rem 1rem;
        }
    }
</style>

<div class="box">
    <h2><i class="fas fa-cloud-download-alt"></i> Download</h2>
    
    <div class="file-info">
        <div class="file-info-item">
            <span class="file-info-label">Filename:</span>
            <span class="file-info-value" title="<?php echo $fullFilename; ?>"><?php echo $filename; ?></span>
            </div>
        <div class="file-info-item">
            <span class="file-info-label">Size:</span>
            <span class="file-info-value"><?php echo formatFileSize($meta['size']); ?></span>
        </div>
        <div class="file-info-item">
            <span class="file-info-label">Uploaded:</span>
            <span class="file-info-value"><?php echo date('d M Y h:i A', $meta['uploaded_at']); ?></span>
        </div>
        <div class="file-info-item">
            <span class="file-info-label">Expires:</span>
            <span class="file-info-value"><?php echo date('d M Y h:i A', $meta['expires_at']); ?></span>
        </div>
        <?php if ($passwordRequired): ?>
        <div class="file-info-item">
            <span class="file-info-label">Protection:</span>
            <span class="file-info-value" style="color: #f72585;">
                <i class="fas fa-lock"></i> Password protected
            </span>
        </div>
        <?php endif; ?>
    </div>

    <?php if (time() > $meta['expires_at']): ?>
        <div class="expired-badge">
            <i class="fas fa-exclamation-circle"></i> This file has expired and is no longer available
        </div>
    <?php else: ?>
        <div class="progress-container" id="progressContainer">
            <div class="progress-bar" id="progressBar"></div>
        </div>

        <div class="download-btn" id="download-link">
            <a href="?file=<?php echo urlencode($token); ?>&download=1">
                <button><i class="fas fa-download"></i> Download Now</button>
            </a>
        </div>
        
        <?php if ($hoursLeft < 24): ?>
        <div class="expiry-warning">
            <i class="fas fa-clock"></i> This file will expire in <?php echo $hoursLeft; ?> hours
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
<div class = "adsSection"> 
    <p> Ads </p>
</div>

<script>
    const bar = document.getElementById('progressBar');
    const container = document.getElementById('progressContainer');
    const downloadBtn = document.getElementById('download-link');
    const fileMeta = <?php echo json_encode($meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES); ?>;

    <?php if (time() <= $meta['expires_at']): ?>
    let width = 0;
    const interval = setInterval(() => {
        width += 2;
        bar.style.width = width + '%';

        if (width >= 100) {
            clearInterval(interval);
            downloadBtn.style.display = 'block';
        }
    }, 100); // Fill over 5 seconds

    // Hide progress bar after 5 seconds
    setTimeout(() => {
        container.classList.add('hide');
    }, 5000);
    <?php endif; ?>
</script>

<?php 
// Helper function to format file sizes
function formatFileSize($bytes) {
    if ($bytes >= 1073741824) {
        return number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        return number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        return number_format($bytes / 1024, 2) . ' KB';
    } elseif ($bytes > 1) {
        return $bytes . ' bytes';
    } elseif ($bytes == 1) {
        return '1 byte';
    } else {
        return '0 bytes';
    }
}

include 'footer.php'; 
?>