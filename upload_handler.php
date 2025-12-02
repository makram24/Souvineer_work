<?php
require_once 'config.php';

function handleUploads($entry_id, $entry_date) {
    $conn = getDBConnection();
    $errors = [];
    $success = true;
    
    // Handle image uploads
    if (isset($_FILES['images']) && !empty($_FILES['images']['name'][0])) {
        $imageCount = count($_FILES['images']['name']);
        
        for ($i = 0; $i < $imageCount; $i++) {
            if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];
                
                // Validate file type
                if (!in_array($file['type'], ALLOWED_IMAGE_TYPES)) {
                    $errors[] = "Image '{$file['name']}' has invalid type";
                    continue;
                }
                
                // Validate file size
                if ($file['size'] > MAX_FILE_SIZE) {
                    $errors[] = "Image '{$file['name']}' exceeds maximum size";
                    continue;
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('img_', true) . '_' . time() . '.' . $extension;
                $filepath = IMAGE_UPLOAD_DIR . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Save to database
                    $stmt = $conn->prepare("INSERT INTO entry_images (entry_id, image_path, image_name) VALUES (?, ?, ?)");
                    $relative_path = 'uploads/images/' . $filename;
                    $stmt->bind_param("iss", $entry_id, $relative_path, $file['name']);
                    if (!$stmt->execute()) {
                        $errors[] = "Failed to save image '{$file['name']}' to database";
                        @unlink($filepath); // Delete file if DB insert fails
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Failed to upload image '{$file['name']}'";
                    $success = false;
                }
            } else {
                $errors[] = "Error uploading image: " . getUploadErrorMessage($_FILES['images']['error'][$i]);
                $success = false;
            }
        }
    }
    
    // Handle video uploads
    if (isset($_FILES['videos']) && !empty($_FILES['videos']['name'][0])) {
        $videoCount = count($_FILES['videos']['name']);
        
        for ($i = 0; $i < $videoCount; $i++) {
            if ($_FILES['videos']['error'][$i] === UPLOAD_ERR_OK) {
                $file = [
                    'name' => $_FILES['videos']['name'][$i],
                    'type' => $_FILES['videos']['type'][$i],
                    'tmp_name' => $_FILES['videos']['tmp_name'][$i],
                    'size' => $_FILES['videos']['size'][$i]
                ];
                
                // Validate file type
                if (!in_array($file['type'], ALLOWED_VIDEO_TYPES)) {
                    $errors[] = "Video '{$file['name']}' has invalid type";
                    continue;
                }
                
                // Validate file size
                if ($file['size'] > MAX_FILE_SIZE) {
                    $errors[] = "Video '{$file['name']}' exceeds maximum size";
                    continue;
                }
                
                // Generate unique filename
                $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = uniqid('vid_', true) . '_' . time() . '.' . $extension;
                $filepath = VIDEO_UPLOAD_DIR . $filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $filepath)) {
                    // Save to database
                    $stmt = $conn->prepare("INSERT INTO entry_videos (entry_id, video_path, video_name) VALUES (?, ?, ?)");
                    $relative_path = 'uploads/videos/' . $filename;
                    $stmt->bind_param("iss", $entry_id, $relative_path, $file['name']);
                    if (!$stmt->execute()) {
                        $errors[] = "Failed to save video '{$file['name']}' to database";
                        @unlink($filepath); // Delete file if DB insert fails
                    }
                    $stmt->close();
                } else {
                    $errors[] = "Failed to upload video '{$file['name']}'";
                    $success = false;
                }
            } else {
                $errors[] = "Error uploading video: " . getUploadErrorMessage($_FILES['videos']['error'][$i]);
                $success = false;
            }
        }
    }
    
    return [
        'success' => $success && empty($errors),
        'errors' => $errors
    ];
}

function getUploadErrorMessage($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return 'File exceeds maximum size';
        case UPLOAD_ERR_PARTIAL:
            return 'File was only partially uploaded';
        case UPLOAD_ERR_NO_FILE:
            return 'No file was uploaded';
        case UPLOAD_ERR_NO_TMP_DIR:
            return 'Missing temporary folder';
        case UPLOAD_ERR_CANT_WRITE:
            return 'Failed to write file to disk';
        case UPLOAD_ERR_EXTENSION:
            return 'File upload stopped by extension';
        default:
            return 'Unknown upload error';
    }
}
?>

