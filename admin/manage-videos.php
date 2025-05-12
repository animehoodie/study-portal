<?php
require_once 'C:/xampp/htdocs/study-portal/includes/config.php';
require_once 'C:/xampp/htdocs/study-portal/includes/auth.php';
redirectIfNotAdmin();

$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM tutorial_videos WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Video deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Error deleting video: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    $youtube_url = trim($_POST['youtube_url']);
    
    if (empty($title) || empty($subject) || empty($youtube_url)) {
        $error = 'Title, Subject, and YouTube URL are required.';
    } elseif (!filter_var($youtube_url, FILTER_VALIDATE_URL)) {
        $error = 'Please enter a valid YouTube URL.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO tutorial_videos (title, subject, description, youtube_url, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subject, $description, $youtube_url, $_SESSION['user_id']]);
            $success = 'Video added successfully.';
        } catch (PDOException $e) {
            $error = 'Error adding video: ' . $e->getMessage();
        }
    }
}

// Fetch all videos
$videos = [];
try {
    $stmt = $pdo->query("SELECT * FROM tutorial_videos ORDER BY subject ASC, title ASC");
    $videos = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching videos: ' . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<h2>Manage Tutorial Videos</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Add New Video</h4>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="youtube_url" class="form-label">YouTube URL</label>
                <input type="url" class="form-control" id="youtube_url" name="youtube_url" required placeholder="https://www.youtube.com/watch?v=...">
            </div>
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Add Video</button>
        </form>
    </div>
</div>

<h4>Existing Videos</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Subject</th>
                <th>URL</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($videos as $video): ?>
            <tr>
                <td><?php echo $video['id']; ?></td>
                <td><?php echo htmlspecialchars($video['title']); ?></td>
                <td><?php echo htmlspecialchars($video['subject']); ?></td>
                <td><a href="<?php echo htmlspecialchars($video['youtube_url']); ?>" target="_blank">View</a></td>
                <td>
                    <?php 
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$video['uploaded_by']]);
                    $user = $stmt->fetch();
                    echo htmlspecialchars($user['username']);
                    ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($video['created_at'])); ?></td>
                <td>
                    <a href="../videos.php" class="btn btn-sm btn-info">View</a>
                    <a href="?action=delete&id=<?php echo $video['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>