<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();
?>

<?php include 'includes/header.php'; ?>

<h2>Admin Dashboard</h2>

<div class="row mt-4">
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Notes</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM notes");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?php echo $count; ?></p>
                <a href="manage-notes.php" class="text-white">Manage Notes</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Question Papers</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM question_papers");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?php echo $count; ?></p>
                <a href="manage-papers.php" class="text-white">Manage Papers</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <h5 class="card-title">Syllabus</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM syllabus");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?php echo $count; ?></p>
                <a href="manage-syllabus.php" class="text-white">Manage Syllabus</a>
            </div>
        </div>
    </div>
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-danger">
            <div class="card-body">
                <h5 class="card-title">Videos</h5>
                <?php
                $stmt = $pdo->query("SELECT COUNT(*) FROM tutorial_videos");
                $count = $stmt->fetchColumn();
                ?>
                <p class="card-text display-4"><?php echo $count; ?></p>
                <a href="manage-videos.php" class="text-white">Manage Videos</a>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>