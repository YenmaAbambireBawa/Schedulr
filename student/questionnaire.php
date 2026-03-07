<?php
/**
 * Student Questionnaire
 * Collects additional information from newly registered students
 */

require_once __DIR__ . '/../middleware/Auth.php';
require_once __DIR__ . '/../config/database.php';

// Require student authentication
Auth::requireStudent();

// Get current user data
$user = Auth::user();

// Get current year
$current_year = date('Y');

// Initialize courses array grouped by department
$courses_by_dept = [];
$all_courses = [];

try {
    // Create database connection using the Database class
    $database = new Database();
    $pdo = $database->getConnection();

    if ($pdo === null) {
        throw new Exception("Failed to establish database connection");
    }

    // Fetch all courses with department information
    $stmt = $pdo->prepare("
        SELECT c.course_code, c.course_name, d.dept_name, d.dept_code
        FROM courses c
        JOIN departments d ON c.dept_id = d.dept_id
        ORDER BY d.dept_name ASC, c.course_code ASC
    ");
    $stmt->execute();

    // Store courses grouped by department
    while ($row = $stmt->fetch()) {
        $dept_name = $row['dept_name'];
        if (!isset($courses_by_dept[$dept_name])) {
            $courses_by_dept[$dept_name] = [];
        }
        $courses_by_dept[$dept_name][] = [
            'code' => $row['course_code'],
            'name' => $row['course_name']
        ];
        // Also store in flat array for backward compatibility
        $all_courses[$row['course_code']] = $row['course_name'];
    }

} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("Database Error in questionnaire.php: " . $e->getMessage());
    die("
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; background: #fee; border: 1px solid #fcc; border-radius: 8px;'>
            <h2 style='color: #c00;'>Database Connection Error</h2>
            <p>Unable to connect to the database. Please ensure:</p>
            <ul>
                <li>MySQL server is running (Start XAMPP)</li>
                <li>Database 'schedulr_db' exists</li>
                <li>Database credentials are correct in config/database.php</li>
            </ul>
            <p><strong>Error:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
        </div>
    ");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Your Profile - Schedulr</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #dc2626;
            --primary-dark: #b91c1c;
            --primary-light: #ef4444;
            --accent: #991b1b;
            --dark: #1a1a1a;
            --dark-soft: #2d2d2d;
            --gray: #6b7280;
            --light: #f9fafb;
            --white: #ffffff;
            --success: #10b981;
            --error: #ef4444;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2d2d2d 50%, #1a1a1a 100%);
            min-height: 100vh;
            color: #f9fafb;
            padding: 20px;
        }

        /* Animated background */
        .bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .floating-shape {
            position: absolute;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            opacity: 0.03;
            border-radius: 50%;
            animation: float 20s infinite ease-in-out;
        }

        .shape-1 {
            width: 600px;
            height: 600px;
            top: -200px;
            left: -200px;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            bottom: -100px;
            right: -100px;
            animation-delay: 5s;
        }

        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -30px) scale(1.05); }
            50% { transform: translate(-20px, 20px) scale(0.95); }
            75% { transform: translate(20px, 30px) scale(1.02); }
        }

        .container {
            position: relative;
            z-index: 1;
            max-width: 900px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo-container {
            display: inline-flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            text-decoration: none;
        }

        .logo {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 24px;
            color: white;
        }

        .brand-name {
            font-size: 32px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--white), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-message {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--white);
        }

        .subtitle {
            color: var(--gray);
            font-size: 16px;
        }

        .form-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--white);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-number {
            width: 32px;
            height: 32px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: var(--light);
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .required {
            color: var(--primary-light);
        }

        .form-input, .form-select, .form-textarea {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: var(--white);
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        /* Black dropdown text color as per requirement #3 */
        .form-select option {
            background: #ffffff;
            color: #000000;
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        .form-input::placeholder, .form-textarea::placeholder {
            color: var(--gray);
        }

        .form-input:focus, .form-select:focus, .form-textarea:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.15);
        }

        .form-select {
            cursor: pointer;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 12px;
            max-height: 500px;
            overflow-y: auto;
            padding: 10px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 10px;
        }

        .dept-section {
            margin-bottom: 20px;
        }

        .dept-header {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary-light);
            margin-bottom: 12px;
            padding: 10px 12px;
            background: rgba(220, 38, 38, 0.1);
            border-left: 4px solid var(--primary);
            border-radius: 6px;
            position: sticky;
            top: 0;
            z-index: 10;
            backdrop-filter: blur(10px);
        }

        .checkbox-option {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .checkbox-option:hover {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--primary);
        }

        .checkbox-option input {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: var(--primary);
        }

        .checkbox-option label {
            color: var(--light);
            font-size: 14px;
            cursor: pointer;
            flex: 1;
        }

        .btn {
            width: 100%;
            padding: 16px 24px;
            border: none;
            border-radius: 12px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 30px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--white);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.3);
        }

        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(220, 38, 38, 0.5);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .message {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
            display: none;
        }

        .message.success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .message.error {
            background: rgba(239, 68, 68, 0.15);
            color: var(--error);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .message.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            color: #93c5fd;
            font-size: 14px;
        }

        .course-count {
            color: var(--gray);
            font-size: 13px;
            margin-top: 5px;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-card {
                padding: 30px 20px;
            }

            .checkbox-group {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="bg-decoration">
        <div class="floating-shape shape-1"></div>
        <div class="floating-shape shape-2"></div>
    </div>

    <div class="container">
        <div class="header">
            <a href="/schedulr/index.html" class="logo-container">
                <div class="logo">S</div>
                <div class="brand-name">Schedulr</div>
            </a>
            <h1 class="welcome-message">Welcome, <?php echo htmlspecialchars($user['name']); ?>! </h1>
            <p class="subtitle">Let's complete your profile</p>
        </div>

        <div class="form-card">
            <div id="message" class="message"></div>

            <form id="questionnaireForm">
                <h2 class="section-title">
                    <span class="section-number">S</span>
                    Academic Information
                </h2>

                <div class="info-box">
                     Academic Year: <?php echo $current_year; ?>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="program">
                            Program/Major <span class="required">*</span>
                        </label>
                        <select id="program" class="form-select" required>
                            <option value="">Select your program</option>
                            <option value="Computer Science">Computer Science</option>
                            <option value="Management Information Systems">Management Information Systems</option>
                            <option value="Business Administration">Business Administration</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="year">
                            Current Year <span class="required">*</span>
                        </label>
                        <select id="year" class="form-select" required>
                            <option value="">Select year</option>
                            <option value="1">Year 1 (Freshman)</option>
                            <option value="2">Year 2 (Sophomore)</option>
                            <option value="3">Year 3 (Junior)</option>
                            <option value="4">Year 4 (Senior)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="gpa">
                        Current GPA <span class="required">*</span>
                    </label>
                    <input 
                        type="number" 
                        id="gpa" 
                        class="form-input" 
                        placeholder="e.g., 3.5" 
                        step="0.01"
                        min="0"
                        max="4"
                        required
                    />
                </div>

                <div class="form-group">
                    <label class="form-label">
                        Completed Courses <span class="required">*</span>
                    </label>
                    <p style="color: var(--gray); font-size: 13px; margin-bottom: 10px;">
                        Select all courses you have completed across all departments
                    </p>
                    <p class="course-count">
                        Total courses available: <?php echo count($all_courses); ?>
                    </p>
                    <div class="checkbox-group" id="coursesGroup">
                        <?php foreach ($courses_by_dept as $dept_name => $courses): ?>
                            <div class="dept-section">
                                <div class="dept-header">
                                    <?php echo htmlspecialchars($dept_name); ?> (<?php echo count($courses); ?> courses)
                                </div>
                                <?php foreach ($courses as $course): ?>
                                    <div class="checkbox-option">
                                        <input 
                                            type="checkbox" 
                                            id="course_<?php echo htmlspecialchars($course['code']); ?>" 
                                            name="completed_courses" 
                                            value="<?php echo htmlspecialchars($course['code']); ?>"
                                        >
                                        <label for="course_<?php echo htmlspecialchars($course['code']); ?>">
                                            <?php echo htmlspecialchars($course['code']); ?> - <?php echo htmlspecialchars($course['name']); ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn">
                    Save Profile & Go to Dashboard
                </button>
            </form>
        </div>
    </div>

    <script>
        const form = document.getElementById('questionnaireForm');
        const submitBtn = document.getElementById('submitBtn');

        function showMessage(text, type) {
            const messageDiv = document.getElementById('message');
            messageDiv.textContent = text;
            messageDiv.className = `message ${type} show`;
            setTimeout(() => {
                messageDiv.classList.remove('show');
            }, 5000);
        }

        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            // Validate required fields
            const program = document.getElementById('program').value;
            const year = document.getElementById('year').value;
            const gpa = document.getElementById('gpa').value;
            const completedCourses = Array.from(document.querySelectorAll('input[name="completed_courses"]:checked')).map(cb => cb.value);

            if (!program || !year || !gpa) {
                showMessage('Please complete all required fields', 'error');
                return;
            }

            if (completedCourses.length === 0) {
                showMessage('Please select at least one completed course', 'error');
                return;
            }

            // Collect form data
            const formData = {
                program: program,
                year: year,
                gpa: gpa,
                completed_courses: completedCourses,
                current_year: <?php echo $current_year; ?>
            };

            // Disable submit button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving...';

            try {
                // FIXED: Use relative path instead of absolute path
                const response = await fetch('../api/save-questionnaire.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(formData)
                });

                const result = await response.json();

                if (result.success) {
                    showMessage(result.message + ' Redirecting...', 'success');

                    // FIXED: Use absolute path for redirect
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 2000);
                } else {
                    showMessage(result.message, 'error');
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Save Profile & Go to Dashboard';
                }

            } catch (error) {
                console.error('Error:', error);
                showMessage('An error occurred. Please try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Profile & Go to Dashboard';
            }
        });

        // Add console logging for debugging
        console.log('Questionnaire form loaded');
        console.log('Current URL:', window.location.href);
    </script>
</body>
</html>