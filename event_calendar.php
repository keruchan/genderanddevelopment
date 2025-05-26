<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

function fetchEvents($conn, $month, $year) {
    $startDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
    $endDate = date("Y-m-t", strtotime($startDate));
    $sql = "SELECT * FROM events WHERE event_date BETWEEN '$startDate' AND '$endDate'";
    $result = $conn->query($sql);
    $events = [];
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
    return $events;
}

if (isset($_GET['month']) && isset($_GET['year'])) {
    $month = intval($_GET['month']);
    $year = intval($_GET['year']);

    if ($month < 1) {
        $month = 12;
        $year--;
    } elseif ($month > 12) {
        $month = 1;
        $year++;
    }
} else {
    $month = date('m');
    $year = date('Y');
}

$events = fetchEvents($conn, $month, $year);

function formatTime12($time) {
    if (!$time) return '';
    return date("g:i A", strtotime($time));
}

function buildCalendar($month, $year, $events) {
    $daysOfWeek = array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
    $firstDayOfMonth = mktime(0, 0, 0, $month, 1, $year);
    $numberDays = date('t', $firstDayOfMonth);
    $dateComponents = getdate($firstDayOfMonth);
    $monthName = $dateComponents['month'];
    $dayOfWeek = $dateComponents['wday'];

    $prevMonth = $month - 1;
    $nextMonth = $month + 1;
    $prevYear = $year;
    $nextYear = $year;

    if ($prevMonth < 1) {
        $prevMonth = 12;
        $prevYear--;
    }
    if ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }

    $calendar = "<div style='display:flex;gap:2rem;max-width:1400px;margin:2rem auto;font-family:sans-serif;'>";

    // Event Info Panel
    $calendar .= "<div id='eventInfoPanel' style='flex:1;background:#f5f5f5;padding:20px;border-radius:10px;border:1px solid #ddd;'>"
              . "<h2 style='margin-top:0;'>Event Details</h2>"
              . "<p><strong>Title:</strong> <span id='eventTitle'></span></p>"
              . "<p><strong>Description:</strong><br><span id='eventDescription'></span></p>"
              . "<img id='eventImage' src='' style='width:100%;margin-top:10px;border-radius:6px;display:none;' />"
              . "<p><strong>Start:</strong> <span id='eventStartTime'></span></p>"
              . "<p><strong>End:</strong> <span id='eventEndTime'></span></p>"
              . "<p><strong>Attendees:</strong> <span id='eventAttendees'></span></p>"
              . "</div>";

    // Calendar table
    $calendar .= "<div style='flex:3;'>";
    $calendar .= "<h2 style='text-align:center;margin-bottom:1rem;'>"
        ."<a href='?month=$prevMonth&year=$prevYear' style='margin-right:30px;color:#4285F4;text-decoration:none;'>&laquo;</a>"
        ."$monthName $year"
        ."<a href='?month=$nextMonth&year=$nextYear' style='margin-left:30px;color:#4285F4;text-decoration:none;'>&raquo;</a>"
        ."</h2>";
    $calendar .= "<table style='width:100%;table-layout:fixed;border-collapse:collapse;border:1px solid #ddd;'>";
    $calendar .= "<thead><tr style='background:#f0f8ff;'>";

    foreach ($daysOfWeek as $day) {
        $calendar .= "<th style='padding:10px;border:1px solid #ccc;'>$day</th>";
    }

    $calendar .= "</tr></thead><tbody><tr>";

    // Blank cells before the first day
    if ($dayOfWeek > 0) {
        for ($k = 0; $k < $dayOfWeek; $k++) {
            $calendar .= "<td style='padding:10px;height:120px;border:1px solid #eee;background:#fafafa;'></td>";
        }
    }

    $currentDay = 1;
    $currentWeekDay = $dayOfWeek;

    while ($currentDay <= $numberDays) {
        if ($currentWeekDay == 7) {
            $calendar .= "</tr><tr>";
            $currentWeekDay = 0;
        }

        $currentDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($currentDay, 2, '0', STR_PAD_LEFT);
        $calendar .= "<td style='vertical-align:top;padding:10px;height:120px;border:1px solid #ddd;background:#fff;overflow:hidden;'>";
        $calendar .= "<div style='font-weight:bold;'>$currentDay</div>";

        foreach ($events as $event) {
            if ($event['event_date'] == $currentDate) {
                $calendar .= "<div onclick=\"showEventInfo("
                    . htmlspecialchars(json_encode($event['title']), ENT_QUOTES)
                    . ", "
                    . htmlspecialchars(json_encode($event['description']), ENT_QUOTES)
                    . ", "
                    . htmlspecialchars(json_encode($event['attachment_path'] ? ('attachments/' . $event['attachment_path']) : ''), ENT_QUOTES)
                    . ", "
                    . htmlspecialchars(json_encode(formatTime12($event['start_time'])), ENT_QUOTES)
                    . ", "
                    . htmlspecialchars(json_encode(formatTime12($event['end_time'])), ENT_QUOTES)
                    . ", "
                    . htmlspecialchars(json_encode($event['attendees']), ENT_QUOTES)
                    . ")\" style='margin-top:5px;padding:6px 8px;background:#4285F4;color:white;border-radius:5px;cursor:pointer;font-size:0.8rem;line-height:1.2em;max-height:80px;overflow-y:auto;white-space:pre-line;'>"
                    . htmlspecialchars($event['title'])
                    . "</div>";
            }
        }

        $calendar .= "</td>";
        $currentDay++;
        $currentWeekDay++;
    }

    // Blank cells after the last day
    if ($currentWeekDay != 7) {
        $remainingDays = 7 - $currentWeekDay;
        for ($l = 0; $l < $remainingDays; $l++) {
            $calendar .= "<td style='padding:10px;height:120px;border:1px solid #eee;background:#fafafa;'></td>";
        }
    }

    $calendar .= "</tr></tbody></table></div></div>";
    return $calendar;
}

echo buildCalendar($month, $year, $events);
?>

<script>
function showEventInfo(title, description, image, startTime, endTime, attendees) {
    document.getElementById('eventTitle').innerText = title;
    document.getElementById('eventDescription').innerText = description;
    if (image && image !== "attachments/") {
        document.getElementById('eventImage').src = image;
        document.getElementById('eventImage').style.display = 'block';
    } else {
        document.getElementById('eventImage').style.display = 'none';
    }
    document.getElementById('eventStartTime').innerText = startTime;
    document.getElementById('eventEndTime').innerText = endTime;
    document.getElementById('eventAttendees').innerText = attendees;
}
</script>

<?php include_once('temp/footeradmin.php'); ?>