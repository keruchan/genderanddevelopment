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
        $month = 1;
        $year++;
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
                $calendar .= "<div class='event' style='background-color: #4285F4; color: #fff; padding: 5px; margin: 5px 0; border-radius: 4px; cursor: pointer;' onclick='showEventModal(\"{$event['title']}\", \"{$event['description']}\\nStart: {$event['start_time']}\\nEnd: {$event['end_time']}\")'>{$event['title']}</div>";
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
    <button onclick="hideEventModal()" style="background-color: #4285F4; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer;">Close</button>
</div>
<div id="modalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;" onclick="hideEventModal()"></div>

<script>
function showEventModal(title, description) {
    document.getElementById('eventTitle').innerText = title;
    document.getElementById('eventDescription').innerText = description;
    document.getElementById('eventModal').style.display = 'block';
    document.getElementById('modalOverlay').style.display = 'block';
}

function hideEventModal() {
    document.getElementById('eventModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
}

function showPostEventModal() {
    document.getElementById('postEventModal').style.display = 'block';
    document.getElementById('postModalOverlay').style.display = 'block';
}

function hidePostEventModal() {
    document.getElementById('postEventModal').style.display = 'none';
    document.getElementById('postModalOverlay').style.display = 'none';
}
</script>

<!-- Post Event Modal -->
<div id="postEventModal" style="display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); background-color: #fff; padding: 30px; box-shadow: 0 0 15px rgba(0,0,0,0.5); z-index: 1000; border-radius: 8px; width: 90%; max-width: 600px; font-family: Arial, sans-serif;">
    <h2 style="font-size: 28px; margin-bottom: 20px; color: #333;">Post New Event</h2>
    <form id="postEventForm" action="post_event.php" method="POST" enctype="multipart/form-data">
        <label for="eventTitleInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">Title:</label>
        <input type="text" id="eventTitleInput" name="title" required style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">
        
        <label for="eventDescriptionInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">Description:</label>
        <textarea id="eventDescriptionInput" name="description" required style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px; resize: vertical; height: 100px;"></textarea>
        
        <label for="eventDateInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">Date:</label>
        <input type="date" id="eventDateInput" name="event_date" required style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">
        
        <label for="startTimeInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">Start Time:</label>
        <input type="time" id="startTimeInput" name="start_time" required style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">
        
        <label for="endTimeInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">End Time:</label>
        <input type="time" id="endTimeInput" name="end_time" required style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">
        
        <label for="eventAttachmentInput" style="font-size: 18px; display: block; margin-bottom: 10px; color: #555;">Attachment:</label>
        <input type="file" id="eventAttachmentInput" name="attachment" style="font-size: 16px; width: 100%; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd; border-radius: 4px;">
        
        <div style="display: flex; justify-content: space-between; margin-top: 20px;">
            <button type="submit" style="background-color: #4285F4; color: #fff; font-size: 18px; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; flex: 1; margin-right: 10px;">Post Event</button>
            <button type="button" onclick="hidePostEventModal()" style="background-color: #ccc; color: #000; font-size: 18px; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; flex: 1; margin-left: 10px;">Cancel</button>
        </div>
    </form>
</div>
<div id="postModalOverlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 999;" onclick="hidePostEventModal()"></div>

<button onclick="showPostEventModal()" style="background-color: #4285F4; color: #fff; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin-top: 20px;">Post New Event</button>

<?php include_once('temp/footeradmin.php'); ?>

<?php
if (isset($_GET['success'])) {
    echo "<script>showSuccessModal('Event posted successfully!');</script>";
} elseif (isset($_GET['failure'])) {
    echo "<script>showFailureModal('Failed to post event. An event already exists on this date.');</script>";
}
?>