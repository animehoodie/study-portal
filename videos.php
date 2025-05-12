<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4">Tutorial Videos</h2>

<div class="row">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM tutorial_videos ORDER BY subject ASC, title ASC");
        while ($video = $stmt->fetch()) {
            // Extract YouTube video ID from URL
            $video_id = '';
            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video['youtube_url'], $matches)) {
                $video_id = $matches[1];
            }
            
            echo '<div class="col-md-6 mb-4">';
            echo '<div class="card h-100">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($video['title']) . '</h5>';
            echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($video['subject']) . '</h6>';
            
            if ($video_id) {
                echo '<div class="ratio ratio-16x9 mt-3">';
                echo '<iframe src="https://www.youtube.com/embed/' . $video_id . '" frameborder="0" allowfullscreen></iframe>';
                echo '</div>';
            } else {
                echo '<div class="alert alert-warning">Invalid YouTube URL</div>';
            }
            
            if (!empty($video['description'])) {
                echo '<p class="card-text mt-3">' . nl2br(htmlspecialchars($video['description'])) . '</p>';
            }
            
            echo '</div>';
            echo '<div class="card-footer bg-transparent">';
            echo '<a href="' . htmlspecialchars($video['youtube_url']) . '" target="_blank" class="btn btn-primary btn-sm">Watch on YouTube</a>';
            if (isAdmin()) {
                echo '<a href="admin/manage-videos.php?action=delete&id=' . $video['id'] . '" class="btn btn-danger btn-sm ms-2">Delete</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error loading videos: ' . $e->getMessage() . '</div>';
    }
    ?>
</div>

<?php if (isAdmin()): ?>
<div class="mb-4">
    <a href="admin/manage-videos.php" class="btn btn-success">Add New Video</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>