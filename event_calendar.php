<?php
include_once('temp/header.php');
include_once('temp/navigationold.php');
require 'connecting/connect.php';

function fetchEvents($conn, $month, $year) {
    $startDate = "$year-$month-01";
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
        $nextMonth = 1;
        $nextYear++;
    }
} else {
    $month = date('m');
    $year = date('Y');
}

$events = fetchEvents($conn, $month, $year);

function buildCalendar($month, $year, $events) {
    $daysOfWeek = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');
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
    } elseif ($nextMonth > 12) {
        $nextMonth = 1;
        $nextYear++;
    }

    $calendar = "<table class='calendar' style='width: 100%; border-collapse: collapse;'>";
    $calendar .= "<caption style='text-align: center; font-size: 24px; margin: 10px 0;'>
                    <a href='?month=$prevMonth&year=$prevYear' style='margin-right: 20px;'>&lt;&lt;</a>
                    $monthName $year
                    <a href='?month=$nextMonth&year=$nextYear' style='margin-left: 20px;'>&gt;&gt;</a>
                  </caption>";
    $calendar .= "<tr>";

    foreach ($daysOfWeek as $day) {
        $calendar .= "<th class='header' style='padding: 10px; border: 1px solid #ddd; background-color: #f2f2f2;'>$day</th>";
    }

    $calendar .= "</tr><tr>";

    if ($dayOfWeek > 0) {
        for ($k = 0; $k < $dayOfWeek; $k++) {
            $calendar .= "<td class='empty' style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'></td>";
        }
    }

    $currentDay = 1;

    while ($currentDay <= $numberDays) {
        if ($dayOfWeek == 7) {
            $dayOfWeek = 0;
            $calendar .= "</tr><tr>";
        }

        $currentDate = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-" . str_pad($currentDay, 2, '0', STR_PAD_LEFT);
        $calendar .= "<td class='day' rel='$currentDate' style='padding: 10px; border: 1px solid #ddd; background-color: #fff;'>$currentDay";

        foreach ($events as $event) {
            if ($event['event_date'] == $currentDate) {
                $calendar .= "<div class='event' style='background-color: #4285F4; color: #fff; padding: 5px; margin: 5px 0; border-radius: 4px; cursor: pointer;' onclick='showEventModal(\"{$event['title']}\", \"{$event['description']}\", \"attachments/{$event['attachment_path']}\", \"{$event['start_time']}\", \"{$event['end_time']}\")'>{$event['title']}</div>";
            }
        }

        $calendar .= "</td>";
        $currentDay++;
        $dayOfWeek++;
    }

    if ($dayOfWeek != 7) {
        $remainingDays = 7 - $dayOfWeek;
        for ($l = 0; $l < $remainingDays; $l++) {
            $calendar .= "<td class='empty' style='padding: 10px; border: 1px solid #ddd; background-color: #f9f9f9;'></td>";
        }
    }

    $calendar .= "</tr>";
    $calendar .= "</table>";
    return $calendar;
}

echo buildCalendar($month, $year, $events);
?>

<!-- Event Modal -->
<div id="eventModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 20px; box-shadow: 0 0 10px rgba(0,0,0,0.5); z-index: 1000;">
    <h2 id="eventTitle"></h2>
    <p id="eventDescription"></p>
    <img id="eventImage" src="" style="width: 100%; height: auto; object-fit: contain; margin-top: 10px;" />
    <p><strong>Start:</strong> <span id="eventStartTime"></span></p>
    <p><strong>End:</strong> <span id="eventEndTime"></span></p>
    <button onclick="hideEventModal()" style="background-color: #4285F4; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
</div>
<div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;" onclick="hideEventModal()"></div>

<script>
function showEventModal(title, description, image, startTime, endTime) {
    document.getElementById('eventTitle').innerText = title;
    document.getElementById('eventDescription').innerText = description;
    document.getElementById('eventImage').src = image;
    document.getElementById('eventStartTime').innerText = startTime;
    document.getElementById('eventEndTime').innerText = endTime;
    document.getElementById('eventModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function hideEventModal() {
    document.getElementById('eventModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}
</script>

<?php include_once('temp/footeradmin.php'); ?>
