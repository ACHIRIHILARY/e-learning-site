<?php include 'includes/header.php'; ?>
 <style>
        body {
            background: #f4f8fb;
        }
        .header-highlight {
            background: #fff;
            border-radius: 0 0 1.5rem 1.5rem;
            box-shadow: 0 4px 24px 0 rgba(0,123,255,0.07);
            margin-bottom: 2rem;
            padding: 3rem 1rem 2rem 1rem;
            text-align: center;
        }
        .header-highlight h1 {
            font-size: 2.5rem;
            font-weight: 800;
            color: #212529;
            margin-bottom: 1rem;
        }
        .header-highlight p {
            font-size: 1.2rem;
            color: #495057;
            margin-bottom: 1.5rem;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        .header-btn {
            padding: 0.7rem 2.2rem;
            font-size: 1.1rem;
            font-weight: 600;
        }
    </style>


 <!-- Header Section -->
    <div class="header-highlight">
        <h1>Master HTML with Interactive Lessons</h1>
        <p>Unlock your web development potential! Dive into structured lessons, hands-on coding, and engaging quizzes designed for beginners and aspiring professionals.</p>
        <a href="courses/course_view.php" class="btn btn-primary header-btn shadow">Start Learning Now</a>
    </div>


<div class="container mt-5">
    <h1 class="text-center">Learn HTML Easily</h1>
    <p class="lead text-center">Join our courses to master HTML from beginner to expert level.</p>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card">
                <img src="assets/images/html_course.jpg" class="card-img-top" alt="HTML Course">
                <div class="card-body">
                    <h5 class="card-title">HTML Basics</h5>
                    <p class="card-text">Learn the fundamental structure of an HTML document.</p>
                    <a href="courses/course_view.php?id=1" class="btn btn-primary">Start Learning</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>