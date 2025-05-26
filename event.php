<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation Form</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            padding: 40px; /* Increased padding to move form lower */
            background-color: #f8f9fa;
        }
        .container {
            max-width: 900px;
            margin: auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #007bff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #007bff;
            color: white;
        }
        .question {
            text-align: left;
            font-weight: bold;
        }
        .sub-question {
            text-align: left;
            font-weight: normal;
        }
        textarea {
            width: 100%;
            height: 80px;
            padding: 8px;
            margin-top: 10px;
        }
        button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<?php
    session_start();
    include_once('temp/header.php');
    include_once('temp/navigation.php');
    require 'connecting/connect.php';

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['evaluation_event_id'])) {
        echo "<script>alert('Invalid access.'); window.location.href = 'event_list.php';</script>";
        exit();
    }

    $event_id = $_SESSION['evaluation_event_id'];
    $stmt = $conn->prepare("SELECT title FROM events WHERE id = ?");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $stmt->bind_result($event_title);
    $stmt->fetch();
    $stmt->close();
?>

    <div class="container">
        <h2>Seminar/Training Evaluation Form</h2>
        <p style="text-align:center; font-size: 18px;"><strong>Event Title:</strong> <?php echo htmlspecialchars($event_title); ?></p>
        <form action="submit_evaluation.php" method="POST">
            <table>
                <tr>
                    <th class="question">Evaluation Criteria</th>
                    <th>5</th>
                    <th>4</th>
                    <th>3</th>
                    <th>2</th>
                    <th>1</th>
                </tr>
                <tr>
                    <td class="question">I. Content/Paksa ng Seminar/Training</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="sub-question">1. The topics are well organized and systematically discussed.</td>
                    <td><input type="radio" name="organization_1" value="5"></td>
                    <td><input type="radio" name="organization_1" value="4"></td>
                    <td><input type="radio" name="organization_1" value="3"></td>
                    <td><input type="radio" name="organization_1" value="2"></td>
                    <td><input type="radio" name="organization_1" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">2. The topics are relevant and vital to my work/task.</td>
                    <td><input type="radio" name="organization_2" value="5"></td>
                    <td><input type="radio" name="organization_2" value="4"></td>
                    <td><input type="radio" name="organization_2" value="3"></td>
                    <td><input type="radio" name="organization_2" value="2"></td>
                    <td><input type="radio" name="organization_2" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">3. The topics are timely.</td>
                    <td><input type="radio" name="organization_3" value="5"></td>
                    <td><input type="radio" name="organization_3" value="4"></td>
                    <td><input type="radio" name="organization_3" value="3"></td>
                    <td><input type="radio" name="organization_3" value="2"></td>
                    <td><input type="radio" name="organization_3" value="1"></td>
                </tr>
                <tr>
                    <td class="question">II. Materials/Kagamitan Pantakalay</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="sub-question">1. The materials are available and prepared before the activity start.</td>
                    <td><input type="radio" name="materials_1" value="5"></td>
                    <td><input type="radio" name="materials_1" value="4"></td>
                    <td><input type="radio" name="materials_1" value="3"></td>
                    <td><input type="radio" name="materials_1" value="2"></td>
                    <td><input type="radio" name="materials_1" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">2. The visuals and handouts were helpful in facilitating inputs and generating outputs.</td>
                    <td><input type="radio" name="materials_2" value="5"></td>
                    <td><input type="radio" name="materials_2" value="4"></td>
                    <td><input type="radio" name="materials_2" value="3"></td>
                    <td><input type="radio" name="materials_2" value="2"></td>
                    <td><input type="radio" name="materials_2" value="1"></td>
                </tr>

                <tr>
                    <td class="question">III. Resource Person/Tagapagsalita</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="sub-question">1. Knowledgeable and well-versed in the topic.</td>
                    <td><input type="radio" name="speaker_1" value="5"></td>
                    <td><input type="radio" name="speaker_1" value="4"></td>
                    <td><input type="radio" name="speaker_1" value="3"></td>
                    <td><input type="radio" name="speaker_1" value="2"></td>
                    <td><input type="radio" name="speaker_1" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">2. Concepts were clearly discussed.</td>
                    <td><input type="radio" name="speaker_2" value="5"></td>
                    <td><input type="radio" name="speaker_2" value="4"></td>
                    <td><input type="radio" name="speaker_2" value="3"></td>
                    <td><input type="radio" name="speaker_2" value="2"></td>
                    <td><input type="radio" name="speaker_2" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">3. Responsive to questions/issues raised by participants.</td>
                    <td><input type="radio" name="speaker_3" value="5"></td>
                    <td><input type="radio" name="speaker_3" value="4"></td>
                    <td><input type="radio" name="speaker_3" value="3"></td>
                    <td><input type="radio" name="speaker_3" value="2"></td>
                    <td><input type="radio" name="speaker_3" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">4. Well-poised, alert and can hold participants' attention.</td>
                    <td><input type="radio" name="speaker_4" value="5"></td>
                    <td><input type="radio" name="speaker_4" value="4"></td>
                    <td><input type="radio" name="speaker_4" value="3"></td>
                    <td><input type="radio" name="speaker_4" value="2"></td>
                    <td><input type="radio" name="speaker_4" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">5. Motivate the participants to active involve.</td>
                    <td><input type="radio" name="speaker_5" value="5"></td>
                    <td><input type="radio" name="speaker_5" value="4"></td>
                    <td><input type="radio" name="speaker_5" value="3"></td>
                    <td><input type="radio" name="speaker_5" value="2"></td>
                    <td><input type="radio" name="speaker_5" value="1"></td>
                </tr>
                <tr>
                    <td class="question">IV. Overall Evaluation/Pangkalahatang Pagtataya</td>
                    <td colspan="5"></td>
                </tr>
                <tr>
                    <td class="sub-question">1. The entire program is well-organized.</td>
                    <td><input type="radio" name="overall_1" value="5"></td>
                    <td><input type="radio" name="overall_1" value="4"></td>
                    <td><input type="radio" name="overall_1" value="3"></td>
                    <td><input type="radio" name="overall_1" value="2"></td>
                    <td><input type="radio" name="overall_1" value="1"></td>
                </tr>
                <tr>
                    <td class="sub-question">2. The program objectives are attained.</td>
                    <td><input type="radio" name="overall_2" value="5"></td>
                    <td><input type="radio" name="overall_2" value="4"></td>
                    <td><input type="radio" name="overall_2" value="3"></td>
                    <td><input type="radio" name="overall_2" value="2"></td>
                    <td><input type="radio" name="overall_2" value="1"></td>
                </tr>
            </table>
            <div class="form-community">
                <label>Comments and Suggestions:</label>
                <textarea name="comments"></textarea>
            </div>
            <button type="submit">Submit Evaluation</button>
        </form>
    </div>
    <?php include_once('temp/footer.php'); ?>
</body>
</html>