<?php
/**
 * Shared upload helper for admin modules (partners, members, media, banners, branding)
 * Files are stored in /uploads/{subdir}/ — the same layout as vnchips_old.
 */

const ADMIN_IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'ico'];

/**
 * Move one uploaded file into uploads/{subdir}/.
 * $file is one entry of $_FILES (or a normalized entry of a multi-upload).
 * Returns ['path' => 'uploads/...', 'name' => ..., 'size' => bytes] or ['error' => message].
 */
function adminStoreUpload(array $file, $subdir, $prefix, array $allowedExts = ADMIN_IMAGE_EXTS) {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['error' => 'Upload failed (error code ' . (int)($file['error'] ?? -1) . ').'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExts, true)) {
        return ['error' => 'Invalid file type ".' . $ext . '". Allowed: ' . strtoupper(implode(', ', $allowedExts)) . '.'];
    }

    $dir = __DIR__ . '/../../uploads/' . $subdir . '/';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return ['error' => 'Cannot create upload folder uploads/' . $subdir . '.'];
    }

    $fileName = $prefix . '_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 6) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], $dir . $fileName)) {
        return ['error' => 'Failed to save uploaded file on server.'];
    }

    return ['path' => 'uploads/' . $subdir . '/' . $fileName, 'name' => $fileName, 'size' => (int)$file['size']];
}

/**
 * Handle an optional single-file field: returns the new path, the $current path when nothing
 * was uploaded, and appends to $errors on failure.
 */
function adminUploadField($field, $subdir, $prefix, $current, array &$errors) {
    if (empty($_FILES[$field]['name'])) {
        return $current;
    }

    $result = adminStoreUpload($_FILES[$field], $subdir, $prefix);
    if (isset($result['error'])) {
        $errors[] = $result['error'];
        return $current;
    }
    return $result['path'];
}

/**
 * Human readable file size (same format as vnchips_old media.file_size)
 */
function adminFormatBytes($bytes) {
    return $bytes >= 1048576 ? round($bytes / 1048576, 2) . ' MB' : round($bytes / 1024, 2) . ' KB';
}
