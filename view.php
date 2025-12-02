<?php
require_once 'config.php';
requireAuth();

$conn = getDBConnection();
$selected_date = $_GET['date'] ?? date('Y-m-d');

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = date('Y-m-d');
}

// Get entry for selected date
$stmt = $conn->prepare("SELECT * FROM daily_entries WHERE entry_date = ?");
$stmt->bind_param("s", $selected_date);
$stmt->execute();
$result = $stmt->get_result();
$entry = $result->fetch_assoc();
$stmt->close();

$entry_id = $entry['id'] ?? null;

// Get images for this entry
$images = [];
if ($entry_id) {
    $stmt = $conn->prepare("SELECT * FROM entry_images WHERE entry_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $images[] = $row;
    }
    $stmt->close();
}

// Get videos for this entry
$videos = [];
if ($entry_id) {
    $stmt = $conn->prepare("SELECT * FROM entry_videos WHERE entry_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $videos[] = $row;
    }
    $stmt->close();
}

// Get all entry dates for navigation
$stmt = $conn->query("SELECT DISTINCT entry_date FROM daily_entries ORDER BY entry_date DESC");
$all_dates = [];
while ($row = $stmt->fetch_assoc()) {
    $all_dates[] = $row['entry_date'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>View Entry - <?php echo formatDate($selected_date); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>👁️ View Daily Entry</h1>
            <div class="user-info">
                <a href="index.php" class="btn btn-secondary">⬅️ Back to Entry</a>
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </header>

        <main>
            <div class="date-selector">
                <label for="date_picker">📅 Select Date:</label>
                <input type="date" id="date_picker" value="<?php echo $selected_date; ?>">
                <button onclick="goToDate()" class="btn btn-primary">🔍 Go</button>
            </div>

            <?php if ($entry): ?>
                <div class="entry-details">
                    <div class="entry-header">
                        <h2><?php echo formatDate($selected_date); ?></h2>
                    </div>

                    <div class="entry-info">
                        <div class="info-item">
                            <strong>⏰ Worked Hours:</strong>
                            <span><?php echo number_format($entry['worked_hours'], 2); ?> hours</span>
                        </div>

                        <?php if (!empty($entry['notes'])): ?>
                            <div class="info-item">
                                <strong>📝 Notes:</strong>
                                <div class="notes-content"><?php echo nl2br(htmlspecialchars($entry['notes'])); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($images)): ?>
                        <div class="media-section">
                            <h3>🖼️ Images (<?php echo count($images); ?>)</h3>
                            <div class="image-gallery">
                                <?php foreach ($images as $image): ?>
                                    <div class="image-item">
                                        <img src="<?php echo htmlspecialchars($image['image_path']); ?>" 
                                             alt="<?php echo htmlspecialchars($image['image_name']); ?>"
                                             onclick="openImageModal(this.src, this.alt)">
                                        <p class="image-name"><?php echo htmlspecialchars($image['image_name']); ?></p>
                                        <p class="image-date"><?php echo date('M j, Y g:i A', strtotime($image['uploaded_at'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($videos)): ?>
                        <div class="media-section">
                            <h3>🎥 Videos (<?php echo count($videos); ?>)</h3>
                            <div class="video-gallery">
                                <?php foreach ($videos as $video): ?>
                                    <div class="video-item">
                                        <video controls>
                                            <source src="<?php echo htmlspecialchars($video['video_path']); ?>" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <p class="video-name"><?php echo htmlspecialchars($video['video_name']); ?></p>
                                        <p class="video-date"><?php echo date('M j, Y g:i A', strtotime($video['uploaded_at'])); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (empty($images) && empty($videos) && empty($entry['notes']) && $entry['worked_hours'] == 0): ?>
                        <div class="no-content">
                            <p>No content available for this date.</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="no-entry">
                    <p>No entry found for <?php echo formatDate($selected_date); ?></p>
                    <a href="index.php?date=<?php echo $selected_date; ?>" class="btn btn-primary">Create Entry</a>
                </div>
            <?php endif; ?>

            <div class="date-navigation">
                <h3>📅 Available Dates</h3>
                <div class="date-list">
                    <?php foreach ($all_dates as $date): ?>
                        <a href="?date=<?php echo $date; ?>" 
                           class="date-link <?php echo $date === $selected_date ? 'active' : ''; ?>">
                            <?php echo formatDate($date); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
        <div class="modal-caption" id="modalCaption"></div>
    </div>

    <script src="script.js"></script>
    <script>
        function goToDate() {
            const date = document.getElementById('date_picker').value;
            if (date) {
                window.location.href = '?date=' + date;
            }
        }

        function openImageModal(src, alt) {
            const modal = document.getElementById('imageModal');
            const modalImg = document.getElementById('modalImage');
            const caption = document.getElementById('modalCaption');
            modal.style.display = 'block';
            modalImg.src = src;
            caption.textContent = alt;
        }

        function closeImageModal() {
            document.getElementById('imageModal').style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
            }
        });
    </script>
</body>
</html>

