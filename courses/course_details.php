<?php
include_once '../config.php';
include_once '../includes/header.php';

if (!isset($_GET['id'])) {
    echo '<div class="container py-5"><div class="alert alert-danger">No course selected.</div></div>';
    include_once '../includes/footer.php';
    exit;
}

$id = intval($_GET['id']);
$sql = "SELECT courses.title, courses.description, courses.url, courses.file, users.name AS teacher
        FROM courses
        JOIN users ON courses.teacher_id = users.id
        WHERE courses.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($title, $description, $url, $file, $teacher);
if ($stmt->fetch()):
?>
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 mb-4">
                <div class="card-body">
                    <h2 class="fw-bold text-primary"><?= htmlspecialchars($title) ?></h2>
                    <h5 class="text-secondary mb-3">By <?= htmlspecialchars($teacher) ?></h5>
                    <p class="mb-4"><?= nl2br(htmlspecialchars($description)) ?></p>
                    <?php if ($url): ?>
                        <div class="mb-3">
                            <?php
                            // Check if it's a YouTube link
                            if (preg_match('/youtu\.be\/([^\?&]+)/', $url, $matches) || preg_match('/youtube\.com.*v=([^\?&]+)/', $url, $matches)) {
                                $video_id = $matches[1];
                                ?>
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/<?= htmlspecialchars($video_id) ?>" allowfullscreen class="rounded"></iframe>
                                </div>
                            <?php
                            } else {
                                // Otherwise, try to play as a direct video file
                                ?>
                                <video class="w-100" controls>
                                    <source src="<?= htmlspecialchars($url) ?>">
                                    Your browser does not support the video tag.
                                </video>
                            <?php } ?>
                            <div class="text-center mt-2">
                                <a href="<?= htmlspecialchars($url) ?>" target="_blank" class="btn btn-info">Open Resource</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">No video or resource available for this course.</div>
                    <?php endif; ?>
                    
                    <?php if (!empty($file)): ?>
                        <div class="mb-3">
                            <a href="../uploads/<?= htmlspecialchars($file) ?>" class="btn btn-outline-secondary" download>Download Attached File</a>
                        </div>
                    <?php endif; ?>
                    
                </div>
            </div>
            <a href="course_view.php" class="btn btn-outline-primary">Back to Courses</a>
              
            <a href="../dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
            
        </div>
    </div>
</div>
<?php
else:
    echo '<div class="container py-5"><div class="alert alert-danger">Course not found.</div></div>';
endif;
$stmt->close();
include_once '../includes/footer.php';
?>