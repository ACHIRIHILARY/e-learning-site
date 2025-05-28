<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "teacher") {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../config.php';

$title = $description = $url = "";
$success = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $url = trim($_POST["url"]);
    $file_path = null;

    // Handle file upload if a file was selected
    if (isset($_FILES['course_file']) && $_FILES['course_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $filename = uniqid() . '_' . basename($_FILES['course_file']['name']);
        $target_file = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['course_file']['tmp_name'], $target_file)) {
            $file_path = $filename;
        } else {
            $errors[] = "Failed to upload file.";
        }
    }

    if (empty($title)) $errors[] = "Course title is required.";
    if (empty($description)) $errors[] = "Course description is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO courses (title, description, url, file, teacher_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssi", $title, $description, $url, $file_path, $_SESSION["user_id"]);
        if ($stmt->execute()) {
            $success = "Course created successfully!";
            $title = $description = $url = "";
        } else {
            $errors[] = "Failed to create course. Please try again.";
        }
        $stmt->close();
    }
}
?>

<?php include_once '../includes/header.php'; ?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-lg border-0">
                <div class="card-body p-4">
                    <h3 class="mb-4 text-center text-primary fw-bold">Create a New Course</h3>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Course Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($title) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Course Description</label>
                            <textarea name="description" class="form-control form-control-lg" rows="5" required><?= htmlspecialchars($description) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Video URL (optional)</label>
                            <input type="url" name="url" class="form-control form-control-lg" value="<?= htmlspecialchars($url) ?>" placeholder="https://www.youtube.com/watch?v=...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Attach File (optional)</label>
                            <input type="file" name="course_file" class="form-control form-control-lg">
                        </div>
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow">Create Course</button>
                    </form>
                    <div class="text-center mt-3">
                        <a href="../dashboard.php" class="text-decoration-none text-info fw-semibold">Back to Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>