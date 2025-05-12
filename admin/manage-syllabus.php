<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';
redirectIfNotAdmin();

$error = '';
$success = '';

// Handle delete action
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = $_GET['id'];
    try {
        // First get file path to delete the physical file
        $stmt = $pdo->prepare("SELECT file_path FROM syllabus WHERE id = ?");
        $stmt->execute([$id]);
        $file = $stmt->fetch();
        
        if ($file && !empty($file['file_path'])) {
            $file_path = __DIR__ . '/../../study-portal/uploads/syllabus/' . $file['file_path'];
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }
        
        $stmt = $pdo->prepare("DELETE FROM syllabus WHERE id = ?");
        $stmt->execute([$id]);
        $success = 'Syllabus deleted successfully.';
    } catch (PDOException $e) {
        $error = 'Error deleting syllabus: ' . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $subject = trim($_POST['subject']);
    $course = trim($_POST['course']);
    
    // File upload handling
    $file_path = '';
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../../study-portal/uploads/syllabus/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_name = basename($_FILES['file']['name']);
        $file_path = $upload_dir . uniqid() . '_' . $file_name;
        
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            $file_path = str_replace(__DIR__ . '/../../', '', $file_path);
        } else {
            $error = 'Error uploading file.';
        }
    }
    
    if (empty($title) || empty($subject) || empty($course)) {
        $error = 'All fields are required.';
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO syllabus (title, subject, course, file_path, uploaded_by) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$title, $subject, $course, $file_path, $_SESSION['user_id']]);
            $success = 'Syllabus added successfully.';
        } catch (PDOException $e) {
            $error = 'Error adding syllabus: ' . $e->getMessage();
        }
    }
}

// Fetch all syllabus items
$syllabusItems = [];
try {
    $stmt = $pdo->query("SELECT * FROM syllabus ORDER BY course ASC, subject ASC");
    $syllabusItems = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = 'Error fetching syllabus: ' . $e->getMessage();
}
?>

<?php include  'includes/header.php'; ?>

<h2>Manage Syllabus</h2>

<?php if ($error): ?>
<div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
<div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-header">
        <h4>Add New Syllabus</h4>
    </div>
    <div class="card-body">
        <form method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="title" class="form-label">Title*</label>
                <input type="text" class="form-control" id="title" name="title" required>
            </div>
            <div class="mb-3">
                <label for="course" class="form-label">Course*</label>
                <input type="text" class="form-control" id="course" name="course" required>
            </div>
            <div class="mb-3">
                <label for="subject" class="form-label">Subject*</label>
                <input type="text" class="form-control" id="subject" name="subject" required>
            </div>
            <div class="mb-3">
                <label for="file" class="form-label">Upload File (PDF/DOC)</label>
                <input type="file" class="form-control" id="file" name="file" accept=".pdf,.doc,.docx">
            </div>
            <button type="submit" class="btn btn-primary">Add Syllabus</button>
        </form>
    </div>
</div>

<h4>Existing Syllabus Items</h4>
<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Subject</th>
                <th>Course</th>
                <th>Uploaded By</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($syllabusItems as $item): ?>
            <tr>
                <td><?php echo $item['id']; ?></td>
                <td><?php echo htmlspecialchars($item['title']); ?></td>
                <td><?php echo htmlspecialchars($item['subject']); ?></td>
                <td><?php echo htmlspecialchars($item['course']); ?></td>
                <td>
                    <?php 
                    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$item['uploaded_by']]);
                    $user = $stmt->fetch();
                    echo htmlspecialchars($user['username']);
                    ?>
                </td>
                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                <td>
                    <?php if (!empty($item['file_path'])): ?>
                    <a href="download.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-info">Download</a>
                    <?php endif; ?>
                    <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this syllabus item?')">Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'includes/footer.php'; ?>