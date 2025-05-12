<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
redirectIfNotAdmin();

$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // First get file path to delete the physical file
        $stmt = $pdo->prepare("SELECT file_path FROM question_papers WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if ($file && !empty($file['file_path'])) {
            $file_path = '../' . $file['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM question_papers WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Question paper deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Error deleting question paper: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $year = trim($_POST['year']);
    
    // File upload handling
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/papers/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['file']['name']);
        $file_path = $upload_dir . uniqid() . '_' . $file_name;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $file_path = str_replace('../../', '', $file_path);
        } else {
            $error = 'Error uploading file.';
        }
    }
    
    if (empty($title) || empty($subject) || empty($year)) {
        $error = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO question_papers (title, subject, year, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subject, $year, $file_path, $_SESSION['user_id']]);
            $success = 'Question paper added successfully.';
        } catch (PDOException $e) {
            $error = 'Error adding question paper: ' . $e->getMessage();
        }
    }
}

// Fetch all papers
$papers = [];
try {
    $stmt = $pdo->query("SELECT * FROM question_papers ORDER BY year DESC, subject ASC");
    $papers = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching question papers: ' . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Add New Question papers</q></h4>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="year" class="form-label">year</label>
                <textarea class="form-control" id="year" name="year" ></textarea>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Upload File (PDF/DOC)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx">
            </div>
            <button type="submit" class="btn btn-primary">Add Note</button>
        </form>
    </div>
</div>

<h4>Existing Notes</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Subject</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($papers as $papers): ?>
            <tr>
                <td><?php echo $papers['id']; ?></td>
                <td><?php echo htmlspecialchars($papers['title']); ?></td>
                <td><?php echo htmlspecialchars($papers['subject']); ?></td>
                <td>
                    <?php 
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$papers['uploaded_by']]);
                    $user = $stmt->fetch();
                    echo htmlspecialchars($user['username']);
                    ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($papers['created_at'])); ?></td>
                <td>
                    <a href="../papers.php" class="btn btn-sm btn-info">View</a>
                    <a href="?action=delete&id=<?php echo $papers['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>


<!-- Similar form and table structure as manage-notes.php -->
<!-- Adapt fields for question papers (title, subject, year, file upload) -->
<!-- Follow the same pattern as the notes management page -->

<?php include 'includes/footer.php'; ?>