<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>
<?php include_once('connecting/connect.php'); ?>

<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f3f4f6;
        color: #333;
    }

    .container {
        max-width: 800px;
        margin: 40px auto;
        padding: 30px;
        background-color: #ffffff;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        border: 1px solid #e0e0e0;
    }

    .container h2 {
        text-align: center;
        font-size: 24px;
        color: #007bff;
        margin-bottom: 30px;
    }

    .profile-section {
        display: flex;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .profile-section img {
        border-radius: 50%;
        width: 150px;
        height: 150px;
        object-fit: cover;
    }

    .profile-section .user-details {
        flex-grow: 1;
        margin-left: 20px;
    }

    .profile-section .user-details h3 {
        margin-bottom: 10px;
        font-size: 22px;
        color: #333;
    }

    .profile-section .user-details p {
        font-size: 14px;
        color: #555;
    }

    .profile-section .user-details .detail-title {
        font-weight: 600;
        margin-bottom: 5px;
    }

    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 30px;
    }

    .details-table th, .details-table td {
        padding: 12px;
        text-align: left;
        border-bottom: 1px solid #f0f0f0;
    }

    .details-table th {
        background-color: #f8f9fa;
        color: #333;
        font-weight: bold;
    }

    .details-table td {
        background-color: #fafafa;
        color: #666;
    }

    .button-container {
        text-align: center;
        margin-top: 20px;
    }

    .button-container a {
        text-decoration: none;
        padding: 12px 20px;
        background-color: #007bff;
        color: white;
        border-radius: 5px;
        font-size: 16px;
        transition: background-color 0.3s ease;
    }

    .button-container a:hover {
        background-color: #0056b3;
    }
</style>

<div class="container">
    <h2>User Profile</h2>

    <?php
    // Start session and check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        echo "<p>Please log in to view your profile.</p>";
        exit;
    }

    // Fetch user data from the database
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "<p>User not found.</p>";
        exit;
    }
    ?>

    <!-- Profile Section -->
    <div class="profile-section">
        <?php 
        // Check if the profile picture exists, and if not, use a default image
        $profilePic = !empty($user['profilepic']) ? $user['profilepic'] : 'profilepic/default-avatar.png'; 
        ?>
        <img src="<?php echo $profilePic; ?>" alt="Profile Picture">
        
        <div class="user-details">
            <h3><?php echo !empty($user['firstname']) ? $user['firstname'] . ' ' . $user['lastname'] : 'No Name'; ?></h3>
            <p><span class="detail-title">Username:</span> <?php echo !empty($user['username']) ? $user['username'] : 'Not Available'; ?></p>
            <p><span class="detail-title">Email:</span> <?php echo !empty($user['email']) ? $user['email'] : 'Not Available'; ?></p>
            <p><span class="detail-title">Gender:</span> <?php echo !empty($user['gender']) ? $user['gender'] : 'Not Available'; ?></p>
        </div>
    </div>

    <!-- Details Table -->
    <table class="details-table">
        <tr>
            <th>Age</th>
            <td><?php echo !empty($user['age']) ? $user['age'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Contact</th>
            <td><?php echo !empty($user['contact']) ? $user['contact'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Address</th>
            <td><?php echo !empty($user['address']) ? $user['address'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Department</th>
            <td><?php echo !empty($user['department']) ? $user['department'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Course</th>
            <td><?php echo !empty($user['course']) ? $user['course'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Year</th>
            <td><?php echo !empty($user['year']) ? $user['year'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Section</th>
            <td><?php echo !empty($user['section']) ? $user['section'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Group</th>
            <td><?php echo !empty($user['groupp']) ? $user['groupp'] : 'Not Available'; ?></td>
        </tr>
        <tr>
            <th>Impairment</th>
            <td><?php echo !empty($user['impairment']) ? $user['impairment'] : 'Not Available'; ?></td>
        </tr>
    </table>

    <!-- Button to update profile -->
    <div class="button-container">
        <a href="user_update.php">Edit Profile</a>
    </div>
</div>

<?php include_once('temp/footer.php'); ?>

