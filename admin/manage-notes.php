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
        $stmt = $pdo->prepare("DELETE FROM notes WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Note deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Error deleting note: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $description = trim($_POST['description']);
    
    // File upload handling
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../../study-portal/uploads/notes/';
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
    
    if (empty($title) || empty($subject)) {
        $error = 'Title and Subject are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO notes (title, subject, description, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subject, $description, $file_path, $_SESSION['user_id']]);
            $success = 'Note added successfully.';
        } catch (PDOException $e) {
            $error = 'Error adding note: ' . $e->getMessage();
        }
    }
}

// Fetch all notes
$notes = [];
try {
    $stmt = $pdo->query("SELECT * FROM notes ORDER BY created_at DESC");
    $notes = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching notes: ' . $e->getMessage();
}
?>

<?php include 'includes/header.php'; ?>

<h2>Manage Study Notes</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Add New Note</h4>
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
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
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
            <?php foreach ($notes as $note): ?>
            <tr>
                <td><?php echo $note['id']; ?></td>
                <td><?php echo htmlspecialchars($note['title']); ?></td>
                <td><?php echo htmlspecialchars($note['subject']); ?></td>
                <td>
                    <?php 
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$note['uploaded_by']]);
                    $user = $stmt->fetch();
                    echo htmlspecialchars($user['username']);
                    ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($note['created_at'])); ?></td>
                <td>
                    <a href="../notes.php" class="btn btn-sm btn-info">View</a>
                    <a href="../download.php?id=<?php echo $note['id']; ?>" class="btn btn-sm btn-info">Download</a>
                    <a href="?action=delete&id=<?php echo $note['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>