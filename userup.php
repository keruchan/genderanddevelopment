<?php include_once('temp/header.php') ?>
<?php include_once('temp/navigation.php') ?>

<!-- Admin Wrapper --> 
<div class="admin-wrapper">

    <!-- Left Slidebar -->
    <!-- <div class="left-sidebar">


    </div> -->
    <!-- //Left Slidebar -->

    <!-- Admin Content -->
    <h2 class="form-title heading" style="font-size: 50px;">User Registration</h2>

    <div class="auther-content">
        <form action="connecting/register.php" method="post" enctype="multipart/form-data">
            <div>
                <label>First Name</label>
                <input type="text" name="firstname" class="text-input" required>
            </div>
            <div>
                <label>Last Name</label>
                <input type="text" name="lastname" class="text-input" required>
            </div>
            <div>
                <label>Age</label>
                <input type="number" name="age" class="text-input" required>
            </div>
            <div>
                <label>Institutional Email</label>
                <input type="email" name="email" class="text-input" required>
            </div>
            <div>
                <label>Contact Number</label>
                <input type="number" name="contact" class="text-input" required>
            </div>
            <div>
                <label>Address</label>
                <input type="text" name="address" class="text-input" required>
            </div>
            <div>
                <label>Department</label>
                <select id="department" name="department" class="text-input" required>
                    <option value="" disabled selected>Select a department</option>
                    <option value="CCS">CCS (College of Computer Studies)</option>
                    <option value="CCJE">CCJE (College of Criminal Justice Education)</option>
                    <option value="CAS">CAS (College of Arts and Sciences)</option>
                    <option value="COE">COE (College of Engineering)</option>
                    <option value="CBMA">CBMA (College of Business and Management Administration)</option>
                    <option value="CHMT">CHMT (College of Hospitality Management and Tourism)</option>
                    <option value="CTE">CTE (College of Teacher Education)</option>
                </select>
            </div>
            <div>
                <label>Course</label>
                <select id="course" name="course" class="text-input" required>
                    <option value="" disabled selected>Select a course</option>
                </select>
            </div>
            <div>
                <label>Year</label>
                <input type="text" name="year" class="text-input" required>
            </div>
            <div>
                <label>Section</label>
                <input type="text" name="section" class="text-input" required>
            </div>
            <div>
                <label>Group</label>
                <select name="groupp" class="text-input" required>
                    <option value="" disabled selected>Select an option</option>
                    <option value="LGBTQ+">LGBTQ+</option>
                    <option value="Pregnant">Pregnant</option>
                    <option value="PWD">PWD</option>
                </select>
            </div>
            <div>
                <label>Username</label>
                <input type="text" name="username" class="text-input" required>
            </div>
            <div>
                <label>Password</label>
                <input type="password" name="password" class="text-input" required>
            </div>
            <div>
                <label>Photo Upload</label>
                <input type="file" name="profilepic" class="text-input" required>
            </div>
            <div>
                <label>Gender:</label>
                <input type="radio" name="gender" value="Male" required>Male
                <input type="radio" name="gender" value="Female" required>Female
            </div>
            <div>
                <button type="submit" name="register" class="btn btn-big">Submit</button>
            </div>
        </form>
    </div>

</div>
<!-- //Admin Content -->
</div>
<!-- //Admin Wrapper -->

<script>
    document.getElementById('department').addEventListener('change', function() {
        var department = this.value;
        var course = document.getElementById('course');
        course.innerHTML = '<option value="" disabled selected>Select a course</option>'; // Clear previous options

        var courses = {
            'CCS': ['BS Computer Science', 'BS Information Technology', 'BS Information Systems'],
            'CCJE': ['BS Criminology', 'BS Forensic Science'],
            'CAS': ['BA Psychology', 'BA Political Science'],
            'COE': ['BS Computer Engineering', 'BS Mechanical Engineering'],
            'CBMA': ['BSBA Marketing', 'BSBA Finance', 'BS Office Administration'],
            'CHMT': ['BS Hospitality Management', 'BS Tourism'],
            'CTE': ['BS Education (Secondary Education)', 'BS Education (Primary Education)']
        };

        courses[department].forEach(function(courseName) {
            var option = document.createElement('option');
            option.value = courseName;
            option.text = courseName;
            course.add(option);
        });
    });
</script>

<?php include_once('temp/footer.php') ?>