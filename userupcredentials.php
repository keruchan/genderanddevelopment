<?php include_once('temp/header.php') ?>
<?php include_once('temp/navigation.php') ?>

<?php

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['department'] = $_POST['department'];
    $_SESSION['course'] = $_POST['course'];
    $_SESSION['year'] = $_POST['year'];
    $_SESSION['section'] = $_POST['section'];
}
?>

<!-- Admin Wrapper --> 
<div class="admin-wrapper">

    <!-- Admin Content -->
    <h2 class="form-title heading" style="font-size: 50px;">User Registration - Page 3</h2>

    <div class="auther-content">
        <form action="connecting/register.php" method="post" onsubmit="return validatePassword()">
            <div>
                <label class="required-label">
                    <input type="text" name="username" class="text-input" placeholder="Username" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="password" id="password" name="password" class="text-input" placeholder="Password" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div>
                <label class="required-label">
                    <input type="password" id="confirm_password" name="confirm_password" class="text-input" placeholder="Confirm Password" required>
                    <span class="required-asterisk">*</span>
                </label>
            </div>
            <div style="display: flex; justify-content: space-between;">
                <!-- Alternative back navigation approach -->
                <button type="button" class="btn btn-big" onclick="window.location.href = 'userupcourse.php'">Back</button>
                <button type="submit" name="register" class="btn btn-big">Submit</button>
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

    function validatePassword() {
        const password = document.getElementById("password").value;
        const confirmPassword = document.getElementById("confirm_password").value;
        
        if (password !== confirmPassword) {
            alert("Passwords do not match.");
            return false;
        }
        return true;
    }
</script>