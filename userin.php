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
            <div style="position: relative;">
                <input type="password" name="password" id="password" class="text-input" required>
                <i class="fas fa-eye" id="togglePassword" 
                   style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%);
                          cursor: pointer; transition: all 0.3s ease;"></i>
            </div>
        </div>

        <div>
            <button type="submit" class="btn btn-big" name="login-btn">Login</button>
        </div>

        <p>Or <a href="userup.php">Sign Up</a></p>
    </form>
</div>

<?php include_once('temp/footer.php'); ?>

<!-- Font Awesome CDN (Add only once in your layout/head if not already present) -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});
</script>

<!-- Custom Styles for Eye Icon (Responsive & Appealing) -->
<style>
    .text-input {
        padding-right: 30px; /* Give space on right for the eye icon */
    }

    /* Improved Eye Icon style */
    #togglePassword {
        font-size: 1.2rem; /* Slightly bigger icon */
        color: #555; /* Dark color for visibility */
        transition: color 0.3s ease; /* Smooth color transition */
    }

    #togglePassword:hover {
        color: #007bff; /* Change color on hover to blue */
    }

    /* Add padding and increase button size for better mobile responsiveness */
    .btn {
        padding: 12px 20px;
        font-size: 1.1rem;
    }
</style>
