<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4">Question Papers</h2>

<div class="row">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM question_papers ORDER BY year DESC, subject ASC");
        while ($paper = $stmt->fetch()) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card h-100">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($paper['title']) . '</h5>';
            echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($paper['subject']) . ' (' . htmlspecialchars($paper['year']) . ')</h6>';
            echo '</div>';
            echo '<div class="card-footer bg-transparent">';
            echo '<a href="papers_down.php?id=' . $paper['id'] . '" class="btn btn-primary btn-sm">Download</a>';
            if (isAdmin()) {
                echo '<a href="admin/manage-papers.php?action=delete&id=' . $paper['id'] . '" class="btn btn-danger btn-sm ms-2">Delete</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error loading question papers: ' . $e->getMessage() . '</div>';
    }
    ?>
</div>

<?php if (isAdmin()): ?>
<div class="mb-4">
    <a href="admin/manage-papers.php" class="btn btn-success">Add New Paper</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>