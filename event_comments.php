<?php
session_start();
require 'connecting/connect.php'; // Ensure correct path

// Define how many comments to display per page
$commentsPerPage = 10;

// Get the event_id from the URL (ensure it exists)
$eventId = isset($_GET['event_id']) ? (int)$_GET['event_id'] : 0;

// If no event_id is provided, redirect to the admin event list page
if ($eventId === 0) {
    header("Location: admin_eventlists.php");
    exit();
}

// Get the current page from the URL, default to page 1
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $commentsPerPage; // Calculate the offset for the query

// Fetch event details (title) for the specific event
$query = "
    SELECT e.title
    FROM events e
    WHERE e.id = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->bind_result($eventTitle);
$stmt->fetch();
$stmt->close();

// If no event found, redirect to the admin event list page
if (!$eventTitle) {
    header("Location: admin_eventlists.php");
    exit();
}

// Fetch the total number of comments for the specific event
$commentsCountQuery = "
    SELECT COUNT(*) 
    FROM event_evaluations ee
    WHERE ee.event_id = ?
";
$stmt = $conn->prepare($commentsCountQuery);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->bind_result($totalComments);
$stmt->fetch();
$stmt->close();

// Calculate the total number of pages
$totalPages = ceil($totalComments / $commentsPerPage);

// Fetch comments for the specific event (with pagination)
$commentsQuery = "
    SELECT ee.comments, ee.created_at, u.firstname, u.lastname
    FROM event_evaluations ee
    JOIN users u ON ee.user_id = u.id
    WHERE ee.event_id = ? 
    ORDER BY ee.created_at DESC
    LIMIT ?, ?
";
$stmt = $conn->prepare($commentsQuery);
$stmt->bind_param("iii", $eventId, $offset, $commentsPerPage);
$stmt->execute();
$stmt->bind_result($comments, $created_at, $firstname, $lastname);

$commentsList = [];

// Basic Sentiment Analysis Function
function getSentiment($text) {
    // Simple keyword-based sentiment analysis (expandable)
    $positive_keywords = [
        'good', 'great', 'excellent', 'positive', 'amazing', 'love', 'wonderful', 
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
        'wise', 'worthy', 'youthful', 'zealous', 'zesty'
    ];
    
    
    $negative_keywords = [
        'bad', 'poor', 'hate', 'negative', 'terrible', 'worst', 'awful', 'horrible',
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
        'uncooperative', 'unreasonable', 'alienated', 'angry', 'insensitive', 'unwanted', 'incompetent'
    ];
    
    // Convert text to lowercase
    $text = strtolower($text);
    
    // Check for positive or negative keywords
    foreach ($positive_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return 'positive';
        }
    }
    foreach ($negative_keywords as $keyword) {
        if (strpos($text, $keyword) !== false) {
            return 'negative';
        }
    }
    // Default to neutral if no keywords match
    return 'neutral';
}

while ($stmt->fetch()) {
    // Get sentiment from the text
    $sentiment = getSentiment($comments);
    
    $commentsList[] = [
        'comments' => $comments,
        'created_at' => $created_at,
        'firstname' => $firstname,
        'lastname' => $lastname,
        'sentiment' => $sentiment
    ];
}
$stmt->close();

// Fetch all comments for the sentiment analysis chart (not just the paginated ones)
$allCommentsQuery = "
    SELECT ee.comments
    FROM event_evaluations ee
    WHERE ee.event_id = ?
";
$stmt = $conn->prepare($allCommentsQuery);
$stmt->bind_param("i", $eventId);
$stmt->execute();
$stmt->bind_result($comments);
$allCommentsList = [];

while ($stmt->fetch()) {
    $allCommentsList[] = $comments;
}
$stmt->close();

// Sentiment counts for the full event's comments (for the chart)
$sentimentCounts = ['positive' => 0, 'negative' => 0, 'neutral' => 0];

foreach ($allCommentsList as $commentText) {
    $sentiment = getSentiment($commentText);
    $sentimentCounts[$sentiment]++;
}
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigationold.php'); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Comments - <?php echo htmlspecialchars($eventTitle); ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 0 auto;
            overflow: hidden;
        }
        #main-header {
            background-color: #333;
            color: #fff;
            padding-top: 30px;
            min-height: 70px;
            border-bottom: #0779e4 3px solid;
        }
        #main-header h1 {
            text-align: center;
            text-transform: uppercase;
            margin: 0;
            font-size: 24px;
        }
        #main-footer {
            background: #333;
            color: #fff;
            text-align: center;
            padding: 10px;
            margin-top: 30px;
        }
        .comments-section {
            margin-top: 30px;
        }
        .comment {
            background: #fff;
            padding: 20px;
            margin: 10px 0;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .comment .user-info {
            font-size: 1em;
            font-weight: bold;
            color: #333;
        }
        .comment .created-at {
            font-size: 0.9em;
            color: #888;
        }
        .comment .content {
            margin-top: 10px;
            font-size: 1.1em;
            color: #555;
        }
        .comment .event-title {
            font-weight: bold;
            color: #333;
            font-size: 1.2em;
        }
        .pagination {
            margin-top: 20px;
            text-align: center;
        }
        .pagination a {
            padding: 10px 15px;
            margin: 0 5px;
            background-color: #0779e4;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }
        .pagination a:hover {
            background-color: #055bb5;
        }
        .pagination .current-page {
            background-color: #055bb5;
        }    
    </style>
</head>
<body>

<header id="main-header">
    <div class="container">
        <h1>Comments for Event: <?php echo htmlspecialchars($eventTitle); ?></h1>
    </div>
</header>

<div class="container">
    <div class="comments-section">
        <!-- Display the Event Title -->
        <div class="event-title">
            <h2><?php echo htmlspecialchars($eventTitle); ?></h2>
        </div>
        
        <?php if (!empty($commentsList)): ?>
            <?php foreach ($commentsList as $comment): ?>
            <div class="comment">
                <div class="user-info">
                    <span><?php echo htmlspecialchars($comment['firstname']) . ' ' . htmlspecialchars($comment['lastname']); ?></span>
                </div>
                <div class="created-at">
                    <em>Commented on <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($comment['created_at']))); ?></em>
                </div>
                <div class="content">
                    <?php echo nl2br(htmlspecialchars($comment['comments'])); ?>
                    <p><strong>Sentiment: </strong><?php echo htmlspecialchars($comment['sentiment']); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No comments available for this event.</p>
        <?php endif; ?>

        <!-- Sentiment Analysis Graph -->
        <canvas id="sentimentChart" width="400" height="200"></canvas>
        <script>
            // Sentiment count for the graph
            const sentimentCounts = {
                positive: <?php echo $sentimentCounts['positive']; ?>,
                negative: <?php echo $sentimentCounts['negative']; ?>,
                neutral: <?php echo $sentimentCounts['neutral']; ?>
            };
            
            const ctx = document.getElementById('sentimentChart').getContext('2d');
            const sentimentChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Positive', 'Negative', 'Neutral'],
                    datasets: [{
                        label: 'Sentiment Distribution',
                        data: [sentimentCounts.positive, sentimentCounts.negative, sentimentCounts.neutral],
                        backgroundColor: ['green', 'red', 'orange'],
                        borderColor: ['darkgreen', 'darkred', 'darkgray'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        </script>

        <!-- Pagination Links -->
        <div class="pagination">
            <?php if ($page > 1): ?>
                <a href="event_comments.php?event_id=<?php echo $eventId; ?>&page=<?php echo $page - 1; ?>">Previous</a>
            <?php endif; ?>

            <!-- Page number links -->
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="event_comments.php?event_id=<?php echo $eventId; ?>&page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'current-page' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $totalPages): ?>
                <a href="event_comments.php?event_id=<?php echo $eventId; ?>&page=<?php echo $page + 1; ?>">Next</a>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
