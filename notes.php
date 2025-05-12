<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4">Study Notes</h2>

<div class="row">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
        while ($note = $stmt->fetch()) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card h-100">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($note['title']) . '</h5>';
            echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($note['subject']) . '</h6>';
            echo '<p class="card-text">' . substr(htmlspecialchars($note['description']), 0, 100) . '...</p>';
            echo '</div>';
            echo '<div class="card-footer bg-transparent">';
            echo '<a href="download.php?id=' . $note['id'] . '" class="btn btn-primary btn-sm">Download</a>';
            if (isAdmin()) {
                echo '<a href="admin/manage-notes.php?action=delete&id=' . $note['id'] . '" class="btn btn-danger btn-sm ms-2">Delete</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error loading notes: ' . $e->getMessage() . '</div>';
    }
    ?>
</div>

<?php if (isAdmin()): ?>
<div class="mb-4">
    <a href="admin/manage-notes.php" class="btn btn-success">Add New Note</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>