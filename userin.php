<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<div class="auth-content">
    <form action="connecting/login.php" method="post">
        <h2 class="form-title">Login</h2>

        <div>
            <label>Username</label>
            <input type="text" name="username" class="text-input" required>
        </div>

        <div>
            <label>Password</label>
            <input type="password" name="password" class="text-input" required>
        </div>

        <div>
            <button type="submit" class="btn btn-big" name="login-btn">Login</button>
        </div>

        <p>Or <a href="userup.php">Sign Up</a></p>
    </form>
</div>

<?php include_once('temp/footer.php'); ?>
