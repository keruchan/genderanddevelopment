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

    <!-- Admin Wrapper --> 
    <div class="admin-wrapper">

        <!-- Left Slidebar -->
        <!-- <div class="left-sidebar">
            

        </div> -->
        <!-- //Left Slidebar -->

        <!-- Admin Content -->
        <div class="admin-content">
                <form action="userin.php" method="post">
            <h2 class="form-title heading">User Registration</h2>

            <div class="auther-content">
            

                <form action="userup.php" method="post">
                    <!-- Rey jani  ikkada register form di video chusi add chai vachaka/ -->
                    <div>
                        <label>First Name</label>
                        <input type="text" name="first_name" class="text-input">
                    </div>
                    <div>
                        <label>Last Name</label>
                        <input type="text" name="last_name" class="text-input">
                    </div>
                    <div>
                        <label>Age</label>
                        <input type="number" name="age" class="text-input">
                    </div>
                    <div>
                        <label>Institutional Email</label>
                        <input type="email" name="email" class="text-input">
                    </div>
                    <div>
                        <lable>Contact Number</lable>
                        <input type="number" name="number" class="text-input">
                    </div>
                    <div>
                        <lable>Address</lable><br>
                        <input class="text-input" name="address" type="text">
                    </div>
                    <div>
                        <lable>Department</lable>
                        <input type="text" name="department" class="text-input">
                    </div>
                    <div>
                        <lable>Course</lable>
                        <input type="text" name="course" class="text-input">
                    </div>
                    <div>
                        <lable>year</lable>
                        <input type="text" name="year" class="text-input">
                    </div>
                    <div>
                        <lable>Section</lable>
                        <input type="text" name="section" class="text-input">
                    </div>
                    <div>
                        <lable for="options">Group</lable>
                        <select id="options" name="options" class="text-input" required>
                            <option value="" disabled selected>Select an option</option>
                            <option value="option1">LGBTQ+</option>
                            <option value="option2">Pregnant</option>
                            <option value="option3">PWD</option>
                     </select>
                    </div>
                    <div>
                        <lable>Username</lable>
                        <input type="text" name="username" class="text-input">
                    </div>
                    <div>
                        <lable>password</lable>
                        <input type="password" name="password" class="text-input">
                    </div>
                    <!-- <div> 
                        <label class="education"><b> Higher Eduction : </b></label>
                        <div> College/University</div> 
                        <input type="text" name="collegename" class="text-input">
                        <div>Year Of Passing</div>
                        <input type="month" name="year" class="text-input">
                        <div>Percentage(%)</div>
                        <input type="text" name="percentage" class="text-input">
                    </div>
                    <div>
                        <label class="education"><b> Intermidiate :</b></label>
                        <div> College</div> 
                        <input type="text" name="collegename" class="text-input">
                        <div>Year Of Passing</div>
                        <input type="month" name="year" class="text-input">
                        <div>Percentage(%)</div>
                        <input type="text" name="percentage" class="text-input"> -->
<!-- 
                    </div> -->
                    <div>
                        <lable>Photo Upload</lable>
                        <input type="file" name="image" class="text-input">
                    </div>
                    <div>
                        <lable>Joining Date</lable>
                        <input type="month" name="month" class="text-input">
                    </div>
                    <div>
                        <lable>Reporting Date</lable>
                        <input type="month" name="month" class="text-input">
                    </div>
                    <div>
                        <lable>Gender</lable>
                        <input type="radio" name="gender" value="Male" >Male
                        <input type="radio" name="gender" value="Female" >Female
                    </div>
                    <div>
                        <button type="submit" class="btn btn-big">Submit</button>
                    </div>
                    <p>Or <a href="userin.php">Sign in</a></p>
                </form>

            </div>

        </div>
        <!-- //Admin Content -->
    </div>
    <!-- //Admin Wrapper -->
    
    <?php include_once('temp/footer.php') ?>


