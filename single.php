<?php
session_start();
require 'connecting/connect.php';

// Get the story ID from the URL (default to 1 if not set)
$story_id = isset($_GET['id']) ? (int)$_GET['id'] : 1;

// Fetch the selected story
$stmt = $conn->prepare("SELECT id, title, writer, date_published, picture, content FROM stories WHERE id = ?");
$stmt->bind_param("i", $story_id);
$stmt->execute();
$stmt->bind_result($id, $title, $writer, $datePublished, $picture, $content);
$stmt->fetch();
$stmt->close();

// Fetch all stories (including the current one)
$allStories = [];
$stmt = $conn->prepare("SELECT id, title, picture FROM stories ORDER BY date_published DESC");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $allStories[] = $row;
}
$stmt->close();
?>

<?php include_once('temp/header.php'); ?>
<?php include_once('temp/navigation.php'); ?>

<title><?php echo htmlspecialchars($title); ?> - Story Page</title>

<!-- Page Wrapper -->
<div class="page-wrapper">
    <div class="content">
        <!-- Main Story Content -->
        <div class="main-content single" id="story-container">
            <h1 class="post-title" id="story-title"><?php echo htmlspecialchars($title); ?></h1>
            <div class="post-meta">
                <p><strong>By:</strong> <span id="story-writer"><?php echo htmlspecialchars($writer); ?></span></p>
                <p><strong>Date Published:</strong> <span id="story-date"><?php echo date("F j, Y", strtotime($datePublished)); ?></span></p>
            </div>

            <?php if (!empty($picture)): ?>
                <div class="story-picture">
                    <img id="story-image" src="images/<?php echo htmlspecialchars($picture); ?>" alt="Story Image">
                </div>
            <?php endif; ?>

            <div class="story-content" id="story-content">
                <?php echo nl2br(htmlspecialchars($content)); ?>
            </div>
        </div>

        <!-- Sidebar: All Stories -->
        <div class="sidebar">
            <h2>All Stories</h2>
            <ul>
                <?php foreach ($allStories as $story): ?>
                    <li class="<?php echo ($story['id'] == $story_id) ? 'current-story' : ''; ?>">
                        <div class="story-link <?php echo ($story['id'] == $story_id) ? 'active' : ''; ?>" data-id="<?php echo $story['id']; ?>">
                            <?php if (!empty($story['picture'])): ?>
                                <img src="images/<?php echo htmlspecialchars($story['picture']); ?>" alt="Story Image">
                            <?php endif; ?>
                            <span><?php echo htmlspecialchars($story['title']); ?><?php echo ($story['id'] == $story_id) ? ' (Currently Viewing)' : ''; ?></span>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>

<?php include_once('temp/footer.php'); ?>

<!-- JavaScript to Handle Story Click -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    function loadStory(storyId, updateHistory = true) {
        fetch(`fetch_story.php?id=${storyId}`)
            .then(response => response.json())
            .then(data => {
                // Update content dynamically
                document.getElementById("story-title").innerText = data.title;
                document.getElementById("story-writer").innerText = data.writer;
                document.getElementById("story-date").innerText = data.datePublished;
                document.getElementById("story-content").innerHTML = data.content;

                // Update image (or remove if none)
                const storyImage = document.getElementById("story-image");
                if (data.picture) {
                    storyImage.src = `images/${data.picture}`;
                    storyImage.style.display = "block";
                } else {
                    storyImage.style.display = "none";
                }

                // Remove active class and "currently viewing" label from all links
                document.querySelectorAll(".story-link").forEach(link => {
                    link.classList.remove("active");
                    link.querySelector("span").innerText = link.querySelector("span").innerText.replace(' (Currently Viewing)', '');
                });

                // Add active class and "currently viewing" label to the clicked story
                const activeStoryLink = document.querySelector(`.story-link[data-id="${storyId}"]`);
                if (activeStoryLink) {
                    activeStoryLink.classList.add("active");
                    activeStoryLink.querySelector("span").innerText += ' (Currently Viewing)';
                }

                // Highlight the currently viewed story in the sidebar
                document.querySelectorAll(".sidebar ul li").forEach(li => li.classList.remove("current-story"));
                document.querySelector(`.story-link[data-id="${storyId}"]`).closest("li").classList.add("current-story");

                // Update URL in browser without reloading
                if (updateHistory) {
                    history.pushState({ storyId: storyId }, '', `?id=${storyId}`);
                }
            })
            .catch(error => console.error("Error fetching story:", error));
    }

    // Handle clicking on a story link
    document.querySelectorAll(".story-link").forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            const storyId = this.getAttribute("data-id");
            loadStory(storyId);
        });
    });

    // Handle back/forward navigation
    window.addEventListener("popstate", function (event) {
        if (event.state && event.state.storyId) {
            loadStory(event.state.storyId, false);
        } else {
            loadStory(<?php echo $story_id; ?>, false);
        }
    });
});
</script>

<!-- CSS Styling -->
<style>
/* Ensure full-page layout */
html, body {
    height: 100%;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
}

/* Main page layout */
.page-wrapper {
    display: flex;
    justify-content: center;
    align-items: flex-start;
    min-height: 100vh;
    padding: 20px;
}

/* Content layout with sidebar */
.content {
    display: flex;
    max-width: 1200px;
    width: 100%;
    gap: 20px;
}

/* Main Story Content */
.main-content.single {
    flex: 3;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
    text-align: center;
}

/* Sidebar */
.sidebar {
    flex: 1;
    background-color: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

/* Sidebar title */
.sidebar h2 {
    font-size: 20px;
    text-align: center;
}

/* Sidebar list */
.sidebar ul {
    list-style: none;
    padding: 0;
}

.sidebar ul li {
    margin-bottom: 10px;
    text-align: center;
}

/* Sidebar story links */
.sidebar ul li a, .story-link {
    display: block;
    padding: 10px;
    background-color: #f8f8f8;
    text-decoration: none;
    color: #333;
    border-radius: 5px;
    transition: 0.3s;
}

/* Sidebar: Hover effect */
.sidebar ul li a:hover, .story-link:hover {
    background-color: #ddd;
}

/* Sidebar: Currently viewed story */
.current-story .story-link {
    background-color: #007bff;
    color: white;
    font-weight: bold;
}

/* Sidebar images */
.sidebar ul li img {
    max-width: 100%;
    height: auto;
    display: block;
    margin: 0 auto 5px;
    border-radius: 5px;
}
</style>