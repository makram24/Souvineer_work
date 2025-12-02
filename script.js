// Calendar initialization
function initCalendar(entryDates) {
    const calendar = document.getElementById('calendar');
    if (!calendar) return;

    // Get current date
    const today = new Date();
    const currentMonth = today.getMonth();
    const currentYear = today.getFullYear();

    // Get first day of month and number of days
    const firstDay = new Date(currentYear, currentMonth, 1);
    const lastDay = new Date(currentYear, currentMonth + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();

    // Clear calendar
    calendar.innerHTML = '';

    // Add empty cells for days before month starts
    for (let i = 0; i < startingDayOfWeek; i++) {
        const emptyDay = document.createElement('div');
        emptyDay.className = 'calendar-day empty';
        calendar.appendChild(emptyDay);
    }

    // Add days of the month
    for (let day = 1; day <= daysInMonth; day++) {
        const dayElement = document.createElement('div');
        dayElement.className = 'calendar-day';
        
        const dateStr = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        
        // Check if this date has an entry
        if (entryDates.includes(dateStr)) {
            dayElement.classList.add('has-entry');
        }

        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;

        const dayName = document.createElement('div');
        dayName.className = 'day-name';
        const dayDate = new Date(currentYear, currentMonth, day);
        dayName.textContent = dayDate.toLocaleDateString('en-US', { weekday: 'short' });

        dayElement.appendChild(dayNumber);
        dayElement.appendChild(dayName);

        // Add click handler
        dayElement.addEventListener('click', function() {
            window.location.href = `view.php?date=${dateStr}`;
        });

        calendar.appendChild(dayElement);
    }
}

// Form validation
document.addEventListener('DOMContentLoaded', function() {
    const entryForm = document.getElementById('entryForm');
    if (entryForm) {
        entryForm.addEventListener('submit', function(e) {
            const workedHours = parseFloat(document.getElementById('worked_hours').value);
            if (workedHours < 0 || workedHours > 24) {
                e.preventDefault();
                alert('Worked hours must be between 0 and 24');
                return false;
            }

            // Check file sizes
            const imageInput = document.getElementById('images');
            const videoInput = document.getElementById('videos');
            const maxSize = 100 * 1024 * 1024; // 100MB

            if (imageInput && imageInput.files.length > 0) {
                for (let i = 0; i < imageInput.files.length; i++) {
                    if (imageInput.files[i].size > maxSize) {
                        e.preventDefault();
                        alert(`Image "${imageInput.files[i].name}" exceeds maximum file size (100MB)`);
                        return false;
                    }
                }
            }

            if (videoInput && videoInput.files.length > 0) {
                for (let i = 0; i < videoInput.files.length; i++) {
                    if (videoInput.files[i].size > maxSize) {
                        e.preventDefault();
                        alert(`Video "${videoInput.files[i].name}" exceeds maximum file size (100MB)`);
                        return false;
                    }
                }
            }
        });
    }

    // Auto-fill date picker with selected calendar day
    const urlParams = new URLSearchParams(window.location.search);
    const dateParam = urlParams.get('date');
    if (dateParam && document.getElementById('entry_date')) {
        document.getElementById('entry_date').value = dateParam;
    }
});

// File input preview (optional enhancement)
function previewFiles(input, previewContainer) {
    if (input.files && input.files.length > 0) {
        // Could add file preview functionality here
    }
}

