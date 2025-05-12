<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Check if user is logged in
if (!isLoggedIn()) {
    header('HTTP/1.0 403 Forbidden');
    die('Access denied. Please log in.');
}

// Check if ID parameter exists
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('HTTP/1.0 400 Bad Request');
    die('Invalid file ID.');
}

$id = (int)$_GET['id'];

try {
    // Get file information from database
    $stmt = $pdo->prepare("SELECT file_path, title FROM notes WHERE id = ?");
    $stmt->execute([$id]);
    $file = $stmt->fetch();

    if (!$file || empty($file['file_path'])) {
        header('HTTP/1.0 404 Not Found');
        die('File not found in database.');
    }

    // Debug: Output the stored path
    // error_log("Stored file path: " . $file['file_path']);

    // Construct full file path
    $baseUploadDir = 'uploads/notes/';
    $relativePath = ltrim($file['file_path'], '/');
    $filePath = $baseUploadDir . basename($relativePath);

    // Debug: Output the constructed path
    error_log("Constructed file path: " . $filePath);

    // Security checks
    if (!file_exists($filePath)) {
        header('HTTP/1.0 404 Not Found');
        die('File not found on server. Path: ' . htmlspecialchars($filePath));
    }

    // Prevent directory traversal
    $realBasePath = realpath($baseUploadDir) . DIRECTORY_SEPARATOR;
    $realFilePath = realpath($filePath);
    
    if ($realFilePath === false || strpos($realFilePath, $realBasePath) !== 0) {
        header('HTTP/1.0 403 Forbidden');
        die('Access denied - invalid file path.');
    }

    // Get file info
    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $safeFilename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '', $file['title']) . '.' . $fileExtension;

    // Set appropriate headers
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $safeFilename . '"');
    header('Content-Length: ' . filesize($filePath));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Clear output buffer
    if (ob_get_level()) {
        ob_clean();
        flush();
    }
    
    // Read the file
    readfile($filePath);
    exit;

} catch (PDOException $e) {
    header('HTTP/1.0 500 Internal Server Error');
    die('Database error: ' . $e->getMessage());
} catch (Exception $e) {
    header('HTTP/1.0 500 Internal Server Error');
    die('Error: ' . $e->getMessage());
}