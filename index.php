<?php
require_once 'config.php';
requireAuth();

$conn = getDBConnection();
$message = '';
$messageType = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $entry_date = $_POST['entry_date'] ?? date('Y-m-d');
    $worked_hours = floatval($_POST['worked_hours'] ?? 0);
    $notes = sanitizeInput($_POST['notes'] ?? '');
    
    // Validate date
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $entry_date)) {
        $message = 'Invalid date format';
        $messageType = 'error';
    } else {
        // Check if entry exists for this date
        $stmt = $conn->prepare("SELECT id FROM daily_entries WHERE entry_date = ?");
        $stmt->bind_param("s", $entry_date);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing entry
            $entry = $result->fetch_assoc();
            $entry_id = $entry['id'];
            $stmt->close();
            
            $stmt = $conn->prepare("UPDATE daily_entries SET worked_hours = ?, notes = ? WHERE id = ?");
            $stmt->bind_param("dsi", $worked_hours, $notes, $entry_id);
            $stmt->execute();
            $stmt->close();
        } else {
            // Create new entry
            $stmt = $conn->prepare("INSERT INTO daily_entries (entry_date, worked_hours, notes) VALUES (?, ?, ?)");
            $stmt->bind_param("sds", $entry_date, $worked_hours, $notes);
            $stmt->execute();
            $entry_id = $stmt->insert_id;
            $stmt->close();
        }
        
        // Handle file uploads
        require_once 'upload_handler.php';
        $uploadResult = handleUploads($entry_id, $entry_date);
        
        if ($uploadResult['success']) {
            $message = 'Entry saved successfully!';
            $messageType = 'success';
            if (!empty($uploadResult['errors'])) {
                $message .= ' ' . implode(', ', $uploadResult['errors']);
            }
        } else {
            $message = 'Entry saved but some uploads failed: ' . implode(', ', $uploadResult['errors']);
            $messageType = 'warning';
        }
    }
}

// Get all entry dates for calendar
$stmt = $conn->query("SELECT DISTINCT entry_date FROM daily_entries ORDER BY entry_date DESC");
$entry_dates = [];
while ($row = $stmt->fetch_assoc()) {
    $entry_dates[] = $row['entry_date'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Work Management - Daily Entry</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Daily Work Management</h1>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="view.php" class="btn btn-secondary">View Entries</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            </div>
        </header>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>"><?php echo $message; ?></div>
        <?php endif; ?>

        <main>
            <div class="entry-form-container">
                <h2>Add/Edit Daily Entry</h2>
                <form method="POST" enctype="multipart/form-data" id="entryForm">
                    <div class="form-group">
                        <label for="entry_date">Date</label>
                        <input type="date" id="entry_date" name="entry_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="worked_hours">Worked Hours</label>
                        <input type="number" id="worked_hours" name="worked_hours" step="0.25" min="0" max="24" value="0" required>
                    </div>

                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" rows="5" placeholder="Enter your notes here..."></textarea>
                    </div>

                    <div class="form-group">
                        <label for="images">Upload Images</label>
                        <input type="file" id="images" name="images[]" accept="image/*" multiple>
                        <small>You can select multiple images (JPEG, PNG, GIF, WebP)</small>
                    </div>

                    <div class="form-group">
                        <label for="videos">Upload Videos</label>
                        <input type="file" id="videos" name="videos[]" accept="video/*" multiple>
                        <small>You can select multiple videos (MP4, WebM, OGG, QuickTime)</small>
                    </div>

                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </form>
            </div>

            <div class="calendar-container">
                <h2>Entry Calendar</h2>
                <div class="calendar" id="calendar">
                    <!-- Calendar will be populated by JavaScript -->
                </div>
            </div>
        </main>
    </div>

    <script src="script.js"></script>
    <script>
        // Pass entry dates to JavaScript
        const entryDates = <?php echo json_encode($entry_dates); ?>;
        if (typeof initCalendar === 'function') {
            initCalendar(entryDates);
        }
    </script>
</body>
</html>

