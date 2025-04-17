<?php
// Start a session and include necessary files
session_start();
require 'connecting/connect.php'; // Include database connection
include_once('temp/header.php');
include_once('temp/navigation.php');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve form data
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $age = $_POST['age'];
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $address = $_POST['address'];
    $gender = $_POST['gender'];
    $groupp = $_POST['groupp'];
    $impairment = $_POST['impairment'] ?? ''; // For PWD group, impairment will be required
    $profilePicPath = NULL;

    // Check if profile picture is uploaded
    if (!empty($_FILES["profilepic"]["name"])) {
        $allowedTypes = ["image/jpeg", "image/png"];
        $uploadDir = "profilepic/"; // Profile picture folder

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true); // Create directory if it doesn't exist
        }

        $fileType = mime_content_type($_FILES["profilepic"]["tmp_name"]);
        if (!in_array($fileType, $allowedTypes)) {
            echo "<script>
                    alert('Invalid profile picture format. Only JPG and PNG allowed.');
                    window.location.href = 'userupcourse.php';
                  </script>";
            exit();
        }

        if ($_FILES["profilepic"]["size"] > 2 * 1024 * 1024) { // Max 2MB
            echo "<script>
                    alert('Profile picture size should not exceed 2MB.');
                    window.location.href = 'userupcourse.php';
                  </script>";
            exit();
        }

        // Generate a unique name for the uploaded file and move it to the profilepic folder
        $profilePicPath = $uploadDir . uniqid() . '_' . basename($_FILES["profilepic"]["name"]);
        if (!move_uploaded_file($_FILES["profilepic"]["tmp_name"], $profilePicPath)) {
            echo "<script>
                    alert('Failed to upload profile picture.');
                    window.location.href = 'userupcourse.php';
                  </script>";
            exit();
        }
    }

    // Insert user data into the database
    $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, age, email, contact, address, gender, groupp, impairment, profilepic) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssissssss", $firstname, $lastname, $age, $email, $contact, $address, $gender, $groupp, $impairment, $profilePicPath);

    if ($stmt->execute()) {
        echo "<script>
                alert('User registered successfully!');
                window.location.href = 'index.php'; // Redirect to a page (for example, dashboard)
              </script>";
    } else {
        echo "<script>
                alert('Registration failed. Please try again.');
                window.location.href = 'userupcourse.php';
              </script>";
    }

    $stmt->close();
    $conn->close();
}
?>

<!-- Admin Wrapper -->
<div class="admin-wrapper">

    <!-- Admin Content -->
    <h2 class="form-title heading" style="font-size: 50px;">User Registration - Page 1</h2>

    <div class="auther-content">
        <form id="registrationForm" action="userupcourse.php" method="post" enctype="multipart/form-data" onsubmit="return checkEmailExists();">
            <div>
                <label class="required-label">
                    <input type="text" name="firstname" class="text-input" placeholder="First Name" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="text" name="lastname" class="text-input" placeholder="Last Name" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="number" name="age" class="text-input" placeholder="Age" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="email" name="email" class="text-input" placeholder="Institutional Email" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="text" name="contact" class="text-input" placeholder="Contact Number (09123456789 or +639123456789)" required 
                           pattern="(\+639\d{9}|09\d{9})" 
                           title="Enter a valid contact number (09123456789 or +639123456789)">
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="text" name="address" class="text-input" placeholder="Address" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label style="font-size: 20px;">Sex:</label>
                <input type="radio" name="gender" value="Male" required><span style="font-size: 20px; margin-right: 10px;">Male</span>
                <input type="radio" name="gender" value="Female" required><span style="font-size: 20px;">Female</span>
                <span class="required-asterisk">*</span>
            </div>
            <div style="display: inline-flex; align-items: center; width: 100%; gap: 10px;">
                <label style="font-size: 25px; white-space: nowrap;">Profile Picture:</label>
                <input type="file" name="profilepic" style="flex: 1; font-size: 20px;" required>
            </div>
            <div>
                <label class="required-label">
                    <select id="groupSelect" name="groupp" class="text-input" required>
                        <option value="" disabled selected>Select Group</option>
                        <option value="LGBTQ+">LGBTQ+</option>
                        <option value="Pregnant">Pregnant</option>
                        <option value="PWD">PWD</option>
                    </select>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div id="impairmentDiv" style="display: none;">
                <label class="required-label">
                    <select id="impairmentSelect" name="impairment" class="text-input">
                        <option value="" disabled selected>Select Impairment</option>
                        <option value="Leg Impairment">Leg Impairment</option>
                        <option value="Eye Impairment">Eye Impairment</option>
                        <option value="Arms Impairment">Arms Impairment</option>
                    </select>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <button type="submit" name="next" class="btn btn-big">Next</button>
            </div>
        </form>
    </div>

</div>
<!-- //Admin Content -->
</div>
<!-- //Admin Wrapper -->

<?php include_once('temp/footer.php') ?>

<style>
    .required-label {
        position: relative;
        display: block;
    }

    .required-asterisk {
        color: red;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        display: none; /* Initially hide the asterisk */
    }

    .text-input:invalid ~ .required-asterisk {
        display: inline; /* Show the asterisk if input is invalid */
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const inputs = document.querySelectorAll(".text-input");
        const groupSelect = document.getElementById("groupSelect");
        const impairmentDiv = document.getElementById("impairmentDiv");
        const impairmentSelect = document.getElementById("impairmentSelect");

        groupSelect.addEventListener("change", function() {
            if (this.value === "PWD") {
                impairmentDiv.style.display = "block";
                impairmentSelect.required = true;
            } else {
                impairmentDiv.style.display = "none";
                impairmentSelect.required = false;
            }
        });

        inputs.forEach(input => {
            input.addEventListener("input", function() {
                const asterisk = this.nextElementSibling;
                if (this.checkValidity()) {
                    asterisk.style.display = "none";
                } else {
                    asterisk.style.display = "inline";
                }
            });
        });
    });

async function checkEmailExists() {
    const email = document.querySelector('input[name="email"]').value;

    if (!email) return true; // Allow form submission if no email is entered

    try {
        const response = await fetch('check_email.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'email=' + encodeURIComponent(email)
        });

        const data = await response.json();

        if (data.status === 'error') {
            alert(data.message); // Show the message in an alert
            const emailInput = document.querySelector('input[name="email"]');
            emailInput.style.border = "2px solid red"; // Highlight the field
            emailInput.focus();
            window.location.href = "userup.php"; // Redirect to userup.php
            return false; // Prevent form submission
        } else {
            return true; // Allow form submission
        }
    } catch (error) {
        console.error('Error:', error);
        alert("Something went wrong, please try again.");
        return false; // Prevent submission on error
    }
}
</script>
