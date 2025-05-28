<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: dashboard.php");
    exit;
}

include_once 'config.php';
$user_id = $_SESSION["user_id"];
$user_name = $_SESSION["user_name"];
$user_role = $_SESSION["user_role"];

include_once 'includes/header.php';

// Handle approve/reject group join requests
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["set_group_status"], $_POST["student_id"], $_POST["course_id"])) {
    $student_id = intval($_POST["student_id"]);
    $course_id = intval($_POST["course_id"]);
    $status = $_POST["set_group_status"] === "approve" ? "approved" : "rejected";
    $update = $conn->prepare("UPDATE join_requests SET status = ? WHERE student_id = ? AND course_id = ?");
    $update->bind_param("sii", $status, $student_id, $course_id);
    $update->execute();
    $update->close();
    header("Location: dashboard.php");
    exit;
}
?>

<div class="container py-5">
    <?php if ($user_role === "teacher"): ?>
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold text-primary mb-2">Welcome, <?= htmlspecialchars($user_name) ?>!</h2>
                <h4 class="text-secondary mb-4">Teacher Dashboard</h4>
                <a href="courses/create_course.php" class="btn btn-success btn-lg mb-3 shadow">+ Create New Course</a>
                <a href="chat.php" class="btn btn-primary btn-lg mb-3 shadow ms-2">Open Group Chat</a>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h5 class="fw-semibold mb-3 text-info">Your Courses</h5>
                <?php
                $sql = "SELECT id, title, description, created_at FROM courses WHERE teacher_id = ? ORDER BY created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <?php if ($result->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php while ($course = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #e3f2fd 60%, #fce4ec 100%);">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($course['title']) ?></h5>
                                        <p class="card-text text-dark mb-3" style="min-height: 70px;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                                        <span class="badge bg-success mb-2">Created: <?= date('M d, Y', strtotime($course['created_at'])) ?></span>
                                        <div class="mt-auto mb-2">
                                            <a href="courses/course_view.php?id=<?= $course['id'] ?>" class="btn btn-outline-primary btn-sm w-100 mb-2">View Course</a>
                                            <a href="courses/edit_course.php?id=<?= $course['id'] ?>" class="btn btn-outline-warning btn-sm w-100 mb-2">Edit</a>
                                            <a href="courses/delete_course.php?id=<?= $course['id'] ?>" class="btn btn-outline-danger btn-sm w-100" onclick="return confirm('Are you sure you want to delete this course?');">Delete</a>
                                        </div>
                                        <hr>
                                        <h6 class="fw-semibold text-info">Enrolled Students</h6>
                                        <?php
                                        // Fetch enrolled students for this course
                                        $enroll_sql = "SELECT u.name, u.email FROM enrollments e JOIN users u ON e.student_id = u.id WHERE e.course_id = ?";
                                        $enroll_stmt = $conn->prepare($enroll_sql);
                                        $enroll_stmt->bind_param("i", $course['id']);
                                        $enroll_stmt->execute();
                                        $enroll_result = $enroll_stmt->get_result();
                                        if ($enroll_result->num_rows > 0): ?>
                                            <ul class="list-group list-group-flush">
                                                <?php while ($student = $enroll_result->fetch_assoc()): ?>
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        <span>
                                                            <?= htmlspecialchars($student['name']) ?> <small class="text-muted">(<?= htmlspecialchars($student['email']) ?>)</small>
                                                        </span>
                                                        <span class="badge bg-primary">In Group</span>
                                                    </li>
                                                <?php endwhile; ?>
                                            </ul>
                                        <?php else: ?>
                                            <div class="text-muted small">No students enrolled yet.</div>
                                        <?php endif;
                                        $enroll_stmt->close();
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mt-4">
                        You haven't created any courses yet.
                    </div>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
        </div>

        <?php
        // Fetch all courses for this teacher
        $courses_sql = "SELECT id, title FROM courses WHERE teacher_id = ? ORDER BY created_at DESC";
        $courses_stmt = $conn->prepare($courses_sql);
        $courses_stmt->bind_param("i", $user_id);
        $courses_stmt->execute();
        $courses_result = $courses_stmt->get_result();
        $courses = [];
        while ($row = $courses_result->fetch_assoc()) {
            $courses[] = $row;
        }
        $courses_stmt->close();

        // Fetch all students enrolled in any of the teacher's courses
        $students_sql = "SELECT DISTINCT u.id, u.name, u.email
            FROM enrollments e
            JOIN users u ON e.student_id = u.id
            JOIN courses c ON e.course_id = c.id
            WHERE c.teacher_id = ?
            ORDER BY u.name";
        $students_stmt = $conn->prepare($students_sql);
        $students_stmt->bind_param("i", $user_id);
        $students_stmt->execute();
        $students_result = $students_stmt->get_result();
        $students = [];
        while ($row = $students_result->fetch_assoc()) {
            $students[] = $row;
        }
        $students_stmt->close();
        ?>

        <?php if (count($courses) && count($students)): ?>
            <div class="mt-5">
                <h5 class="fw-semibold mb-3 text-info">Manage Group Memberships</h5>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center">
                        <thead class="table-primary">
                            <tr>
                                <th>Student Name</th>
                                <?php foreach ($courses as $course): ?>
                                    <th><?= htmlspecialchars($course['title']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td class="fw-semibold"><?= htmlspecialchars($student['name']) ?><br>
                                        <small class="text-muted"><?= htmlspecialchars($student['email']) ?></small>
                                    </td>
                                    <?php foreach ($courses as $course): ?>
                                        <td>
                                            <?php
                                            // Check if this student is enrolled in this course
                                            $enrolled_sql = "SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?";
                                            $enrolled_stmt = $conn->prepare($enrolled_sql);
                                            $enrolled_stmt->bind_param("ii", $student['id'], $course['id']);
                                            $enrolled_stmt->execute();
                                            $enrolled_stmt->store_result();
                                            $is_enrolled = $enrolled_stmt->num_rows > 0;
                                            $enrolled_stmt->close();

                                            if ($is_enrolled) {
                                                // Check group status
                                                $group_sql = "SELECT status FROM join_requests WHERE student_id = ? AND course_id = ?";
                                                $group_stmt = $conn->prepare($group_sql);
                                                $group_stmt->bind_param("ii", $student['id'], $course['id']);
                                                $group_stmt->execute();
                                                $group_stmt->bind_result($group_status);
                                                $group_stmt->fetch();
                                                $group_stmt->close();

                                                if ($group_status === 'approved') {
                                                    echo '<span class="badge bg-success">In Group</span>';
                                                    echo '<form method="post" class="d-inline ms-2">
                                                        <input type="hidden" name="student_id" value="'.$student['id'].'">
                                                        <input type="hidden" name="course_id" value="'.$course['id'].'">
                                                        <button type="submit" name="set_group_status" value="reject" class="btn btn-danger btn-sm">Remove</button>
                                                    </form>';
                                                } elseif ($group_status === 'pending') {
                                                    echo '<span class="badge bg-warning text-dark">Pending</span>';
                                                    echo '<form method="post" class="d-inline ms-2">
                                                        <input type="hidden" name="student_id" value="'.$student['id'].'">
                                                        <input type="hidden" name="course_id" value="'.$course['id'].'">
                                                        <button type="submit" name="set_group_status" value="approve" class="btn btn-success btn-sm">Approve</button>
                                                        <button type="submit" name="set_group_status" value="reject" class="btn btn-danger btn-sm ms-1">Reject</button>
                                                    </form>';
                                                } elseif ($group_status === 'rejected') {
                                                    echo '<span class="badge bg-danger">Rejected</span>';
                                                    echo '<form method="post" class="d-inline ms-2">
                                                        <input type="hidden" name="student_id" value="'.$student['id'].'">
                                                        <input type="hidden" name="course_id" value="'.$course['id'].'">
                                                        <button type="submit" name="set_group_status" value="approve" class="btn btn-success btn-sm">Approve</button>
                                                    </form>';
                                                } else {
                                                    echo '<span class="badge bg-secondary">Not Requested</span>';
                                                }
                                            } else {
                                                echo '<span class="text-muted">Not Enrolled</span>';
                                            }
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="row mb-4">
            <div class="col-12 text-center">
                <h2 class="fw-bold text-primary mb-2">Welcome, <?= htmlspecialchars($user_name) ?>!</h2>
                <h4 class="text-secondary mb-4">Student Dashboard</h4>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                <h5 class="fw-semibold mb-3 text-info">Available Courses</h5>
                <?php
                // Handle enroll action
                if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["enroll_course_id"])) {
                    $course_id = intval($_POST["enroll_course_id"]);
                    // Enroll student if not already enrolled
                    $check = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND course_id = ?");
                    $check->bind_param("ii", $user_id, $course_id);
                    $check->execute();
                    $check->store_result();
                    if ($check->num_rows == 0) {
                        $enroll = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
                        $enroll->bind_param("ii", $user_id, $course_id);
                        $enroll->execute();
                        $enroll->close();
                        // Send group join request (if not already pending/approved)
                        $group_check = $conn->prepare("SELECT id FROM join_requests WHERE student_id = ? AND course_id = ? AND status IN ('pending','approved')");
                        $group_check->bind_param("ii", $user_id, $course_id);
                        $group_check->execute();
                        $group_check->store_result();
                        if ($group_check->num_rows == 0) {
                            $group_req = $conn->prepare("INSERT INTO join_requests (student_id, course_id, status) VALUES (?, ?, 'pending')");
                            $group_req->bind_param("ii", $user_id, $course_id);
                            $group_req->execute();
                            $group_req->close();
                        }
                        $group_check->close();
                        echo "<script>location.reload();</script>";
                    }
                    $check->close();
                }

                // Fetch all courses and enrollment/group status for this student
                $sql = "SELECT c.id, c.title, c.description, u.name AS teacher, c.created_at,
                            e.id AS enrolled,
                            jr.status AS group_status
                        FROM courses c
                        JOIN users u ON c.teacher_id = u.id
                        LEFT JOIN enrollments e ON e.course_id = c.id AND e.student_id = ?
                        LEFT JOIN join_requests jr ON jr.course_id = c.id AND jr.student_id = ?
                        ORDER BY c.created_at DESC";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ii", $user_id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                ?>
                <?php if ($result->num_rows > 0): ?>
                    <div class="row g-4">
                        <?php while ($course = $result->fetch_assoc()): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card border-0 shadow-lg h-100" style="background: linear-gradient(135deg, #fffde4 60%, #e1f5fe 100%);">
                                    <div class="card-body d-flex flex-column">
                                        <h5 class="card-title fw-bold text-primary"><?= htmlspecialchars($course['title']) ?></h5>
                                        <h6 class="card-subtitle mb-2 text-secondary">By <?= htmlspecialchars($course['teacher']) ?></h6>
                                        <p class="card-text text-dark mb-3" style="min-height: 70px;"><?= nl2br(htmlspecialchars($course['description'])) ?></p>
                                        <?php if ($course['enrolled']): ?>
                                            <span class="badge bg-success mb-2">Enrolled</span>
                                            <div class="mt-auto mb-2">
                                                <a href="courses/course_view.php?id=<?= $course['id'] ?>" class="btn btn-outline-primary btn-sm w-100">Continue Course</a>
                                            </div>
                                            <?php if ($course['group_status'] === 'approved'): ?>
                                                <a href="chat.php" class="btn btn-primary btn-sm w-100">Open Group Chat</a>
                                            <?php elseif ($course['group_status'] === 'pending'): ?>
                                                <button class="btn btn-secondary btn-sm w-100" disabled>Group Join Pending</button>
                                            <?php elseif ($course['group_status'] === 'rejected'): ?>
                                                <button class="btn btn-danger btn-sm w-100" disabled>Group Join Rejected</button>
                                            <?php else: ?>
                                                <form method="post" class="mt-2">
                                                    <input type="hidden" name="enroll_course_id" value="<?= $course['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm w-100">Request Group Join</button>
                                                </form>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <form method="post" class="mt-auto">
                                                <input type="hidden" name="enroll_course_id" value="<?= $course['id'] ?>">
                                                <button type="submit" class="btn btn-success btn-sm w-100">Enroll &amp; Start Course</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info text-center mt-4">
                        You are not enrolled in any courses yet.
                    </div>
                <?php endif; ?>
                <?php $stmt->close(); ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include_once 'includes/footer.php'; ?>