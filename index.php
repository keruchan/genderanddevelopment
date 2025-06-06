<?php 
session_start();
require 'connecting/connect.php'; // Ensure correct path

$displayName = '<i class="fa fa-user"></i> LOGIN'; // Default login text
$dropdownMenu = ''; // Empty by default

if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Fetch first name from the database
    $stmt = $conn->prepare("SELECT firstname FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->bind_result($firstName);
    $stmt->fetch();
    $stmt->close();

    if (!empty($firstName)) {
        $displayName = '<i class="fa fa-user"></i> ' . htmlspecialchars($firstName);
        $dropdownMenu = '
            <ul class="dropdown">
                <li><a href="user_update.php">Update</a></li>
                <li><a href="connecting/logout.php">Logout</a></li>
            </ul>';
    }
}

// Display logout notification
if (isset($_SESSION['logout_message'])) {
    echo "<script>alert('" . $_SESSION['logout_message'] . "');</script>";
    unset($_SESSION['logout_message']); // Remove message after displaying
}

// Get the selected month filter (if any)
$selectedMonth = isset($_POST['month']) ? $_POST['month'] : 'all';

// Fetch stories from the database
$stmt = $conn->prepare("SELECT id, title, writer, date_published, picture FROM stories ORDER BY date_published DESC");
$stmt->execute();
$stmt->bind_result($id, $title, $writer, $datePublished, $picture);
$stories = [];
while ($stmt->fetch()) {
    $stories[] = [
        'id' => $id,
        'title' => $title,
        'writer' => $writer,
        'date_published' => $datePublished,
        'picture' => $picture
    ];
}
$stmt->close();

// Fetch upcoming events from the database with optional filtering by month
if (!empty($selectedMonth) && $selectedMonth !== 'all') {
    $stmt = $conn->prepare("SELECT id, title, description, event_date, start_time, end_time, attachment_path, attendees, max_participants FROM events WHERE MONTH(event_date) = ? AND event_date >= CURDATE() ORDER BY event_date DESC");
    $stmt->bind_param("s", $selectedMonth);
} else {
    $stmt = $conn->prepare("SELECT id, title, description, event_date, start_time, end_time, attachment_path, attendees, max_participants FROM events WHERE event_date >= CURDATE() ORDER BY event_date DESC");
}

$stmt->execute();
$stmt->bind_result($eventId, $eventTitle, $eventDescription, $eventDate, $eventStartTime, $eventEndTime, $eventAttachment, $eventAttendees, $eventMaxParticipants);
$events = [];
while ($stmt->fetch()) {
    // Format start_time and end_time to 12-hour AM/PM format
    $formattedStartTime = date("h:i A", strtotime($eventStartTime));
    $formattedEndTime = date("h:i A", strtotime($eventEndTime));

    $events[] = [
        'id' => $eventId,
        'title' => $eventTitle,
        'description' => $eventDescription,
        'event_date' => $eventDate,
        'start_time' => $formattedStartTime,
        'end_time' => $formattedEndTime,
        'attachment_path' => $eventAttachment,
        'attendees' => $eventAttendees,
        'max_participants' => $eventMaxParticipants
    ];
}
$stmt->close();
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="post-slider">
        <h1 class="post-slider-title">Learn about Gender and Development</h1>
        <i class="fas fa-chevron-left prev"></i>
        <i class="fas fa-chevron-right next"></i>

        <h1 class="recent-post-title" style="left:75px; position:relative;">Posts</h1>
        <div class="post-wrapper">
            <?php foreach ($stories as $story): ?>
            <div class="post">
                <img src="images/<?php echo htmlspecialchars($story['picture']); ?>" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php?id=<?php echo htmlspecialchars($story['id']); ?>"><?php echo htmlspecialchars($story['title']); ?></a></h4>
                    <i class="far fa-calendar"><?php echo htmlspecialchars($story['date_published']); ?></i>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="content clearfix">
        <!-- Main Content -->
        <div class="main-content">
            <h1 class="recent-post-title">Upcoming Events</h1>

            <?php if (empty($events)): ?>
                <p>No upcoming events found for the selected month.</p>
            <?php else: ?>
                <?php foreach ($events as $event): ?>
                <div class="post event-container">
                    <img src="attachments/<?php echo htmlspecialchars($event['attachment_path']); ?>" alt="" class="post-image">
                    <div class="post-preview">
                        <h2><?php echo htmlspecialchars($event['title']); ?></h2>
                        <p><i class="far fa-calendar"></i> <?php echo htmlspecialchars(date("F j, Y", strtotime($event['event_date']))); ?> 
                        | <strong>Start Time:</strong> <?php echo htmlspecialchars($event['start_time']); ?> 
                        | <strong>End Time:</strong> <?php echo htmlspecialchars($event['end_time']); ?></p>
                        <p class="preview-text">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </p>

                        <?php 
                        $userId = $_SESSION['user_id'] ?? null;
                        $eventId = $event['id'];
                        $isAttending = false;
                        $availableSeats = (int)$event['max_participants'] - (int)$event['attendees'];

                        if ($userId) {
                            $stmt = $conn->prepare("SELECT id FROM event_attendance WHERE user_id = ? AND event_id = ?");
                            $stmt->bind_param("ii", $userId, $eventId);
                            $stmt->execute();
                            $stmt->store_result();
                            
                            if ($stmt->num_rows > 0) {
                                $isAttending = true;
                            }
                            $stmt->close();
                        }

                        if ($isAttending): ?>
                            <p style="color: green; font-weight: bold;">✔ You are already attending this event.</p>
                        <?php elseif ($availableSeats <= 0): ?>
                            <button type="button" class="btn" disabled style="background-color: #ccc; cursor: not-allowed;">
                                <i class="fa fa-times"></i> Full
                            </button>
                        <?php else: ?>
                            <form action="attend_event.php" method="post" onsubmit="return checkLogin()">
                                <input type="hidden" name="event_id" value="<?php echo $eventId; ?>">
                                <button type="submit" class="btn"><i class="fa fa-check"></i> Attend</button>
                            </form>
                        <?php endif; ?>

                        <p>Attendees: <?php echo (int) $event['attendees']; ?> | 
                        Available seats: <?php echo $availableSeats; ?>
                        </p>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

        </div>
        <!-- //Main Content -->

        <div class="sidebar">
            <div class="section filter">
                <h2 class="section-title">Filter by Month</h2>
                <form action="" method="post">
                    <select name="month" class="text-input" onchange="this.form.submit()">
                        <option value="" disabled>Select Month</option>
                        <option value="all" <?php if ($selectedMonth === 'all') echo 'selected'; ?>>All</option>
                        <?php for ($i = 1; $i <= 12; $i++): ?>
                            <option value="<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>" <?php if ($selectedMonth == str_pad($i, 2, '0', STR_PAD_LEFT)) echo 'selected'; ?>>
                                <?php echo date("F", mktime(0, 0, 0, $i, 1)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </form>
            </div>
        </div>
    </div>
    <!-- //Content -->
</div>
<!-- //Page Wrapper -->

<?php include_once('temp/footer.php'); ?>

<style>
/* Ensure proper box sizing and text wrapping for event containers */
.event-container {
    border: 1px solid #ccc;
    border-radius: 5px;
    padding: 15px;
    margin-bottom: 20px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    overflow: hidden; /* Prevent content overflow */
    background-color: #fff; /* Add a white background for better readability */
    min-height: unset; /* Ensure no fixed min-height */
    height: auto !important; /* Force auto height override */
}

.event-container .post-image {
    max-width: 100%; /* Ensure the image doesn't overflow */
    height: auto;
    border-radius: 5px;
    margin-bottom: 10px;
}

.event-container .post-preview {
    word-wrap: break-word; /* Break long text to prevent overflow */
    overflow-wrap: break-word;
}

.event-container p {
    margin: 5px 0;
}

.event-container h2 {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 10px;
}

.text-input {
    width: 100%;
    padding: 10px;
    margin: 10px 0;
    box-sizing: border-box;
    border: 1px solid #ccc;
    border-radius: 4px;
}

.sidebar .section {
    margin-bottom: 20px;
}

.sidebar .section-title {
    font-size: 20px;
    margin-bottom: 10px;
}

.btn {
    background-color: #007BFF;
    color: white;
    padding: 10px;
    border: none;
    cursor: pointer;
    border-radius: 4px;
    margin-top: 10px;
}

.btn i {
    margin-right: 5px;
}
</style>

<script>
function checkLogin() {
    <?php if (!isset($_SESSION['user_id'])): ?>
        alert("Please log in to attend the event.");
        window.location.href = "userin.php";
        return false;
    <?php endif; ?>
    return true;
}
</script>