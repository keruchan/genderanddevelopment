<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        
        <!-- Font Awesome -->
        <!-- <script src="https://kit.fontawesome.com/5734d4928d.js"></script> -->
        <link rel="stylesheet" href="fontcss/all.css">

        <!-- Google font -->
        <link href="https://fonts.googleapis.com/css?family=Candal|Lora&display=swap" rel="stylesheet">

        <!-- Custom Styling -->
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>
        <header>
        <div class="logo">
                    <img src="pictures/gadimage1.png">
                </div>
            <div class="logo">
               <a href="index.php"> <h1 class="logo-text">Gender<span>&</span>Development </h1></a>
            </div>
            <i class="fa fa-bars menu-toggle"></i>
            <ul class="nav">
                
                    <li><a href="index.php">Homepage</a></li>
                    <!-- <li><a href="adminin.html">Admin</a></li>
                    <li><a href="superadmin.html">Super Admin</a></li> -->
                <!-- <li><a href="index.html">Home</a></li> -->
                <!-- <li><a href="#">
                        <i class="fa fa-user"></i>
                          Profile   
                        <i class="fa fa-chevron-down" style="font-size: .8em;"></i>
                    </a>
                    <ul>
                        <li><a href="#" class="logout">Logout</a></li>
                    </ul>
                </li> -->
            </ul>
        </header>

        <div class="auth-content">
            <form action="userin.html" method="post">
                <h2 class="form-title">Login</h2>
                <div>
                    <label>Username</label>
                    <input type="text" name="username" class="text-input">
                </div>
                <div>
                    <label>Password</label>
                    <input type="password" name="password" class="text-input">
                <!-- </div>
                <label>Role</label>
                        <select name="role" class="text-input">
                            <option value="User">User</option>
                            <option value="Admin">Admin</option>
                        </select>
                <div> -->
                    <button type="submit" class="btn btn-big" name="login-btn"><a href="index.php">Login</a></button>
                </div>
                
                <p>Or <a href="userup.php">Sign Up</a></p>
            </form>


        </div>
        <?php include_once('temp/footer.php') ?>
