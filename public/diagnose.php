<?php
/**
 * Vexogen Diagnostic & Repair Utility
 * Place this in public/diagnose.php and open in browser:
 * https://erp.vexogen.in/diagnose.php
 */

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

$basePath = dirname(__DIR__);
$envPath = $basePath . '/.env';
$zipPath = $basePath . '/vexogen-hostinger-live.zip';
$safeGenPath = $basePath . '/vendor/thecodingmachine/safe/generated';

$message = '';
$error = '';

// Handle unzip action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'unzip') {
    if (!class_exists('ZipArchive')) {
        $error = 'ZipArchive extension is not enabled on this server.';
    } elseif (!is_file($zipPath)) {
        $error = 'Zip file vexogen-hostinger-live.zip not found at: ' . htmlspecialchars($zipPath);
    } else {
        $zip = new ZipArchive();
        if ($zip->open($zipPath) === true) {
            if ($zip->extractTo($basePath)) {
                $message = 'Successfully extracted vexogen-hostinger-live.zip to ' . htmlspecialchars($basePath);
            } else {
                $error = 'Failed to extract zip file. Check folder permissions.';
            }
            $zip->close();
        } else {
            $error = 'Failed to open zip file. It might be corrupted.';
        }
    }
}

// Check files
$envExists = is_file($envPath);
$zipExists = is_file($zipPath);
$safeGenExists = is_dir($safeGenPath);

$safeFiles = [];
if ($safeGenExists) {
    $dir81 = $safeGenPath . '/8.1';
    if (is_dir($dir81)) {
        $safeFiles = scandir($dir81);
        $safeFiles = array_filter($safeFiles, fn($f) => !in_array($f, ['.', '..'], true));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vexogen Live Diagnostics & Repair</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0b0f19;
            --card-bg: rgba(22, 30, 49, 0.7);
            --border: rgba(255, 255, 255, 0.08);
            --text: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #3b82f6;
            --primary-glow: rgba(59, 130, 246, 0.3);
            --success: #10b981;
            --error: #ef4444;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background-image: radial-gradient(circle at 10% 20%, rgba(90, 120, 250, 0.05) 0%, transparent 40%),
                              radial-gradient(circle at 90% 80%, rgba(250, 90, 120, 0.05) 0%, transparent 40%);
        }
        .container {
            width: 100%;
            max-width: 700px;
            background: var(--card-bg);
            backdrop-filter: blur(12px);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        h1 {
            font-size: 28px;
            font-weight: 700;
            margin-top: 0;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #fff 0%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        p.subtitle {
            color: var(--text-muted);
            margin-bottom: 30px;
            font-size: 16px;
        }
        .status-card {
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
            background: rgba(255, 255, 255, 0.02);
        }
        .status-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .status-row:last-child {
            border-bottom: none;
        }
        .status-label {
            font-weight: 600;
            font-size: 15px;
        }
        .status-value {
            font-size: 14px;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }
        .badge-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }
        .badge-error {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }
        .alert {
            padding: 16px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-size: 14px;
            line-height: 1.5;
        }
        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }
        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px var(--primary-glow);
            width: 100%;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px var(--primary-glow);
        }
        .btn:active {
            transform: translateY(0);
        }
        code {
            background: rgba(255, 255, 255, 0.08);
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Vexogen Live Diagnostics</h1>
        <p class="subtitle">Analyze and repair Hostinger environment settings</p>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= $error ?></div>
        <?php endif; ?>

        <div class="status-card">
            <div class="status-row">
                <span class="status-label">Environment File (<code>.env</code>)</span>
                <span class="status-value">
                    <?php if ($envExists): ?>
                        <span class="badge badge-success">Present</span>
                    <?php else: ?>
                        <span class="badge badge-error">Missing</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-row">
                <span class="status-label">Deployment Zip (<code>vexogen-hostinger-live.zip</code>)</span>
                <span class="status-value">
                    <?php if ($zipExists): ?>
                        <span class="badge badge-success">Present (<?= round(filesize($zipPath) / 1024 / 1024, 2) ?> MB)</span>
                    <?php else: ?>
                        <span class="badge badge-error">Missing</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-row">
                <span class="status-label">Safe Generator Folder (<code>vendor/.../safe/generated</code>)</span>
                <span class="status-value">
                    <?php if ($safeGenExists): ?>
                        <span class="badge badge-success">Present</span>
                    <?php else: ?>
                        <span class="badge badge-error">Missing</span>
                    <?php endif; ?>
                </span>
            </div>
            <div class="status-row">
                <span class="status-label">Safe PHP 8.1 Files Count</span>
                <span class="status-value">
                    <code><?= count($safeFiles) ?> files</code>
                </span>
            </div>
        </div>

        <?php if ($zipExists): ?>
            <div style="margin-top: 30px;">
                <p style="font-size: 14px; color: var(--text-muted); text-align: center; margin-bottom: 12px;">
                    If files are missing or corrupted, you can extract the deployment zip directly on the server:
                </p>
                <form method="POST">
                    <input type="hidden" name="action" value="unzip">
                    <button type="submit" class="btn">Extract vexogen-hostinger-live.zip</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-error" style="text-align: center;">
                Please upload <code>vexogen-hostinger-live.zip</code> to the root folder (above <code>public/</code>) to enable auto-extraction.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
