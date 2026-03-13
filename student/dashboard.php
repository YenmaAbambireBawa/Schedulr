<?php
/**
 * Student Dashboard - CORRECTED VERSION
 * Protected page - requires authentication
 * Implements course registration with 3 FLEXIBLE timetable options (different courses allowed per option)
 * and Microsoft OAuth integration
 * Reads student data from saved user_data files and courses from database
 */

require_once __DIR__ . '/../middleware/Auth.php';

// Require student authentication
Auth::requireStudent();

// Get current user data
$user = Auth::user();
// Database connection
require_once __DIR__ . '/../config/database.php';
$database = new Database();
$db = $database->getConnection();
if (!$db) {
    die("Database connection failed");
}
// Function to read student data from saved file
function getStudentDataFromFile($userId, $userName) {
    $userDataDir = __DIR__ . '/../user_data';
    
    // Clean the full name to match saved filename
    $fullName = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $userName);
    $fullName = str_replace(' ', '_', $fullName);
    
    $filename = $userId . '_' . $fullName . '.txt';
    $filepath = $userDataDir . '/' . $filename;
    
    // default student data
    $studentData = [
        'name' => $userName,
        'student_id' => 'STU' . str_pad($userId, 6, '0', STR_PAD_LEFT),
        'cgpa' => 0.00,
        'program' => 'Computer Science',
        'year' => 1,
        'completed_courses' => []
    ];
    
    if (!file_exists($filepath)) {
        return $studentData;
    }
    
    $content = file_get_contents($filepath);
    
    // parse GPA
    if (preg_match('/Current GPA\s*[:=]\s*([\d.]+)/i', $content, $matches)) {
        $studentData['cgpa'] = floatval($matches[1]);
    }
    
    // parse Program
    if (preg_match('/Program\/Major\s*[:=]\s*(.+)/i', $content, $matches)) {
        $studentData['program'] = trim($matches[1]);
    }
    
    // parse Year
    if (preg_match('/Current Year\s*[:=]?\s*Year\s*(\d+)/i', $content, $matches)) {
        $studentData['year'] = intval($matches[1]);
    }
    
    // parse completed courses
    if (preg_match('/COMPLETED COURSES\s*[:\n](.*)/is', $content, $matches)) {
        $coursesText = trim($matches[1]);
        $lines = preg_split("/\r\n|\n|\r/", $coursesText);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (!$line) continue;

            // match lines like "CS 111 - Intro to CS" or "CS 111: Intro to CS"
            if (preg_match('/([A-Z]+\s*\d+)\s*[-:]\s*(.+)/i', $line, $courseMatch)) {
                $studentData['completed_courses'][] = [
                    'code' => strtoupper(trim($courseMatch[1])),
                    'name' => trim($courseMatch[2])
                ];
            }
        }
    }
    
    return $studentData;
}

// Function to get all courses from database
function getAllCourses($db) {
    $query = "SELECT 
                c.course_id,
                c.course_code,
                c.course_name,
                c.credits,
                c.course_description,
                d.dept_name,
                d.dept_code
              FROM courses c
              LEFT JOIN departments d ON c.dept_id = d.dept_id
              ORDER BY c.course_code";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to get course prerequisites
function getCoursePrerequisites($db, $courseId) {
    $query = "SELECT 
                c.course_code,
                c.course_name,
                cp.prerequisite_type
              FROM course_prerequisites cp
              JOIN courses c ON cp.prerequisite_course_id = c.course_id
              WHERE cp.course_id = ?
              ORDER BY cp.prerequisite_type, c.course_code";
    
    $stmt = $db->prepare($query);
    $stmt->execute([$courseId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to build course catalog structure
function buildCourseCatalog($db) {
    $courses = getAllCourses($db);
    $catalog = [];
    
    foreach ($courses as $course) {
        $prerequisites = getCoursePrerequisites($db, $course['course_id']);
        
        // Group prerequisites by type
        $requiredPrereqs = [];
        $orPrereqs = [];
        
        foreach ($prerequisites as $prereq) {
            if ($prereq['prerequisite_type'] === 'required') {
                $requiredPrereqs[] = $prereq['course_code'];
            } else {
                $orPrereqs[] = $prereq['course_code'];
            }
        }
        
        // Combine prerequisites with proper formatting
        $prereqList = $requiredPrereqs;
        if (!empty($orPrereqs)) {
            $prereqList[] = '(' . implode(' OR ', $orPrereqs) . ')';
        }
        
        $catalog[$course['course_code']] = [
            'course_id' => $course['course_id'],
            'name' => $course['course_name'],
            'credits' => $course['credits'],
            'description' => $course['course_description'],
            'department' => $course['dept_name'],
            'dept_code' => $course['dept_code'],
            'prerequisites' => $prereqList,
            'sections' => [
                // Default sections - in a real system, these would also come from database
                ['section' => 'A', 'days' => 'Mon, Wed', 'time' => '08:00-09:30', 'instructor' => 'TBA'],
                ['section' => 'B', 'days' => 'Tue, Thu', 'time' => '10:00-11:30', 'instructor' => 'TBA']
            ]
        ];
    }
    
    return $catalog;
}

// Get student data
$student_data = getStudentDataFromFile($user['id'], $user['name']);

// Build course catalog from database
$course_catalog = buildCourseCatalog($db);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Schedulr</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            color: #1a1a1a;
        }

        .navbar {
            background: #ffffff;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: #1a1a1a;
        }

        .logo-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 20px;
            color: white;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 800;
            color: #1a1a1a;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-name {
            font-weight: 600;
            color: #4b5563;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(220, 38, 38, 0.4);
        }

        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 81px);
        }

        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: #ffffff;
            border-right: 1px solid #e5e7eb;
            padding: 30px 0;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.02);
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-item {
            margin-bottom: 5px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 30px;
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-link:hover {
            background: #f9fafb;
            color: #dc2626;
            border-left-color: #dc2626;
        }

        .sidebar-link.active {
            background: #fef2f2;
            color: #dc2626;
            border-left-color: #dc2626;
        }

        .sidebar-icon {
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 40px;
            background: #f8f9fa;
            overflow-y: auto;
        }

        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        /* Student Info Card */
        .student-info-card {
            background: linear-gradient(135deg, #dc2626, #991b1b);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(220, 38, 38, 0.2);
        }

        .student-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 20px;
        }

        .student-name {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .student-id {
            font-size: 14px;
            opacity: 0.9;
        }

        .student-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .detail-item {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 12px;
            padding: 15px;
        }

        .detail-label {
            font-size: 12px;
            opacity: 0.9;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .detail-value {
            font-size: 18px;
            font-weight: 700;
        }

        .cgpa-container {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px 30px;
            display: inline-flex;
            align-items: center;
            gap: 15px;
        }

        .cgpa-label {
            font-size: 14px;
            opacity: 0.9;
            font-weight: 600;
        }

        .cgpa-value {
            font-size: 36px;
            font-weight: 800;
            filter: blur(0px);
            transition: filter 0.3s ease;
        }

        .cgpa-value.hidden {
            filter: blur(8px);
        }

        .eye-button {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .eye-button:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .eye-icon {
            width: 20px;
            height: 20px;
            stroke: white;
            stroke-width: 2;
            fill: none;
        }

        /* Completed Courses */
        .section-title {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 20px;
            color: #1a1a1a;
        }

        .courses-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .course-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .course-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
            border-color: #dc2626;
        }

        .course-card.selectable {
            cursor: pointer;
        }

        .course-card.selected {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .course-code {
            font-size: 14px;
            font-weight: 700;
            color: #dc2626;
            margin-bottom: 8px;
        }

        .course-name {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 12px;
        }

        .course-info {
            color: #6b7280;
            font-size: 14px;
            line-height: 1.6;
        }

        .course-prerequisites {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
        }

        .course-description {
            margin-top: 10px;
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
        }

        /* Search Bar */
        .search-bar {
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 30px;
            border: 2px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 15px;
            transition: all 0.3s ease;
        }

        .search-bar:focus-within {
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        .search-input {
            flex: 1;
            border: none;
            outline: none;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            color: #1a1a1a;
        }

        .search-icon {
            width: 20px;
            height: 20px;
            stroke: #9ca3af;
            stroke-width: 2;
            fill: none;
        }

        /* Registration Workflow */
        .registration-workflow {
            background: white;
            border-radius: 20px;
            padding: 40px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .workflow-step {
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 1px solid #e5e7eb;
        }

        .workflow-step:last-child {
            border-bottom: none;
        }

        .step-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .step-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .step-title {
            font-size: 20px;
            font-weight: 700;
            color: #1a1a1a;
        }

        .selected-courses-list {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            min-height: 60px;
        }

        .selected-course-item {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .remove-btn {
            background: #fee2e2;
            color: #991b1b;
            border: none;
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
        }

        .remove-btn:hover {
            background: #fecaca;
        }

        .btn-secondary {
            background: white;
            color: #dc2626;
            border: 2px solid #dc2626;
        }

        .btn-secondary:hover {
            background: #dc2626;
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #15803d, #166534);
            color: white;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(21, 128, 61, 0.4);
        }

        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .status-indicator {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            background: #fef3c7;
            color: #92400e;
            border-radius: 10px;
            font-weight: 600;
            font-size: 14px;
            margin-top: 15px;
        }

        .status-indicator.success {
            background: #d1fae5;
            color: #065f46;
        }

        .status-indicator.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-indicator.info {
            background: #dbeafe;
            color: #1e40af;
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Timetable Options Styles */
        .timetable-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5e7eb;
        }

        .timetable-tab {
            padding: 12px 24px;
            background: transparent;
            border: none;
            border-bottom: 3px solid transparent;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #6b7280;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .timetable-tab:hover {
            color: #dc2626;
        }

        .timetable-tab.active {
            color: #dc2626;
            border-bottom-color: #dc2626;
        }

        .timetable-tab .rank-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #dc2626;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 700;
        }

        .timetable-content {
            display: none;
        }

        .timetable-content.active {
            display: block;
        }

        .section-selector {
            margin-top: 15px;
        }

        .section-options {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 10px;
        }

        .section-option {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .section-option:hover {
            border-color: #dc2626;
        }

        .section-option.selected {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .section-option input[type="radio"] {
            display: none;
        }

        .section-header {
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 8px;
        }

        .section-details {
            font-size: 14px;
            color: #6b7280;
            line-height: 1.6;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #6b7280;
        }

        .error-list {
            margin-top: 15px;
        }

        .error-item {
            background: #fee2e2;
            border-left: 4px solid #991b1b;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: #991b1b;
        }

        .clash-item {
            background: #fef3c7;
            border-left: 4px solid #92400e;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 8px;
            color: #92400e;
        }

        .ranking-section {
            background: #f9fafb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .ranking-item {
            background: white;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .ranking-number {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #dc2626, #991b1b);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 18px;
        }

        .ranking-details {
            flex: 1;
        }

        .ranking-label {
            font-size: 12px;
            color: #6b7280;
            margin-bottom: 4px;
        }

        .ranking-value {
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
        }

        .summary-card {
            background: white;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
        }

        .summary-header {
            font-size: 18px;
            font-weight: 700;
            margin-bottom: 15px;
            color: #1a1a1a;
        }

        .summary-item {
            padding: 10px 0;
            border-bottom: 1px solid #f3f4f6;
        }

        .summary-item:last-child {
            border-bottom: none;
        }

        .option-header-info {
            background: #f9fafb;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-left: 4px solid #dc2626;
        }

        .option-header-info h4 {
            font-size: 16px;
            font-weight: 700;
            color: #1a1a1a;
            margin-bottom: 5px;
        }

        .option-header-info p {
            font-size: 14px;
            color: #6b7280;
            margin: 0;
        }

        .mycamu-credentials-section {
            margin: 30px 0;
            padding: 20px;
            background: #f5f5f5;
            border-radius: 8px;
        }

        .mycamu-credentials-section h3 {
            margin-bottom: 10px;
            color: #1a1a1a;
        }

        .mycamu-credentials-section input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: 'Outfit', sans-serif;
        }

        .mycamu-credentials-section label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        /* Mobile hamburger */
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
        }
        .hamburger span {
            display: block;
            width: 24px;
            height: 2px;
            background: #1a1a1a;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Timetable option select (mobile-friendly) */
        .option-select-wrapper {
            display: none;
            margin-bottom: 20px;
        }
        .option-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 600;
            color: #1a1a1a;
            background: white;
            cursor: pointer;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23dc2626' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            padding-right: 40px;
        }
        .option-select:focus {
            outline: none;
            border-color: #dc2626;
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1);
        }

        /* Sidebar overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.4);
            z-index: 99;
        }
        .sidebar-overlay.active { display: block; }

        @media (max-width: 768px) {
            .navbar {
                padding: 14px 20px;
            }
            .logo-text { font-size: 20px; }
            .user-name { display: none; }
            .hamburger { display: flex; }

            .dashboard-container { flex-direction: column; }

            .sidebar {
                position: fixed;
                top: 0;
                left: -280px;
                height: 100%;
                z-index: 100;
                transition: left 0.3s ease;
                box-shadow: 4px 0 20px rgba(0,0,0,0.1);
                padding-top: 70px;
            }
            .sidebar.open { left: 0; }

            .main-content {
                padding: 20px 16px;
            }

            .student-info-card {
                padding: 24px 20px;
                border-radius: 16px;
            }
            .student-header {
                flex-direction: column;
                gap: 16px;
                align-items: flex-start;
            }
            .student-name { font-size: 24px; }
            .cgpa-container { width: 100%; justify-content: space-between; }

            .student-details {
                grid-template-columns: 1fr 1fr;
                gap: 12px;
            }

            .courses-grid {
                grid-template-columns: 1fr;
            }

            .registration-workflow {
                padding: 20px 16px;
                border-radius: 14px;
            }

            /* Show select, hide tabs on mobile */
            .timetable-tabs { display: none; }
            .option-select-wrapper { display: block; }

            .section-options {
                grid-template-columns: 1fr;
            }

            .step-header { gap: 10px; }
            .step-title { font-size: 17px; }

            .selected-course-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }

            .summary-header {
                display: flex;
                flex-direction: column;
                gap: 4px;
            }
            .summary-header span[style*="float"] {
                float: none !important;
            }

            .mycamu-credentials-section {
                padding: 16px;
            }

            .section-title { font-size: 20px; }
        }

        @media (max-width: 480px) {
            .student-details { grid-template-columns: 1fr; }
            .btn { padding: 10px 16px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div style="display:flex;align-items:center;gap:14px;">
            <button class="hamburger" id="hamburgerBtn" aria-label="Open menu">
                <span></span><span></span><span></span>
            </button>
            <a href="/" class="logo">
            <div class="logo-icon">S</div>
            <div class="logo-text">Schedulr</div>
        </a>
        </div>
        <div class="user-menu">
            <span class="user-name"><?php echo htmlspecialchars($user['email']); ?></span>
            <a href="../api/logout.php" class="btn btn-primary">Logout</a>
        </div>
    </nav>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <ul class="sidebar-nav">
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link active" data-section="dashboard">
                        <span class="sidebar-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <rect x="3" y="3" width="7" height="7"></rect>
                                <rect x="14" y="3" width="7" height="7"></rect>
                                <rect x="14" y="14" width="7" height="7"></rect>
                                <rect x="3" y="14" width="7" height="7"></rect>
                            </svg>
                        </span>
                        Dashboard
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" data-section="register">
                        <span class="sidebar-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M9 11l3 3L22 4"></path>
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"></path>
                            </svg>
                        </span>
                        Register Courses
                    </a>
                </li>
                <li class="sidebar-item">
                    <a href="#" class="sidebar-link" data-section="catalog">
                        <span class="sidebar-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path d="M4 19.5A2.5 2.5 0 016.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"></path>
                            </svg>
                        </span>
                        Course Catalog
                    </a>
                </li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="content-section active">
                <div class="student-info-card">
                    <div class="student-header">
                        <div>
                            <h1 class="student-name"><?php echo htmlspecialchars($student_data['name']); ?></h1>
                            <p class="student-id"><?php echo htmlspecialchars($student_data['student_id']); ?></p>
                        </div>
                        <div class="cgpa-container">
                            <div>
                                <div class="cgpa-label">CGPA</div>
                                <div class="cgpa-value" id="cgpaValue"><?php echo number_format($student_data['cgpa'], 2); ?></div>
                            </div>
                            <button class="eye-button" id="toggleCGPA" aria-label="Toggle CGPA visibility">
                                <svg class="eye-icon" id="eyeIcon" viewBox="0 0 24 24">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="student-details">
                        <div class="detail-item">
                            <div class="detail-label">Program</div>
                            <div class="detail-value"><?php echo htmlspecialchars($student_data['program']); ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Current Year</div>
                            <div class="detail-value">Year <?php echo $student_data['year']; ?></div>
                        </div>
                        <div class="detail-item">
                            <div class="detail-label">Completed Courses</div>
                            <div class="detail-value"><?php echo count($student_data['completed_courses']); ?> Courses</div>
                        </div>
                    </div>
                </div>

                <h2 class="section-title">Completed Courses</h2>
                <?php if (count($student_data['completed_courses']) > 0): ?>
                    <div class="courses-grid">
                        <?php foreach ($student_data['completed_courses'] as $course): ?>
                            <div class="course-card">
                                <div class="course-code"><?php echo htmlspecialchars($course['code']); ?></div>
                                <div class="course-name"><?php echo htmlspecialchars($course['name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <p>No completed courses found. Please complete the questionnaire first.</p>
                    </div>
                <?php endif; ?>
            </section>

            <!-- Register Courses Section -->
            <section id="register" class="content-section">
                <h2 class="section-title">Course Registration - Create 3 Flexible Timetable Options</h2>
                <p style="color: #6b7280; margin-bottom: 30px; font-size: 16px;">
                    Create 3 completely different timetable options. Each option can have different courses, different sections, or any combination you prefer. 
                    You'll rank them in order of preference (1 = Most Preferred, 3 = Least Preferred).
                </p>
                
                <div class="registration-workflow">
                    <!-- Mobile Option Select -->
                    <div class="option-select-wrapper">
                        <select class="option-select" id="optionSelect" aria-label="Select timetable option">
                            <option value="1">Option 1 — Most Preferred</option>
                            <option value="2">Option 2 — Second Choice</option>
                            <option value="3">Option 3 — Third Choice</option>
                        </select>
                    </div>

                    <!-- Timetable Tabs -->
                    <div class="timetable-tabs" id="timetableTabs">
                        <button class="timetable-tab active" data-option="1">
                            Option 1
                            <span class="rank-badge">1</span>
                        </button>
                        <button class="timetable-tab" data-option="2">
                            Option 2
                            <span class="rank-badge">2</span>
                        </button>
                        <button class="timetable-tab" data-option="3">
                            Option 3
                            <span class="rank-badge">3</span>
                        </button>
                    </div>

                    <!-- Option 1 -->
                    <div id="timetableOption1" class="timetable-content active">
                        <div class="option-header-info">
                            <h4>Option 1 - Most Preferred</h4>
                            <p>Select your ideal course combination and time slots</p>
                        </div>

                        <!-- Step 1: Select Courses for Option 1 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">1</div>
                                <h3 class="step-title">Select Courses for Option 1</h3>
                            </div>
                            <div class="search-bar">
                                <svg class="search-icon" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="M21 21l-4.35-4.35"></path>
                                </svg>
                                <input type="text" class="search-input" placeholder="Search by course name or code..." id="registerSearch1">
                            </div>
                            <div id="availableCourses1" class="courses-grid"></div>
                            
                            <h4 style="margin-top: 30px; margin-bottom: 15px; font-size: 18px; font-weight: 700;">Selected Courses</h4>
                            <div class="selected-courses-list" id="selectedCoursesList1">
                                <p style="color: #6b7280; text-align: center;">No courses selected yet</p>
                            </div>
                        </div>

                        <!-- Step 2: Check Prerequisites for Option 1 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">2</div>
                                <h3 class="step-title">Check Prerequisites</h3>
                            </div>
                            <button class="btn btn-secondary" id="checkPrereq1">Check Prerequisites</button>
                            <div id="prereqResult1"></div>
                        </div>

                        <!-- Step 3: Select Time Slots for Option 1 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">3</div>
                                <h3 class="step-title">Select Time Slots</h3>
                            </div>
                            <div id="timeSlotsContainer1"></div>
                        </div>
                    </div>

                    <!-- Option 2 -->
                    <div id="timetableOption2" class="timetable-content">
                        <div class="option-header-info">
                            <h4>Option 2 - Second Choice</h4>
                            <p>Create an alternative course combination (can be completely different from Option 1)</p>
                        </div>

                        <!-- Step 1: Select Courses for Option 2 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">1</div>
                                <h3 class="step-title">Select Courses for Option 2</h3>
                            </div>
                            <div class="search-bar">
                                <svg class="search-icon" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="M21 21l-4.35-4.35"></path>
                                </svg>
                                <input type="text" class="search-input" placeholder="Search by course name or code..." id="registerSearch2">
                            </div>
                            <div id="availableCourses2" class="courses-grid"></div>
                            
                            <h4 style="margin-top: 30px; margin-bottom: 15px; font-size: 18px; font-weight: 700;">Selected Courses</h4>
                            <div class="selected-courses-list" id="selectedCoursesList2">
                                <p style="color: #6b7280; text-align: center;">No courses selected yet</p>
                            </div>
                        </div>

                        <!-- Step 2: Check Prerequisites for Option 2 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">2</div>
                                <h3 class="step-title">Check Prerequisites</h3>
                            </div>
                            <button class="btn btn-secondary" id="checkPrereq2">Check Prerequisites</button>
                            <div id="prereqResult2"></div>
                        </div>

                        <!-- Step 3: Select Time Slots for Option 2 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">3</div>
                                <h3 class="step-title">Select Time Slots</h3>
                            </div>
                            <div id="timeSlotsContainer2"></div>
                        </div>
                    </div>

                    <!-- Option 3 -->
                    <div id="timetableOption3" class="timetable-content">
                        <div class="option-header-info">
                            <h4>Option 3 - Third Choice</h4>
                            <p>Create another alternative (can be completely different from Options 1 & 2)</p>
                        </div>

                        <!-- Step 1: Select Courses for Option 3 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">1</div>
                                <h3 class="step-title">Select Courses for Option 3</h3>
                            </div>
                            <div class="search-bar">
                                <svg class="search-icon" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <path d="M21 21l-4.35-4.35"></path>
                                </svg>
                                <input type="text" class="search-input" placeholder="Search by course name or code..." id="registerSearch3">
                            </div>
                            <div id="availableCourses3" class="courses-grid"></div>
                            
                            <h4 style="margin-top: 30px; margin-bottom: 15px; font-size: 18px; font-weight: 700;">Selected Courses</h4>
                            <div class="selected-courses-list" id="selectedCoursesList3">
                                <p style="color: #6b7280; text-align: center;">No courses selected yet</p>
                            </div>
                        </div>

                        <!-- Step 2: Check Prerequisites for Option 3 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">2</div>
                                <h3 class="step-title">Check Prerequisites</h3>
                            </div>
                            <button class="btn btn-secondary" id="checkPrereq3">Check Prerequisites</button>
                            <div id="prereqResult3"></div>
                        </div>

                        <!-- Step 3: Select Time Slots for Option 3 -->
                        <div class="workflow-step">
                            <div class="step-header">
                                <div class="step-number">3</div>
                                <h3 class="step-title">Select Time Slots</h3>
                            </div>
                            <div id="timeSlotsContainer3"></div>
                        </div>
                    </div>

                    <!-- Step 4: Review All Options -->
                    <div class="workflow-step" style="margin-top: 40px; border-top: 2px solid #e5e7eb; padding-top: 40px;">
                        <div class="step-header">
                            <div class="step-number">4</div>
                            <h3 class="step-title">Review Your Timetable Options</h3>
                        </div>
                        <p style="color: #6b7280; margin-bottom: 20px;">Review your three timetable options before submission. They are ranked in order of preference (1 = Most Preferred, 3 = Least Preferred).</p>
                        
                        <button class="btn btn-secondary" id="reviewOptions">Generate Review</button>
                        <div id="reviewResult"></div>
                    </div>

                    <!-- myCAMU Credentials Section -->
                    <div class="mycamu-credentials-section">
                        <h3>myCAMU Login Credentials</h3>
                        <p style="color: #666; margin-bottom: 20px;">
                            These credentials will be used to automatically register your selected courses in myCAMU.
                            Your password is encrypted and stored securely.
                        </p>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="mycamuEmail">myCAMU Email:</label>
                            <input 
                                type="email" 
                                id="mycamuEmail" 
                                placeholder="your.email@camu.edu" 
                                required
                            >
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <label for="mycamuPassword">myCAMU Password:</label>
                            <input 
                                type="password" 
                                id="mycamuPassword" 
                                placeholder="Enter your myCAMU password" 
                                required
                            >
                            <small style="color: #666; display: block; margin-top: 5px;">
                                🔒 Your password is encrypted using AES-256 encryption
                            </small>
                        </div>
                    </div>

                    <!-- Step 5: Submit for Auto-Registration -->
                    <div class="workflow-step">
                        <div class="step-header">
                            <div class="step-number">5</div>
                            <h3 class="step-title">Submit for Auto-Registration</h3>
                        </div>
                        <p style="color: #6b7280; margin-bottom: 20px;">
                            After entering your myCAMU credentials above, click the button below to submit your registration. 
                            You will receive a verification email to confirm your registration.
                        </p>
                        <button class="btn btn-success" id="submitRegistration" disabled>Submit Registration & Send Verification Email</button>
                        <div id="registrationStatus"></div>
                    </div>
                </div>
            </section>

            <!-- Course Catalog Section -->
            <section id="catalog" class="content-section">
                <h2 class="section-title">Course Catalog</h2>
                <div class="search-bar">
                    <svg class="search-icon" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="8"></circle>
                        <path d="M21 21l-4.35-4.35"></path>
                    </svg>
                    <input type="text" class="search-input" placeholder="Search courses by name or code..." id="catalogSearch">
                </div>
                <div class="courses-grid" id="catalogGrid"></div>
            </section>
        </main>
    </div>

    <script>
        // Course catalog data from PHP
        const courseCatalog = <?php echo json_encode($course_catalog); ?>;
        const completedCourses = <?php echo json_encode(array_column($student_data['completed_courses'], 'code')); ?>;

        // State management
        let selectedCourses = { option1: [], option2: [], option3: [] };
        let selectedSections = { option1: {}, option2: {}, option3: {} };
        let prerequisitesPassed = { option1: false, option2: false, option3: false };
        let currentOption = 1;

        // ── DOM references (declared first so nothing blows up below) ──
        const sidebarLinks     = document.querySelectorAll('.sidebar-link');
        const sections         = document.querySelectorAll('.content-section');
        const timetableTabs    = document.querySelectorAll('.timetable-tab');
        const timetableContents = document.querySelectorAll('.timetable-content');
        const hamburgerBtn     = document.getElementById('hamburgerBtn');
        const sidebar          = document.querySelector('.sidebar');
        const sidebarOverlay   = document.getElementById('sidebarOverlay');
        const optionSelect     = document.getElementById('optionSelect');
        const toggleButton     = document.getElementById('toggleCGPA');
        const cgpaValue        = document.getElementById('cgpaValue');
        const eyeIcon          = document.getElementById('eyeIcon');

        // ── Sidebar navigation ──
        sidebarLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                const target = link.getAttribute('data-section');
                sections.forEach(sec => sec.classList.remove('active'));
                sidebarLinks.forEach(l => l.classList.remove('active'));
                document.getElementById(target).classList.add('active');
                link.classList.add('active');
                // Close mobile sidebar
                sidebar.classList.remove('open');
                sidebarOverlay.classList.remove('active');
            });
        });

        // ── Hamburger menu ──
        hamburgerBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('active');
        });
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('active');
        });

        // ── Timetable tab switching ──
        function switchOption(optionNum) {
            currentOption = parseInt(optionNum);
            timetableTabs.forEach(t => t.classList.remove('active'));
            timetableContents.forEach(c => c.classList.remove('active'));
            const matchingTab = document.querySelector(`.timetable-tab[data-option="${optionNum}"]`);
            if (matchingTab) matchingTab.classList.add('active');
            document.getElementById(`timetableOption${optionNum}`).classList.add('active');
            optionSelect.value = String(optionNum);
        }

        timetableTabs.forEach(tab => {
            tab.addEventListener('click', () => switchOption(tab.getAttribute('data-option')));
        });

        optionSelect.addEventListener('change', () => switchOption(optionSelect.value));

        // ── CGPA visibility toggle ──
        let isHidden = false;
        toggleButton.addEventListener('click', function () {
            isHidden = !isHidden;
            cgpaValue.classList.toggle('hidden', isHidden);
            eyeIcon.innerHTML = isHidden
                ? '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>'
                : '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
        });

        // Display course catalog
        function displayCatalog(searchTerm = '') {
            const catalogGrid = document.getElementById('catalogGrid');
            catalogGrid.innerHTML = '';
            
            Object.entries(courseCatalog).forEach(([code, course]) => {
                if (searchTerm && !code.toLowerCase().includes(searchTerm.toLowerCase()) && 
                    !course.name.toLowerCase().includes(searchTerm.toLowerCase())) {
                    return;
                }
                
                const prereqText = course.prerequisites.length > 0 
                    ? `Prerequisites: ${course.prerequisites.join(', ')}` 
                    : 'No prerequisites';
                
                const card = document.createElement('div');
                card.className = 'course-card';
                card.innerHTML = `
                    <div class="course-code">${code}</div>
                    <div class="course-name">${course.name}</div>
                    <div class="course-info">
                        <strong>Department:</strong> ${course.department || 'N/A'}<br>
                        <strong>Credits:</strong> ${course.credits}
                    </div>
                    ${course.description ? `<div class="course-description">${course.description}</div>` : ''}
                    <div class="course-prerequisites">${prereqText}</div>
                `;
                catalogGrid.appendChild(card);
            });
        }

        // Display available courses for registration - NOW OPTION SPECIFIC
        function displayAvailableCourses(optionNum, searchTerm = '') {
            const container = document.getElementById(`availableCourses${optionNum}`);
            container.innerHTML = '';

            // Show prompt if no search term
            if (!searchTerm.trim()) {
                container.innerHTML = '<p style="color:#6b7280;text-align:center;padding:20px 0;">Type above to search for courses</p>';
                return;
            }
            
            let count = 0;
            Object.entries(courseCatalog).forEach(([code, course]) => {
                if (completedCourses.includes(code)) return;
                
                if (!code.toLowerCase().includes(searchTerm.toLowerCase()) && 
                    !course.name.toLowerCase().includes(searchTerm.toLowerCase())) {
                    return;
                }
                
                count++;
                const prereqText = course.prerequisites.length > 0 
                    ? `Prerequisites: ${course.prerequisites.join(', ')}` 
                    : 'No prerequisites';
                
                const card = document.createElement('div');
                card.className = 'course-card selectable';
                if (selectedCourses[`option${optionNum}`].includes(code)) {
                    card.classList.add('selected');
                }
                
                card.innerHTML = `
                    <div class="course-code">${code}</div>
                    <div class="course-name">${course.name}</div>
                    <div class="course-info">Credits: ${course.credits}</div>
                    <div class="course-prerequisites">${prereqText}</div>
                `;
                
                card.addEventListener('click', () => toggleCourseSelection(code, optionNum));
                container.appendChild(card);
            });

            if (count === 0) {
                container.innerHTML = '<p style="color:#6b7280;text-align:center;padding:20px 0;">No courses found matching your search</p>';
            }
        }

        // Toggle course selection - NOW OPTION SPECIFIC
        function toggleCourseSelection(code, optionNum) {
            const optionKey = `option${optionNum}`;
            const index = selectedCourses[optionKey].indexOf(code);
            
            if (index > -1) {
                selectedCourses[optionKey].splice(index, 1);
                delete selectedSections[optionKey][code];
            } else {
                selectedCourses[optionKey].push(code);
            }
            
            updateSelectedCoursesList(optionNum);
            displayAvailableCourses(optionNum, document.getElementById(`registerSearch${optionNum}`).value);
            prerequisitesPassed[optionKey] = false;
            document.getElementById(`prereqResult${optionNum}`).innerHTML = '';
            clearTimeSlots(optionNum);
            
            // Disable submit if any option changes
            document.getElementById('submitRegistration').disabled = true;
            document.getElementById('reviewResult').innerHTML = '';
        }

        // Update selected courses list - NOW OPTION SPECIFIC
        function updateSelectedCoursesList(optionNum) {
            const container = document.getElementById(`selectedCoursesList${optionNum}`);
            const optionKey = `option${optionNum}`;
            
            if (selectedCourses[optionKey].length === 0) {
                container.innerHTML = '<p style="color: #6b7280; text-align: center;">No courses selected yet</p>';
                return;
            }
            
            container.innerHTML = '';
            selectedCourses[optionKey].forEach(code => {
                const course = courseCatalog[code];
                const item = document.createElement('div');
                item.className = 'selected-course-item';
                item.innerHTML = `
                    <div>
                        <strong>${code}</strong> - ${course.name}
                    </div>
                    <button class="remove-btn" onclick="toggleCourseSelection('${code}', ${optionNum})">Remove</button>
                `;
                container.appendChild(item);
            });
        }

        // Clear time slots - NOW OPTION SPECIFIC
        function clearTimeSlots(optionNum) {
            const container = document.getElementById(`timeSlotsContainer${optionNum}`);
            container.innerHTML = '<p style="color: #6b7280;">Complete prerequisite check first</p>';
        }

        // Check prerequisites - NOW OPTION SPECIFIC
        function checkPrerequisites(optionNum) {
            const resultDiv = document.getElementById(`prereqResult${optionNum}`);
            const optionKey = `option${optionNum}`;
            
            resultDiv.innerHTML = '<div class="status-indicator pulse">Checking prerequisites...</div>';
            
            setTimeout(() => {
                const errors = [];
                
                selectedCourses[optionKey].forEach(code => {
                    const course = courseCatalog[code];
                    const missingPrereqs = [];
                    
                    course.prerequisites.forEach(prereq => {
                        if (prereq.includes('(') && prereq.includes('OR')) {
                            const orPrereqs = prereq.replace(/[()]/g, '').split(' OR ');
                            const hasOne = orPrereqs.some(p => completedCourses.includes(p.trim()));
                            if (!hasOne) {
                                missingPrereqs.push(prereq);
                            }
                        } else {
                            if (!completedCourses.includes(prereq)) {
                                missingPrereqs.push(prereq);
                            }
                        }
                    });
                    
                    if (missingPrereqs.length > 0) {
                        errors.push({
                            code: code,
                            name: course.name,
                            missing: missingPrereqs
                        });
                    }
                });
                
                if (errors.length > 0) {
                    let errorHTML = '<div class="error-list">';
                    errors.forEach(error => {
                        errorHTML += `
                            <div class="error-item">
                                <strong>You are not qualified for: ${error.code} - ${error.name}</strong><br>
                                Missing prerequisites: ${error.missing.join(', ')}
                            </div>
                        `;
                        const index = selectedCourses[optionKey].indexOf(error.code);
                        if (index > -1) {
                            selectedCourses[optionKey].splice(index, 1);
                        }
                    });
                    errorHTML += '</div>';
                    errorHTML += '<div class="status-indicator error">Some courses removed due to missing prerequisites</div>';
                    resultDiv.innerHTML = errorHTML;
                    updateSelectedCoursesList(optionNum);
                    displayAvailableCourses(optionNum, document.getElementById(`registerSearch${optionNum}`).value);
                    prerequisitesPassed[optionKey] = false;
                } else {
                    resultDiv.innerHTML = '<div class="status-indicator success">✓ All prerequisites met! You can now select time slots.</div>';
                    prerequisitesPassed[optionKey] = true;
                    displayTimeSlots(optionNum);
                }
            }, 1500);
        }

        // Display time slots - NOW OPTION SPECIFIC
        function displayTimeSlots(optionNum) {
            const container = document.getElementById(`timeSlotsContainer${optionNum}`);
            const optionKey = `option${optionNum}`;
            
            if (selectedCourses[optionKey].length === 0) {
                container.innerHTML = '<p style="color: #6b7280;">No courses selected</p>';
                return;
            }
            
            container.innerHTML = '';
            
            selectedCourses[optionKey].forEach(code => {
                const course = courseCatalog[code];
                const selectorDiv = document.createElement('div');
                selectorDiv.className = 'section-selector';
                
                let sectionsHTML = `
                    <h4 style="margin-bottom: 10px; font-weight: 700;">${code} - ${course.name}</h4>
                    <div class="section-options">
                `;
                
                course.sections.forEach((section, index) => {
                    const isSelected = selectedSections[optionKey][code] === index;
                    sectionsHTML += `
                        <div class="section-option ${isSelected ? 'selected' : ''}" onclick="selectSection('${code}', ${index}, ${optionNum})">
                            <input type="radio" name="${code}_option${optionNum}" value="${index}" ${isSelected ? 'checked' : ''}>
                            <div class="section-header">Section ${section.section}</div>
                            <div class="section-details">
                                ${section.days}<br>
                                ${section.time}<br>
                                ${section.instructor}
                            </div>
                        </div>
                    `;
                });
                
                sectionsHTML += '</div>';
                selectorDiv.innerHTML = sectionsHTML;
                container.appendChild(selectorDiv);
            });
        }

        // Select section - NOW OPTION SPECIFIC
        function selectSection(courseCode, sectionIndex, optionNum) {
            selectedSections[`option${optionNum}`][courseCode] = sectionIndex;
            displayTimeSlots(optionNum);
        }

        // Setup prerequisite check buttons
        document.getElementById('checkPrereq1').addEventListener('click', () => checkPrerequisites(1));
        document.getElementById('checkPrereq2').addEventListener('click', () => checkPrerequisites(2));
        document.getElementById('checkPrereq3').addEventListener('click', () => checkPrerequisites(3));

        // Setup search inputs
        document.getElementById('registerSearch1').addEventListener('input', (e) => displayAvailableCourses(1, e.target.value));
        document.getElementById('registerSearch2').addEventListener('input', (e) => displayAvailableCourses(2, e.target.value));
        document.getElementById('registerSearch3').addEventListener('input', (e) => displayAvailableCourses(3, e.target.value));

        // Review options
        document.getElementById('reviewOptions').addEventListener('click', function() {
            const resultDiv = document.getElementById('reviewResult');
            
            // Check if all 3 options have at least one course and all courses have sections selected
            let allComplete = true;
            let errorMsg = '';
            
            for (let i = 1; i <= 3; i++) {
                const optionKey = `option${i}`;
                
                if (selectedCourses[optionKey].length === 0) {
                    allComplete = false;
                    errorMsg = `Option ${i} has no courses selected`;
                    break;
                }
                
                if (!prerequisitesPassed[optionKey]) {
                    allComplete = false;
                    errorMsg = `Option ${i} has not passed prerequisite check`;
                    break;
                }
                
                if (!selectedCourses[optionKey].every(code => selectedSections[optionKey].hasOwnProperty(code))) {
                    allComplete = false;
                    errorMsg = `Option ${i} has courses without selected time slots`;
                    break;
                }
            }
            
            if (!allComplete) {
                resultDiv.innerHTML = `<div class="status-indicator error">${errorMsg}</div>`;
                return;
            }
            
            resultDiv.innerHTML = '<div class="status-indicator pulse">Generating review...</div>';
            
            setTimeout(() => {
                let reviewHTML = '<div class="ranking-section">';
                reviewHTML += '<h4 style="margin-bottom: 15px; font-size: 18px; font-weight: 700;">Your Timetable Rankings</h4>';
                
                for (let i = 1; i <= 3; i++) {
                    const optionKey = `option${i}`;
                    const rankLabel = i === 1 ? 'Most Preferred' : i === 2 ? 'Second Choice' : 'Third Choice';
                    const totalCredits = selectedCourses[optionKey].reduce((sum, code) => sum + courseCatalog[code].credits, 0);
                    
                    reviewHTML += `
                        <div class="summary-card">
                            <div class="summary-header">
                                <span style="color: #dc2626;">Rank #${i}</span> - ${rankLabel}
                                <span style="float: right; color: #6b7280; font-size: 14px;">Total Credits: ${totalCredits}</span>
                            </div>
                    `;
                    
                    selectedCourses[optionKey].forEach(code => {
                        const course = courseCatalog[code];
                        const sectionIdx = selectedSections[optionKey][code];
                        const section = course.sections[sectionIdx];
                        
                        reviewHTML += `
                            <div class="summary-item">
                                <strong>${code}</strong> - ${course.name} (${course.credits} credits)<br>
                                <small style="color: #6b7280;">Section ${section.section} | ${section.days} | ${section.time} | ${section.instructor}</small>
                            </div>
                        `;
                    });
                    
                    reviewHTML += '</div>';
                }
                
                reviewHTML += '</div>';
                reviewHTML += '<div class="status-indicator success">✓ Review complete! You can now proceed to submission.</div>';
                
                resultDiv.innerHTML = reviewHTML;
                document.getElementById('submitRegistration').disabled = false;
            }, 1000);
        });

        // Submit registration
        document.getElementById('submitRegistration').addEventListener('click', async function() {
            const statusDiv = document.getElementById('registrationStatus');
            
            // Get myCAMU credentials
            const mycamuEmail = document.getElementById('mycamuEmail').value;
            const mycamuPassword = document.getElementById('mycamuPassword').value;
            
            if (!mycamuEmail || !mycamuPassword) {
                statusDiv.innerHTML = '<div class="status-indicator error">Please enter your myCAMU credentials</div>';
                return;
            }
            
            statusDiv.innerHTML = '<div class="status-indicator pulse">Submitting registration...</div>';
            
            const registrationData = {
                student_id: '<?php echo $user['id']; ?>',
                student_email: '<?php echo $user['email']; ?>',
                mycamu_email: mycamuEmail,
                mycamu_password: mycamuPassword,
                timetable_options: {
                    option1: {
                        courses: selectedCourses.option1,
                        sections: selectedSections.option1
                    },
                    option2: {
                        courses: selectedCourses.option2,
                        sections: selectedSections.option2
                    },
                    option3: {
                        courses: selectedCourses.option3,
                        sections: selectedSections.option3
                    }
                },
                timestamp: new Date().toISOString()
            };
            
            try {
                const response = await fetch('/api/submit-registration.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(registrationData)
                });
                
                const result = await response.json();
                
                if (result.success) {
                    statusDiv.innerHTML = '<div class="status-indicator success">✓ Registration submitted! Check your email (' + registrationData.student_email + ') to verify.</div>';
                    
                    // Clear password field for security
                    document.getElementById('mycamuPassword').value = '';
                    
                    // Guard: ensure the API returned a registration_id
                    if (!result.registration_id) {
                        statusDiv.innerHTML += '<div class="status-indicator error" style="margin-top:10px;">⚠ Registration was submitted but no registration ID was returned by the server. Please contact support or check <code>api/submit-registration.php</code>.</div>';
                        return;
                    }
                    
                    setTimeout(() => {
                        // Dummy mode returns a simulator_url — use it if present
                        if (result.simulator_url) {
                            window.location.href = result.simulator_url;
                        } else {
                            window.location.href = 'registration-pending.php?id=' + encodeURIComponent(result.registration_id);
                        }
                    }, 3000);
                } else {
                    throw new Error(result.message || 'Registration failed');
                }
                
            } catch (error) {
                statusDiv.innerHTML = '<div class="status-indicator error">Error: ' + error.message + '</div>';
            }
        });

        // Catalog search
        document.getElementById('catalogSearch').addEventListener('input', function(e) {
            displayCatalog(e.target.value);
        });

        // Initialize
        displayCatalog();
        displayAvailableCourses(1);
        displayAvailableCourses(2);
        displayAvailableCourses(3);
    </script>
</body>
</html>
