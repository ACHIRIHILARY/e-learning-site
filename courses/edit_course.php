<?php
session_start();
if (!isset($_SESSION["user_id"]) || $_SESSION["user_role"] !== "teacher") {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../config.php';

$course_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$errors = [];
$success = "";

// Fetch course details
$stmt = $conn->prepare("SELECT title, description, url FROM courses WHERE id = ? AND teacher_id = ?");
$stmt->bind_param("ii", $course_id, $_SESSION["user_id"]);
$stmt->execute();
$stmt->bind_result($title, $description, $youtube_url);
if (!$stmt->fetch()) {
    $stmt->close();
    echo '<div class="container py-5"><div class="alert alert-danger">Course not found or you do not have permission to edit this course.</div></div>';
    include_once '../includes/footer.php';
    exit;
}
$stmt->close();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $youtube_url = trim($_POST["youtube_url"]);

    if (empty($title)) $errors[] = "Course title is required.";
    if (empty($description)) $errors[] = "Course description is required.";
    if (empty($youtube_url)) $errors[] = "YouTube URL is required.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE courses SET title = ?, description = ?, url = ? WHERE id = ? AND teacher_id = ?");
        $stmt->bind_param("sssii", $title, $description, $youtube_url, $course_id, $_SESSION["user_id"]);
        if ($stmt->execute()) {
            $success = "Course updated successfully!";
        } else {
            $errors[] = "Failed to update course. Please try again.";
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
                    <h3 class="mb-4 text-center text-primary fw-bold">Edit Course</h3>
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= $success ?></div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Course Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" value="<?= htmlspecialchars($title) ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Course Description</label>
                            <textarea name="description" class="form-control form-control-lg" rows="5" required><?= htmlspecialchars($description) ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">YouTube Video URL</label>
                            <input type="url" name="youtube_url" class="form-control form-control-lg" value="<?= htmlspecialchars($youtube_url) ?>" required>
                        </div>
                        <button type="submit" class="btn btn-warning btn-lg w-100 shadow">Update Course</button>
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