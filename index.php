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
                <li><a href="update.php">Update</a></li>
                <li><a href="connecting/logout.php">Logout</a></li>
            </ul>';
    }
}

// Display logout notification
if (isset($_SESSION['logout_message'])) {
    echo "<script>alert('" . $_SESSION['logout_message'] . "');</script>";
    unset($_SESSION['logout_message']); // Remove message after displaying
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="post-slider">
        <h1 class="post-slider-title">Learn about Gender and Development</h1>
        <i class="fas fa-chevron-left prev"></i>
        <i class="fas fa-chevron-right next"></i>

        <div class="post-wrapper">
            <div class="post">
                <img src="images/1.jpg" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php">Sample description</a></h4>
                    <i class="far fa-user">Sample post</i>
                    &nbsp;
                    <i class="far fa-calendar">January 1, 2025</i>
                </div>
            </div>
            <div class="post">
                <img src="images/3.jpg" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php">Sample description</a></h4>
                    <i class="far fa-user">Sample post</i>
                    &nbsp;
                    <i class="far fa-calendar">January 2, 2025</i>
                </div>
            </div>
            <div class="post">
                <img src="images/4.jpg" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php">Sample description</a></h4>
                    <i class="far fa-user">Sample Post</i>
                    &nbsp;
                    <i class="far fa-calendar">January 3, 2025</i>
                </div>
            </div>
            <div class="post">
                <img src="images/2.jpg" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php">Sample description</a></h4>
                    <i class="far fa-user">Sample Post</i>
                    &nbsp;
                    <i class="far fa-calendar">January 4, 2025</i>
                </div>
            </div>
            <div class="post">
                <img src="images/5.jpg" alt="" class="slider-image">
                <div class="post-info">
                    <h4><a href="single.php">Sample description</a></h4>
                    <i class="far fa-user">Sample Post</i>
                    &nbsp;
                    <i class="far fa-calendar">January 5, 2025</i>
                </div>
            </div>
        </div>
    </div>

    <!-- Content -->
    <div class="content clearfix">
        <!-- Main Content -->
        <div class="main-content">
            <h1 class="recent-post-title">Events</h1>

            <div class="post">
                <img src="images/1.jpg" alt="" class="post-image">
                <div class="post-preview">
                    <h2><a href="single.php">The Strongest and sweetest songs yet remain to be sung</a></h2>
                    <i class="far fa-user">Shaik Jani</i>
                    &nbsp;
                    <i class="far fa-calendar">Dec 9, 2019</i>
                    <p class="preview-text">
                        The books contain knowledge on the Black Arts which Zeref used. Many of the spells known to come from the books involve the creation of Demons.
                    </p>
                    <a href="single.php" class="btn read-more">Read More</a>
                </div>
            </div>
            <div class="post">
                <img src="images/3.jpg" alt="" class="post-image">
                <div class="post-preview">
                    <h2><a href="single.php">The Strongest and sweetest songs yet remain to be sung</a></h2>
                    <i class="far fa-user">Shaik Jani</i>
                    &nbsp;
                    <i class="far fa-calendar">Dec 9, 2019</i>
                    <p class="preview-text">
                        The books contain knowledge on the Black Arts which Zeref used. Many of the spells known to come from the books involve the creation of Demons.
                    </p>
                    <a href="single.php" class="btn read-more">Read More</a>
                </div>
            </div>
        </div>
        <!-- //Main Content -->

        <div class="sidebar">
            <div class="section search">
                <h2 class="section-title">Search</h2>
                <form action="index.html" method="post">
                    <input type="text" name="search-term" class="text-input" placeholder="Search...">
                </form>
            </div>
        </div>
    </div>
    <!-- //Content -->
</div>
<!-- //Page Wrapper -->

<?php include_once('temp/footer.php'); ?>
