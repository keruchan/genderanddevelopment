<?php include_once('temp/header.php') ?>
<?php include_once('temp/navigation.php') ?>
<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $_SESSION['firstname'] = $_POST['firstname'];
    $_SESSION['lastname'] = $_POST['lastname'];
    $_SESSION['age'] = $_POST['age'];
    $_SESSION['email'] = $_POST['email'];
    $_SESSION['contact'] = $_POST['contact'];
    $_SESSION['address'] = $_POST['address'];
    $_SESSION['gender'] = $_POST['gender'];
    $_SESSION['groupp'] = $_POST['groupp'];

    if ($_POST['groupp'] == 'PWD') {
        $_SESSION['impairment'] = $_POST['impairment'];
    }

    // Store profile picture temporarily in session
    if (isset($_FILES['profilepic']) && $_FILES['profilepic']['error'] == 0) {
        $_SESSION['profilepic'] = $_FILES['profilepic']['name'];
        move_uploaded_file($_FILES['profilepic']['tmp_name'], "profilepic/" . $_FILES['profilepic']['name']);
    }
}
?>

<!-- Admin Wrapper -->
<div class="admin-wrapper">

    <!-- Admin Content -->
    <h2 class="form-title heading" style="font-size: 50px;">User Registration - Page 2</h2>

    <div class="auther-content">
        <form action="userupcredentials.php" method="post">
            <div>
                <label class="required-label">
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
                    <span class="required-asterisk">*</span>
                </label>
            </div>

            <div>
                <label class="required-label">
                    <select id="course" name="course" class="text-input" required>
                        <option value="" disabled selected>Select a course</option>
                    </select>
                    <span class="required-asterisk">*</span>
                </label>
            </div>

            <div>
                <label class="required-label">
                    <select name="year" class="text-input" required>
                        <option value="" disabled selected>Select Year</option>
                        <option value="1st">1st</option>
                        <option value="2nd">2nd</option>
                        <option value="3rd">3rd</option>
                        <option value="4th">4th</option>
                        <option value="5th">5th</option>
                    </select>
                    <span class="required-asterisk">*</span>
                </label>
            </div>

            <div>
                <label class="required-label">
                    <select name="section" class="text-input" required>
                        <option value="" disabled selected>Select Section</option>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                        <option value="E">E</option>
                    </select>
                    <span class="required-asterisk">*</span>
                </label>
            </div>

            <div style="display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-big" onclick="location.href='userup.php'">Back</button>
                <button type="submit" name="next" class="btn btn-big">Next</button>
            </div>
        </form>
    </div>

</div>

<!-- CSS -->
<style>
    .required-label {
        position: relative;
        display: block;
        margin-bottom: 20px;
    }

    .required-asterisk {
        color: red;
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 20px;
        display: none;
    }

    .text-input:invalid ~ .required-asterisk {
        display: inline;
    }
</style>

<!-- JS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const department = document.getElementById("department");
        const course = document.getElementById("course");

        const courses = {
            'CCS': ['BS Computer Science', 'BS Information Technology', 'BS Information Systems'],
            'CCJE': ['BS Criminology', 'BS Forensic Science'],
            'CAS': ['BA Psychology', 'BA Political Science'],
            'COE': ['BS Computer Engineering', 'BS Mechanical Engineering'],
            'CBMA': ['BSBA Marketing', 'BSBA Finance', 'BS Office Administration'],
            'CHMT': ['BS Hospitality Management', 'BS Tourism'],
            'CTE': ['BS Education (Secondary Education)', 'BS Education (Primary Education)']
        };

        department.addEventListener('change', function () {
            course.innerHTML = '<option value="" disabled selected>Select a course</option>';
            if (courses[this.value]) {
                courses[this.value].forEach(courseName => {
                    const option = document.createElement('option');
                    option.value = courseName;
                    option.textContent = courseName;
                    course.appendChild(option);
                });
            }
        });

        document.querySelectorAll(".text-input").forEach(input => {
            input.addEventListener("input", function () {
                const asterisk = this.nextElementSibling;
                asterisk.style.display = this.checkValidity() ? "none" : "inline";
            });
        });
    });
</script>

<?php include_once('temp/footer.php') ?>