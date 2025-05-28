<?php
include_once '../config.php';

// Fetch all courses with teacher name
$sql = "SELECT courses.id, courses.title, courses.description, users.name AS teacher, courses.created_at
        FROM courses
        JOIN users ON courses.teacher_id = users.id
        ORDER BY courses.created_at DESC";
$result = mysqli_query($conn, $sql);
?>

<?php include_once '../includes/header.php'; ?>

<div class="container py-5">
    <h2 class="mb-4 text-center fw-bold text-primary">Available Courses</h2>
    <div class="row g-4">
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-lg border-0 h-100" style="background: linear-gradient(135deg, #e3f2fd 60%, #fce4ec 100%);">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title fw-bold text-info mb-2"><?= htmlspecialchars($row['title']) ?></h5>
                            <h6 class="card-subtitle mb-2 text-secondary">By <span class="fw-semibold"><?= htmlspecialchars($row['teacher']) ?></span></h6>
                            <p class="card-text text-dark mb-3" style="min-height: 80px;"><?= nl2br(htmlspecialchars($row['description'])) ?></p>
                            <div class="mt-auto">
                                <span class="badge bg-success mb-2">Created: <?= date('M d, Y', strtotime($row['created_at'])) ?></span><br>
                                <a href="course_details.php?id=<?= $row['id'] ?>" class="btn btn-primary w-100 shadow-sm">View Course</a>
                            </div>
                        </div>
                    </div>
                </div>
                <a href="../dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    No courses available at the moment. Please check back later!
                </div>
                <a href="../dashboard.php" class="btn btn-outline-primary">Back to Dashboard</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>