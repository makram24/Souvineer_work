<?php
require_once 'config.php';

// Get month and year from query parameters
$selected_month = $_GET['month'] ?? date('Y-m');
$selected_date = $_GET['date'] ?? null;

// Validate month format
if (!preg_match('/^\d{4}-\d{2}$/', $selected_month)) {
    $selected_month = date('Y-m');
}

// Validate date format if provided
if ($selected_date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $selected_date)) {
    $selected_date = null;
}

$conn = getDBConnection();

// Get all entries for the selected month
$month_start = $selected_month . '-01';
$month_end = date('Y-m-t', strtotime($month_start));

$stmt = $conn->prepare("SELECT * FROM daily_entries WHERE entry_date >= ? AND entry_date <= ? ORDER BY entry_date ASC");
$stmt->bind_param("ss", $month_start, $month_end);
$stmt->execute();
$result = $stmt->get_result();

$entries = [];
$monthly_hours = 0;
while ($row = $result->fetch_assoc()) {
    $entries[$row['entry_date']] = $row;
    $monthly_hours += floatval($row['worked_hours']);
}
$stmt->close();

// Get all entry dates for calendar
$stmt = $conn->query("SELECT DISTINCT entry_date FROM daily_entries ORDER BY entry_date DESC");
$all_dates = [];
while ($row = $stmt->fetch_assoc()) {
    $all_dates[] = $row['entry_date'];
}
$stmt->close();

// If a specific date is selected, get its details
$selected_entry = null;
$selected_images = [];
$selected_videos = [];

if ($selected_date && isset($entries[$selected_date])) {
    $selected_entry = $entries[$selected_date];
    $entry_id = $selected_entry['id'];
    
    // Get images
    $stmt = $conn->prepare("SELECT * FROM entry_images WHERE entry_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_images[] = $row;
    }
    $stmt->close();
    
    // Get videos
    $stmt = $conn->prepare("SELECT * FROM entry_videos WHERE entry_id = ? ORDER BY uploaded_at DESC");
    $stmt->bind_param("i", $entry_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $selected_videos[] = $row;
    }
    $stmt->close();
}

// Get available months for filter
$stmt = $conn->query("SELECT DISTINCT DATE_FORMAT(entry_date, '%Y-%m') as month FROM daily_entries ORDER BY month DESC");
$available_months = [];
while ($row = $stmt->fetch_assoc()) {
    $available_months[] = $row['month'];
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Work Portfolio - <?php echo date('F Y', strtotime($month_start)); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <style>
        .public-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 24px;
        }
        
        .public-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 32px 40px;
            border-radius: var(--radius-lg);
            margin-bottom: 32px;
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }
        
        .public-header h1 {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-size: 36px;
            font-weight: 700;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
        }
        
        .public-header p {
            color: var(--gray);
            font-size: 16px;
        }
        
        .month-filter {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: var(--radius-lg);
            margin-bottom: 24px;
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.3);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
        }
        
        .month-filter label {
            font-weight: 600;
            color: var(--dark);
            font-size: 14px;
        }
        
        .month-filter select {
            padding: 12px 16px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            font-size: 15px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .month-filter select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        
        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 24px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
        }
        
        .stat-card .stat-value {
            font-size: 32px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
        }
        
        .stat-card .stat-label {
            color: var(--gray);
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .public-calendar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 32px;
        }
        
        .public-calendar h2 {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 24px;
            font-size: 26px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .calendar-header {
            text-align: center;
            font-weight: 600;
            color: var(--gray);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 12px;
        }
        
        .calendar-day-public {
            padding: 16px 8px;
            border: 2px solid var(--gray-light);
            border-radius: var(--radius);
            text-align: center;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: white;
            position: relative;
            min-height: 80px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .calendar-day-public:hover {
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }
        
        .calendar-day-public.has-entry {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-color: var(--primary);
            box-shadow: var(--shadow);
        }
        
        .calendar-day-public.has-entry:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: var(--shadow-lg);
        }
        
        .calendar-day-public.other-month {
            opacity: 0.3;
            cursor: default;
        }
        
        .calendar-day-public .day-number {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 4px;
        }
        
        .calendar-day-public .day-hours {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 4px;
        }
        
        .day-details {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-xl);
            border: 1px solid rgba(255, 255, 255, 0.3);
            margin-bottom: 32px;
        }
        
        .day-details h3 {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 24px;
            font-size: 28px;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        @media (max-width: 768px) {
            .public-container {
                padding: 16px;
            }
            
            .public-header {
                padding: 24px 20px;
            }
            
            .public-header h1 {
                font-size: 28px;
            }
            
            .month-filter {
                flex-direction: column;
                align-items: stretch;
            }
            
            .month-filter select {
                width: 100%;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .calendar-grid {
                gap: 4px;
            }
            
            .calendar-day-public {
                padding: 12px 4px;
                min-height: 60px;
            }
            
            .calendar-day-public .day-number {
                font-size: 14px;
            }
            
            .calendar-day-public .day-hours {
                font-size: 10px;
            }
            
            .day-details {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="public-container">
        <div class="public-header">
            <h1>📊 Work Portfolio</h1>
            <p>View monthly work entries, hours, and progress</p>
        </div>

        <div class="month-filter">
            <label for="month_select">📅 Select Month:</label>
            <select id="month_select" onchange="changeMonth()">
                <?php foreach ($available_months as $month): ?>
                    <option value="<?php echo $month; ?>" <?php echo $month === $selected_month ? 'selected' : ''; ?>>
                        <?php echo date('F Y', strtotime($month . '-01')); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button onclick="previousMonth()" class="btn btn-secondary">← Previous</button>
                <button onclick="nextMonth()" class="btn btn-secondary">Next →</button>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-card">
                <div class="stat-value"><?php echo count($entries); ?></div>
                <div class="stat-label">Days with Entries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo number_format($monthly_hours, 2); ?></div>
                <div class="stat-label">Total Hours</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($entries) > 0 ? number_format($monthly_hours / count($entries), 2) : '0.00'; ?></div>
                <div class="stat-label">Avg Hours/Day</div>
            </div>
        </div>

        <div class="public-calendar">
            <h2>📆 Calendar View - <?php echo date('F Y', strtotime($month_start)); ?></h2>
            <div class="calendar-grid">
                <?php
                // Calendar headers
                $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
                foreach ($weekdays as $day) {
                    echo '<div class="calendar-header">' . $day . '</div>';
                }
                
                // Get first day of month and number of days
                $first_day = new DateTime($month_start);
                $first_day_of_week = (int)$first_day->format('w');
                $days_in_month = (int)$first_day->format('t');
                
                // Previous month days
                $prev_month = clone $first_day;
                $prev_month->modify('-1 month');
                $prev_month_days = (int)$prev_month->format('t');
                
                for ($i = $first_day_of_week - 1; $i >= 0; $i--) {
                    $day_num = $prev_month_days - $i;
                    $date_str = $prev_month->format('Y-m') . '-' . str_pad($day_num, 2, '0', STR_PAD_LEFT);
                    echo '<div class="calendar-day-public other-month">';
                    echo '<div class="day-number">' . $day_num . '</div>';
                    echo '</div>';
                }
                
                // Current month days
                for ($day = 1; $day <= $days_in_month; $day++) {
                    $date_str = $selected_month . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                    $has_entry = isset($entries[$date_str]);
                    $entry = $has_entry ? $entries[$date_str] : null;
                    $hours = $entry ? number_format($entry['worked_hours'], 1) : '';
                    
                    $classes = 'calendar-day-public';
                    if ($has_entry) {
                        $classes .= ' has-entry';
                    }
                    if ($selected_date === $date_str) {
                        $classes .= ' selected';
                    }
                    
                    echo '<div class="' . $classes . '" onclick="selectDate(\'' . $date_str . '\')">';
                    echo '<div class="day-number">' . $day . '</div>';
                    if ($has_entry && $hours > 0) {
                        echo '<div class="day-hours">' . $hours . 'h</div>';
                    }
                    echo '</div>';
                }
                
                // Next month days to fill the grid
                $total_cells = $first_day_of_week + $days_in_month;
                $remaining_cells = 42 - $total_cells; // 6 rows * 7 days
                if ($remaining_cells > 0) {
                    for ($day = 1; $day <= $remaining_cells; $day++) {
                        echo '<div class="calendar-day-public other-month">';
                        echo '<div class="day-number">' . $day . '</div>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>

        <?php if ($selected_entry): ?>
            <div class="day-details">
                <h3>📝 <?php echo formatDate($selected_date); ?></h3>
                
                <div class="entry-info">
                    <div class="info-item">
                        <strong>⏰ Worked Hours:</strong>
                        <span><?php echo number_format($selected_entry['worked_hours'], 2); ?> hours</span>
                    </div>

                    <?php if (!empty($selected_entry['notes'])): ?>
                        <div class="info-item">
                            <strong>📝 Notes:</strong>
                            <div class="notes-content"><?php echo nl2br(htmlspecialchars($selected_entry['notes'])); ?></div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($selected_images)): ?>
                    <div class="media-section">
                        <h3>🖼️ Images (<?php echo count($selected_images); ?>)</h3>
                        <div class="image-gallery">
                            <?php foreach ($selected_images as $image): ?>
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

                <?php if (!empty($selected_videos)): ?>
                    <div class="media-section">
                        <h3>🎥 Videos (<?php echo count($selected_videos); ?>)</h3>
                        <div class="video-gallery">
                            <?php foreach ($selected_videos as $video): ?>
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
            </div>
        <?php elseif ($selected_date): ?>
            <div class="day-details">
                <div class="no-content">
                    <p>No entry found for <?php echo formatDate($selected_date); ?></p>
                </div>
            </div>
        <?php endif; ?>

        <?php if (empty($entries)): ?>
            <div class="day-details">
                <div class="no-content">
                    <p>No entries found for <?php echo date('F Y', strtotime($month_start)); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="modal" onclick="closeImageModal()">
        <span class="modal-close">&times;</span>
        <img class="modal-content" id="modalImage">
        <div class="modal-caption" id="modalCaption"></div>
    </div>

    <script>
        function changeMonth() {
            const month = document.getElementById('month_select').value;
            window.location.href = '?month=' + month;
        }

        function previousMonth() {
            const currentMonth = document.getElementById('month_select').value;
            const date = new Date(currentMonth + '-01');
            date.setMonth(date.getMonth() - 1);
            const newMonth = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            window.location.href = '?month=' + newMonth;
        }

        function nextMonth() {
            const currentMonth = document.getElementById('month_select').value;
            const date = new Date(currentMonth + '-01');
            date.setMonth(date.getMonth() + 1);
            const newMonth = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0');
            window.location.href = '?month=' + newMonth;
        }

        function selectDate(date) {
            const month = document.getElementById('month_select').value;
            window.location.href = '?month=' + month + '&date=' + date;
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

        // Scroll to selected date details if present
        <?php if ($selected_date): ?>
        window.addEventListener('load', function() {
            const dayDetails = document.querySelector('.day-details');
            if (dayDetails) {
                dayDetails.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>

