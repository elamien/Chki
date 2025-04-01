<?php
require_once 'db_connect.php';
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if assignment ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$assignmentId = (int)$_GET['id'];

// Initialize variables
$title = $description = $course = '';
$dueDate = date('Y-m-d');
$dueTime = '23:59';
$priority = 'medium';
$errors = array();

// Get assignment data
$pdo = getDbConnection();
if ($pdo) {
    try {
        // First, check if this assignment belongs to the logged-in user
        $stmt = $pdo->prepare("
            SELECT * FROM assignments 
            WHERE assignment_id = ? AND user_id = ?
        ");
        $stmt->execute([$assignmentId, $_SESSION['user_id']]);
        $assignment = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$assignment) {
            // If no matching assignment, redirect to index
            header("Location: index.php");
            exit();
        }
        
        // Set form values from database
        $title = $assignment['title'];
        $description = $assignment['description'];
        $course = $assignment['course'];
        
        // Parse the datetime for date and time inputs
        $dueDateObj = new DateTime($assignment['due_date']);
        $dueDate = $dueDateObj->format('Y-m-d');
        $dueTime = $dueDateObj->format('H:i');
        
        $priority = $assignment['priority'];
        
    } catch (PDOException $e) {
        $errors['general'] = "Database error: " . $e->getMessage();
    }
} else {
    $errors['general'] = "Could not connect to the database";
    header("Location: index.php");
    exit();
}

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get and sanitize form data
    $title = trim(filter_input(INPUT_POST, 'assignmentTitle', FILTER_SANITIZE_STRING));
    $description = trim(filter_input(INPUT_POST, 'assignmentDescription', FILTER_SANITIZE_STRING));
    $course = $_POST['courseSelect'] ?? '';
    $dueDate = $_POST['dueDate'] ?? '';
    $dueTime = $_POST['dueTime'] ?? '23:59';
    $priority = $_POST['priorityLevel'] ?? 'medium';
    $emailReminder = isset($_POST['emailReminder']) ? 1 : 0;
    
    // Validate title
    if (empty($title)) {
        $errors['title'] = "Assignment title is required";
    }
    
    // Validate course
    if (empty($course)) {
        $errors['course'] = "Please select a course";
    }
    
    // Validate due date
    if (empty($dueDate)) {
        $errors['dueDate'] = "Due date is required";
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
        $errors['dueDate'] = "Due date must be in YYYY-MM-DD format";
    }
    
    // Combine date and time for database
    $dueDatetime = $dueDate . ' ' . $dueTime;
    
    // If no validation errors, proceed with updating the database
    if (empty($errors)) {
        $pdo = getDbConnection();
        
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    UPDATE assignments 
                    SET title = ?, description = ?, course = ?, due_date = ?, priority = ?
                    WHERE assignment_id = ? AND user_id = ?
                ");
                
                $success = $stmt->execute([
                    $title,
                    $description,
                    $course,
                    $dueDatetime,
                    $priority,
                    $assignmentId,
                    $_SESSION['user_id']
                ]);
                
                if ($success) {
                    // Redirect to home page
                    header("Location: index.php");
                    exit();
                } else {
                    $errors['general'] = "Failed to update assignment";
                }
            } catch (PDOException $e) {
                $errors['general'] = "Database error: " . $e->getMessage();
            }
        } else {
            $errors['general'] = "Could not connect to the database";
        }
    }
}

// Get user's courses for dropdown
$courses = array();
$pdo = getDbConnection();

if ($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT DISTINCT course FROM assignments 
            WHERE user_id = ?
            ORDER BY course
        ");
        $stmt->execute([$_SESSION['user_id']]);
        
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $courses[] = $row['course'];
        }
    } catch (PDOException $e) {
        // Silently continue with default courses
    }
}

// Default courses if none are found
if (empty($courses)) {
    $courses = array('CS4640', 'CS4710', 'CS4750', 'CS4414');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="authors" content="Ahmed Elamin">
    <title>Edit Assignment - Chki</title>
    <link rel="stylesheet" href="styles.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Navigation Bar -->
        <nav class="main-nav">
            <div class="nav-section left">
                <a href="index.php" class="logo">Chki</a>
            </div>
            <div class="nav-section middle">
                <a href="index.php" class="nav-link">
                    <i class="fas fa-home"></i> Home
                </a>
            </div>
            <div class="nav-section right">
                <a href="add-assignment.php" class="btn add-btn" aria-label="Add assignment">
                    <i class="fas fa-plus"></i>
                </a>
                <a href="settings.html" class="btn user-btn" aria-label="Account settings">
                    <i class="fas fa-user"></i>
                </a>
                <button id="toggleForum" class="btn forum-btn" aria-label="Toggle forum visibility">
                    <i class="fas fa-comments"></i>
                </button>
                <a href="logout.php" class="btn logout-btn" aria-label="Log out">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </nav>

        <!-- Main Content -->
        <main class="content-area">
            <div class="form-container">
                <h1>Edit Assignment</h1>
                
                <?php if (isset($errors['general'])): ?>
                    <div class="alert alert-danger"><?php echo $errors['general']; ?></div>
                <?php endif; ?>

                <form id="assignmentForm" class="assignment-form" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) . '?id=' . $assignmentId; ?>">
                    <div class="form-group">
                        <label for="assignmentTitle">Assignment Title *</label>
                        <input type="text" id="assignmentTitle" name="assignmentTitle"
                               value="<?php echo htmlspecialchars($title); ?>"
                               placeholder="Type assignment title here"
                               required
                               autocomplete="off"
                               class="form-control <?php echo isset($errors['title']) ? 'is-invalid' : ''; ?>"
                               aria-required="true">
                        <?php if (isset($errors['title'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['title']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="dueDate">Due Date *</label>
                            <input type="date" id="dueDate" name="dueDate"
                                   value="<?php echo htmlspecialchars($dueDate); ?>"
                                   required
                                   class="form-control <?php echo isset($errors['dueDate']) ? 'is-invalid' : ''; ?>"
                                   aria-required="true">
                            <?php if (isset($errors['dueDate'])): ?>
                                <div class="invalid-feedback"><?php echo $errors['dueDate']; ?></div>
                            <?php endif; ?>
                        </div>

                        <div class="form-group">
                            <label for="dueTime">Due Time</label>
                            <input type="time" id="dueTime" name="dueTime"
                                   value="<?php echo htmlspecialchars($dueTime); ?>"
                                   class="form-control">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="courseSelect">Course *</label>
                        <select id="courseSelect" name="courseSelect"
                                required
                                class="form-control <?php echo isset($errors['course']) ? 'is-invalid' : ''; ?>"
                                aria-required="true">
                            <option value="" disabled>Select a course</option>
                            
                            <?php foreach ($courses as $courseOption): ?>
                                <option value="<?php echo htmlspecialchars($courseOption); ?>" 
                                        <?php echo $course === $courseOption ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($courseOption); ?>
                                </option>
                            <?php endforeach; ?>
                            
                            <option value="add">+ Add New Course</option>
                        </select>
                        <?php if (isset($errors['course'])): ?>
                            <div class="invalid-feedback"><?php echo $errors['course']; ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="assignmentDescription">Description / Notes</label>
                        <textarea id="assignmentDescription"
                                  name="assignmentDescription"
                                  placeholder="Add any details, requirements, or notes about this assignment"
                                  class="form-control"
                                  rows="4"><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="priorityLevel">Priority Level</label>
                        <div class="priority-selector">
                            <input type="radio" id="priorityLow" name="priorityLevel" value="low"
                                   <?php echo $priority === 'low' ? 'checked' : ''; ?>>
                            <label for="priorityLow" class="priority-label low">Low</label>

                            <input type="radio" id="priorityMedium" name="priorityLevel" value="medium"
                                   <?php echo $priority === 'medium' ? 'checked' : ''; ?>>
                            <label for="priorityMedium" class="priority-label medium">Medium</label>

                            <input type="radio" id="priorityHigh" name="priorityLevel" value="high"
                                   <?php echo $priority === 'high' ? 'checked' : ''; ?>>
                            <label for="priorityHigh" class="priority-label high">High</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="checkbox-container">
                            <input type="checkbox" id="emailReminder" name="emailReminder" checked>
                            <label for="emailReminder">Send me email reminders</label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="index.php" class="btn cancel-btn">Cancel</a>
                        <button type="submit" class="btn save-btn">Update Assignment</button>
                    </div>
                </form>
            </div>
        </main>

        <!-- Forum Panel (Hidden by default) -->
        <aside id="forumPanel" class="forum-panel">
            <h2>Class Forums</h2>
            <div class="forum-list">
                <a href="forum.html" class="forum-item">
                    <span class="course">CS4640</span>
                    <span class="post-count">12 new posts</span>
                </a>
                <a href="forum.html" class="forum-item">
                    <span class="course">CS4710</span>
                    <span class="post-count">5 new posts</span>
                </a>
                <a href="forum.html" class="forum-item">
                    <span class="course">CS4750</span>
                    <span class="post-count">3 new posts</span>
                </a>
            </div>
        </aside>
    </div>

    <script>
        // Simple toggle for forum panel
        document.getElementById('toggleForum').addEventListener('click', function() {
            const forumPanel = document.getElementById('forumPanel');
            forumPanel.classList.toggle('active');
        });
        
        // Handle "Add New Course" option
        document.getElementById('courseSelect').addEventListener('change', function() {
            if (this.value === 'add') {
                const newCourse = prompt('Enter the name of the new course:');
                if (newCourse && newCourse.trim() !== '') {
                    // Create new option
                    const option = document.createElement('option');
                    option.value = newCourse.trim();
                    option.text = newCourse.trim();
                    
                    // Insert before the "Add New Course" option
                    this.insertBefore(option, this.options[this.options.length - 1]);
                    
                    // Select the new option
                    this.value = newCourse.trim();
                } else {
                    // If canceled or empty, revert to previous selection
                    this.value = "<?php echo htmlspecialchars($course); ?>";
                }
            }
        });
    </script>
</body>
</html> 