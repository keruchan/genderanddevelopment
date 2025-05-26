<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>
<?php
require 'connecting/connect.php';

// Get selected event ID from query string
$selectedEventId = isset($_GET['event']) ? intval($_GET['event']) : 0;

// Stats
$totalUsers = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$totalRequests = $conn->query("SELECT COUNT(id) AS total FROM requests")->fetch_assoc()['total'];
$totalEvents = $conn->query("SELECT COUNT(id) AS total FROM events")->fetch_assoc()['total'];
$totalPosts = $conn->query("SELECT COUNT(id) AS total FROM stories")->fetch_assoc()['total'];

// Get all event titles for dropdown
$eventOptions = $conn->query("SELECT id, title FROM events ORDER BY title ASC");

// Get comments based on selected event
$comments = [];
if ($selectedEventId > 0) {
    $stmt = $conn->prepare("SELECT ee.comments FROM event_evaluations ee WHERE ee.event_id = ? AND ee.comments IS NOT NULL AND ee.comments != ''");
    $stmt->bind_param("i", $selectedEventId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT comments FROM event_evaluations WHERE comments IS NOT NULL AND comments != ''");
}
while ($row = $result->fetch_assoc()) {
    $comments[] = $row['comments'];
}

// Combine all comments into one string
$allText = strtolower(implode(' ', $comments));

// Remove punctuation
$allText = preg_replace('/[^\w\s]/', '', $allText);

// Define whitelist of words (allowedWords) – trimmed for brevity
$allowedWords = ['good', 'great', 'excellent', 'positive', 'amazing', 'love', 'wonderful', 
        'outstanding', 'fantastic', 'awesome', 'superb', 'terrific', 'marvelous', 
        'happy', 'joyful', 'delightful', 'satisfied', 'content', 'blessed', 'pleased',
        'successful', 'incredible', 'remarkable', 'brilliant', 'inspiring', 'motivating',
        'uplifting', 'cheerful', 'grateful', 'peaceful', 'bright', 'fun', 'beautiful', 
        'favorable', 'outstanding', 'admirable', 'affectionate', 'attractive', 'beneficial',
        'bravo', 'charming', 'classy', 'commendable', 'confident', 'delicious', 'dazzling',
        'dynamic', 'eager', 'energetic', 'enjoyable', 'enthusiastic', 'extraordinary', 
        'fabulous', 'faithful', 'fancy', 'fantastic', 'flawless', 'fortunate', 'friendly',
        'genuine', 'good-hearted', 'gorgeous', 'graceful', 'greatest', 'handsome', 'happy-go-lucky',
        'healthy', 'honorable', 'hopeful', 'impressive', 'incredible', 'indispensable', 'innovative',
        'jovial', 'lively', 'lovable', 'memorable', 'miraculous', 'motivated', 'outstanding', 'precious',
        'radiant', 'respectable', 'robust', 'smooth', 'successful', 'sweet', 'talented', 'thoughtful',
        'triumphant', 'trustworthy', 'upbeat', 'vibrant', 'virtuous', 'visionary', 'wealthy', 'winning',
        'wise', 'worthy', 'youthful', 'zealous', 'zesty',     'bad', 'poor', 'hate', 'negative', 'terrible', 'worst', 'awful', 'horrible',
        'dreadful', 'unpleasant', 'disappointing', 'unfortunate', 'unhappy', 'saddening',
        'depressing', 'miserable', 'pathetic', 'ugly', 'detestable', 'horrendous', 'regretful',
        'nasty', 'disturbing', 'hateful', 'destructive', 'unfortunate', 'terrifying', 'horrible',
        'repulsive', 'disgusting', 'unsatisfactory', 'embarrassing', 'failing', 'tragic', 'damaging',
        'awful', 'unwanted', 'displeasing', 'unworthy', 'despicable', 'irritating', 'inferior',
        'subpar', 'wretched', 'shameful', 'unacceptable', 'terrible', 'disastrous', 'toxic',
        'frustrating', 'depressing', 'annoying', 'agonizing', 'dangerous', 'unreliable', 'untrustworthy',
        'weak', 'cheating', 'failure', 'grief', 'hopeless', 'mournful', 'unpleasant', 'burdened',
        'confused', 'chaotic', 'lousy', 'heartbreaking', 'vile', 'threatening', 'dejected', 'discouraging',
        'sad', 'lonely', 'broken', 'worthless', 'reprehensible', 'untrustworthy', 'selfish', 'unfriendly',
        'unfair', 'unjust', 'stressed', 'disorganized', 'tragic', 'obnoxious', 'displeasing', 'coldhearted',
        'ungrateful', 'rude', 'murderous', 'distraught', 'vindictive', 'unreliable', 'cold', 'unfeeling',
        'hostile', 'malicious', 'unloving', 'evil', 'unmotivated', 'disgusted', 'repelled', 'unethical',
        'uncooperative', 'unreasonable', 'alienated', 'angry', 'insensitive', 'unwanted', 'incompetent',
    'mabuti', 'magaling', 'napakahusay', 'positibo', 'kamangha-mangha', 'pagmamahal', 'kahanga-hanga',
    'natatangi', 'fantastiko', 'astig', 'sobrang ganda', 'bongga', 'masaya', 'maligaya', 'kasiya-siya',
    'kontento', 'pinagpala', 'masiyahin', 'matagumpay', 'kamangha-mangha', 'kapuri-puri', 'maliwanag',
    'inspirasyonal', 'nakakaengganyo', 'nakakataas ng moral', 'masigla', 'mapagpasalamat', 'mapayapa',
    'masarap', 'maganda', 'kaibig-ibig', 'kaaya-aya', 'kapaki-pakinabang', 'galing', 'kaakit-akit',
    'may tiwala sa sarili', 'matapat', 'totoo', 'taos-puso', 'maayos', 'mabait', 'magiliw', 'malikhain',
    'masigasig', 'masayahin', 'matulungin', 'mapagkakatiwalaan', 'matino', 'maalalahanin', 'malusog',
    'kaibig-ibig', 'maabilidad', 'maayos', 'mapagbigay', 'maswerte', 'masigla', 'mapagmahal', 'kagalang-galang',
    'kapuri-puri', 'malapit sa puso', 'inspirado', 'mapag-asa', 'kapanapanabik', 'tagumpay', 'panalo',
    'magiting', 'matalino', 'makabuluhan', 'mapagmahal', 'makatao', 'mapanlikha', 'maluwag ang loob',
    'maalab', 'masarap kasama', 'masinop', 'kaaya-ayang ugali',
    'masama', 'mahina', 'galit', 'negatibo', 'pangit', 'pinakamasama', 'kasuklam-suklam', 'nakakatakot',
    'hindi kanais-nais', 'nakakadismaya', 'malungkot', 'nakakapagpabagabag', 'malubha', 'walang silbi',
    'pangit', 'kasuklam-suklam', 'nakakahiya', 'hindi matagumpay', 'malas', 'masaklap', 'nakakainis',
    'nakakapagod', 'nakakadismaya', 'masalimuot', 'malabo', 'nasisiraan ng loob', 'walang pag-asa',
    'malungkot', 'nasaktan', 'bigong-bigo', 'mapanira', 'hindi tapat', 'mapagkunwari', 'mapang-abuso',
    'masama ang ugali', 'madamot', 'walang pakialam', 'hindi pantay', 'hindi makatarungan', 'baluktot',
    'mapagmataas', 'pikon', 'hindi mapagkakatiwalaan', 'nakakagalit', 'hindi makatao', 'malupit',
    'walang malasakit', 'mapang-api', 'mapanira', 'mapanlinlang', 'mapanghusga', 'walang respeto',
    'walang malasakit', 'masungit', 'walang pakundangan', 'balasubas', 'bugnutin', 'matigas ang ulo',
    'walang puso', 'matigas ang loob', 'tamad', 'maligalig', 'masalimuot', 'maligalig', 'nakakapanlumo',
    'nakakabahala', 'maselan', 'nakakainis', 'nakakagulat', 'mapagsamantala', 'mapag-imbot',
    'madaya', 'palpak', 'mapang-api', 'mapagmataas', 'walang awa', 'mapanira', 'masama ang ugali',
    'mapanakit', 'masakit', 'hindi maayos', 'masalimuot', 'madilim', 'nakakasama ng loob'
];

// Filter words to only allowed words, and length > 2
$words = array_filter(explode(' ', $allText), function ($word) use ($allowedWords) {
    return strlen($word) > 2 && in_array($word, $allowedWords);
});

// Count frequency
$wordFreq = array_count_values($words);
arsort($wordFreq);

// Convert to JS-friendly array
$jsWordList = [];
foreach (array_slice($wordFreq, 0, 100) as $word => $count) {
    $jsWordList[] = "['" . addslashes($word) . "', $count]";
}
$jsWordListString = implode(',', $jsWordList);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard</title>
  <script src="https://cdn.jsdelivr.net/npm/wordcloud@1.1.2/src/wordcloud2.min.js"></script>
  <style>
    .stats, .filter-container, .chart-section { padding: 20px; }
    .stats { display: flex; gap: 20px; flex-wrap: wrap; }
    .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); flex: 1 1 200px; text-align: center; }
    .card h2 { font-size: 28px; margin: 0; }
    .card p { color: gray; margin: 5px 0 0; }
    .chart-container { width: 80%; margin: auto; background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center; }
    canvas { max-width: 100%; height: auto; display: block; margin: 0 auto; }
    select { padding: 10px; font-size: 16px; margin-bottom: 20px; }
    .dashboard-nav {
      display: flex;
      justify-content: center;
      gap: 2rem;
      margin: 1rem auto;
      padding: 1rem;
      background: #d0eaff;
      border-radius: 8px;
      font-size: 1rem;
      font-weight: 600;
    }
    .dashboard-nav a {
      color: #333;
      text-decoration: none;
      padding: 0.5rem 1rem;
      border-radius: 6px;
    }
    .dashboard-nav a.active, .dashboard-nav a:hover {
      background: #4CAF50;
      color: white;
    }
  </style>
</head>
<body>

<section class="stats">
  <div class="card"><h2><?= $totalUsers; ?></h2><p>Total Users</p></div>
  <div class="card"><h2><?= $totalRequests; ?></h2><p>Total Requests</p></div>
  <div class="card"><h2><?= $totalEvents; ?></h2><p>Total Events</p></div>
  <div class="card"><h2><?= $totalPosts; ?></h2><p>Total Posts</p></div>
</section>
<section class="dashboard-nav">
  <a href="admin.php">Summary</a>
  <a href="admindash1.php">Requests/Time</a>
  <a href="admindash2.php"  class="active">Feedback Word Cloud</a>
  <a href="admindash3.php">Ratings/Department</a>
  <a href="admindash4.php">Attendees/Group</a>
</section>
<section class="chart-section">
  <div class="chart-container" id="wordCloudWrapper">
    <h3>Word Cloud from Event Evaluation Comments</h3>

    <!-- Filter dropdown -->
    <form method="get" id="eventFilterForm">
      <label for="event">Filter by Event:</label>
      <select name="event" id="event" onchange="document.getElementById('eventFilterForm').submit();">
        <option value="0">All Events</option>
        <?php while ($event = $eventOptions->fetch_assoc()): ?>
          <option value="<?= $event['id'] ?>" <?= ($selectedEventId == $event['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($event['title']) ?>
          </option>
        <?php endwhile; ?>
      </select>
    </form>

    <canvas id="wordCloudCanvas"></canvas>
  </div>
</section>

<script>
  const wordList = [<?= $jsWordListString ?>];

  function drawWordCloud() {
    const canvas = document.getElementById('wordCloudCanvas');
    const container = document.getElementById('wordCloudWrapper');
    canvas.width = container.offsetWidth - 40;
    canvas.height = 400;

    WordCloud(canvas, {
      list: wordList,
      gridSize: Math.round(16 * canvas.width / 1024),
      weightFactor: function (size) {
        return (canvas.width / 1024) * size * 50;
      },
      fontFamily: 'Arial',
      color: 'random-dark',
      backgroundColor: '#fff',
      rotateRatio: 0.5,
      rotationSteps: 2
    });
  }

  window.addEventListener('load', drawWordCloud);
  window.addEventListener('resize', drawWordCloud);
</script>

</body>
</html>
