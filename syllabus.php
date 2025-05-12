<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
?>

<?php include 'includes/header.php'; ?>

<h2 class="mb-4">Course Syllabus</h2>

<div class="row">
    <?php
    try {
        $stmt = $pdo->query("SELECT * FROM syllabus ORDER BY course ASC, title ASC");
        while ($syllabus = $stmt->fetch()) {
            echo '<div class="col-md-4 mb-4">';
            echo '<div class="card h-100">';
            echo '<div class="card-body">';
            echo '<h5 class="card-title">' . htmlspecialchars($syllabus['title']) . '</h5>';
            echo '<h6 class="card-subtitle mb-2 text-muted">' . htmlspecialchars($syllabus['course']) . '</h6>';
            echo '</div>';
            echo '<div class="card-footer bg-transparent">';
            echo '<a href="syll_down.php?id=' . $syllabus['id'] . '" class="btn btn-primary btn-sm">Download</a>';
            if (isAdmin()) {
                echo '<a href="admin/manage-syllabus.php?action=delete&id=' . $syllabus['id'] . '" class="btn btn-danger btn-sm ms-2">Delete</a>';
            }
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    } catch (PDOException $e) {
        echo '<div class="alert alert-danger">Error loading syllabus: ' . $e->getMessage() . '</div>';
    }
    ?>
</div>

<?php if (isAdmin()): ?>
<div class="mb-4">
    <a href="admin/manage-syllabus.php" class="btn btn-success">Add New Syllabus</a>
</div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>