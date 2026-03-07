<?php
/**
 * Admin Dashboard
 * Protected page - requires admin authentication
 */

require_once __DIR__ . '/../middleware/Auth.php';

// Require admin authentication
Auth::requireAdmin();

// Get current user data
$user = Auth::user();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Schedulr</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --bg-primary: #0a0a0a;
            --bg-secondary: #141414;
            --bg-tertiary: #1a1a1a;
            --accent-red: #dc2626;
            --accent-red-dark: #991b1b;
            --text-primary: #ffffff;
            --text-secondary: #a3a3a3;
            --border-color: #262626;
            --white: #ffffff;
            --card-hover: #1f1f1f;
        }

        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            min-height: 100vh;
        }

        /* Navbar */
        .navbar {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
            backdrop-filter: blur(20px);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--text-primary);
        }

        .logo-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, var(--accent-red), var(--accent-red-dark));
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            font-size: 18px;
        }

        .logo-text {
            font-size: 20px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .admin-badge {
            background-color: var(--accent-red);
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Space Mono', monospace;
        }

        .user-info {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
        }

        .user-email {
            font-size: 12px;
            color: var(--text-secondary);
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: var(--accent-red);
            color: var(--white);
        }

        .btn-primary:hover {
            background-color: var(--accent-red-dark);
        }

        .btn-secondary {
            background-color: var(--bg-tertiary);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--card-hover);
        }

        .btn-white {
            background-color: var(--white);
            color: var(--bg-primary);
        }

        .btn-white:hover {
            background-color: #f5f5f5;
        }

        /* Layout */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 77px);
        }

        /* Sidebar */
        .sidebar {
            width: 280px;
            background-color: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 30px 0;
        }

        .sidebar-section {
            margin-bottom: 30px;
        }

        .sidebar-title {
            padding: 0 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-family: 'Space Mono', monospace;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-item {
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.2s ease;
            border-left: 3px solid transparent;
            font-weight: 500;
        }

        .sidebar-item:hover {
            background-color: var(--bg-tertiary);
            border-left-color: var(--accent-red);
        }

        .sidebar-item.active {
            background-color: var(--bg-tertiary);
            border-left-color: var(--accent-red);
            color: var(--white);
            font-weight: 600;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 40px;
            overflow-y: auto;
        }

        .content-header {
            margin-bottom: 30px;
        }

        .content-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 8px;
            font-family: 'Space Mono', monospace;
        }

        .content-subtitle {
            color: var(--text-secondary);
            font-size: 16px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
            transition: all 0.2s ease;
        }

        .stat-card:hover {
            border-color: var(--accent-red);
            transform: translateY(-2px);
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            margin-bottom: 8px;
            font-family: 'Space Mono', monospace;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
            color: var(--white);
        }

        .stat-change {
            font-size: 13px;
            margin-top: 8px;
            color: var(--text-secondary);
        }

        /* Section */
        .section {
            display: none;
        }

        .section.active {
            display: block;
        }

        /* Table Container */
        .table-container {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 18px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
        }

        .table-actions {
            display: flex;
            gap: 10px;
        }

        .search-box {
            padding: 8px 16px;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            width: 250px;
        }

        .search-box:focus {
            outline: none;
            border-color: var(--accent-red);
        }

        /* Data Table */
        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table thead {
            background-color: var(--bg-tertiary);
        }

        .data-table th {
            padding: 16px 24px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--text-secondary);
            font-family: 'Space Mono', monospace;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table td {
            padding: 16px 24px;
            border-bottom: 1px solid var(--border-color);
            font-size: 14px;
        }

        .data-table tbody tr {
            transition: background-color 0.2s ease;
        }

        .data-table tbody tr:hover {
            background-color: var(--bg-tertiary);
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        /* Action Buttons */
        .action-btns {
            display: flex;
            gap: 8px;
        }

        .btn-icon {
            padding: 6px 12px;
            font-size: 12px;
            border-radius: 4px;
        }

        .btn-edit {
            background-color: #3b82f6;
            color: white;
        }

        .btn-edit:hover {
            background-color: #2563eb;
        }

        .btn-delete {
            background-color: #ef4444;
            color: white;
        }

        .btn-delete:hover {
            background-color: #dc2626;
        }

        .btn-view {
            background-color: #10b981;
            color: white;
        }

        .btn-view:hover {
            background-color: #059669;
        }

        /* Badge */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Space Mono', monospace;
        }

        .badge-success {
            background-color: #10b98133;
            color: #10b981;
        }

        .badge-warning {
            background-color: #f59e0b33;
            color: #f59e0b;
        }

        .badge-danger {
            background-color: #ef444433;
            color: #ef4444;
        }

        .badge-info {
            background-color: #3b82f633;
            color: #3b82f6;
        }

        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }

        .modal-header {
            padding: 24px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 700;
            font-family: 'Space Mono', monospace;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-secondary);
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .modal-close:hover {
            color: var(--text-primary);
        }

        .modal-body {
            padding: 24px;
        }

        .modal-footer {
            padding: 24px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-family: 'Space Mono', monospace;
        }

        .form-input,
        .form-select,
        .form-textarea {
            width: 100%;
            padding: 10px 14px;
            background-color: var(--bg-tertiary);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: var(--text-primary);
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
        }

        .form-input:focus,
        .form-select:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--accent-red);
        }

        .form-textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Loading State */
        .loading {
            text-align: center;
            padding: 40px;
            color: var(--text-secondary);
        }

        .spinner {
            border: 3px solid var(--border-color);
            border-top: 3px solid var(--accent-red);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 40px;
            color: var(--text-secondary);
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 16px;
            margin-bottom: 20px;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 240px;
            }

            .main-content {
                padding: 30px 20px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                border-right: none;
                border-bottom: 1px solid var(--border-color);
            }

            .navbar {
                padding: 16px 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <a href="/" class="logo">
            <div class="logo-icon">S</div>
            <div class="logo-text">Schedulr</div>
        </a>
        <div class="nav-right">
            <span class="admin-badge">Admin</span>
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                <span class="user-email"><?php echo htmlspecialchars($user['email']); ?></span>
            </div>
            <a href="../api/logout.php" class="btn btn-primary">Logout</a>
        </div>
    </nav>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-section">
                <div class="sidebar-title">Overview</div>
                <ul class="sidebar-menu">
                    <li class="sidebar-item active" data-section="dashboard">Dashboard</li>
                    <li class="sidebar-item" data-section="analytics">Analytics</li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">Management</div>
                <ul class="sidebar-menu">
                    <li class="sidebar-item" data-section="users">Users</li>
                    <li class="sidebar-item" data-section="courses">Courses</li>
                    <li class="sidebar-item" data-section="departments">Departments</li>
                    <li class="sidebar-item" data-section="prerequisites">Prerequisites</li>
                    <li class="sidebar-item" data-section="registrations">Registrations</li>
                    <li class="sidebar-item" data-section="questionnaires">Questionnaires</li>
                </ul>
            </div>

            <div class="sidebar-section">
                <div class="sidebar-title">System</div>
                <ul class="sidebar-menu">
                    <li class="sidebar-item" data-section="sessions">Sessions</li>
                    <li class="sidebar-item" data-section="login-attempts">Login Attempts</li>
                    <li class="sidebar-item" data-section="settings">Settings</li>
                </ul>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <!-- Dashboard Section -->
            <section id="dashboard" class="section active">
                <div class="content-header">
                    <h1 class="content-title">Dashboard</h1>
                    <p class="content-subtitle">System overview and statistics</p>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-label">Total Users</div>
                        <div class="stat-value" id="stat-users">—</div>
                        <div class="stat-change">Loading...</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Total Courses</div>
                        <div class="stat-value" id="stat-courses">—</div>
                        <div class="stat-change">Loading...</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Active Registrations</div>
                        <div class="stat-value" id="stat-registrations">—</div>
                        <div class="stat-change">Loading...</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-label">Departments</div>
                        <div class="stat-value" id="stat-departments">—</div>
                        <div class="stat-change">Loading...</div>
                    </div>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">Recent Activity</h2>
                    </div>
                    <div class="loading">
                        <div class="spinner"></div>
                        <p>Loading recent activity...</p>
                    </div>
                </div>
            </section>

            <!-- Users Section -->
            <section id="users" class="section">
                <div class="content-header">
                    <h1 class="content-title">Users</h1>
                    <p class="content-subtitle">Manage student and admin accounts</p>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">All Users</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search users..." id="search-users">
                            <button class="btn btn-white" onclick="openModal('add-user')">Add User</button>
                        </div>
                    </div>
                    <div id="users-table-content" class="loading">
                        <div class="spinner"></div>
                        <p>Loading users...</p>
                    </div>
                </div>
            </section>

            <!-- Courses Section -->
            <section id="courses" class="section">
                <div class="content-header">
                    <h1 class="content-title">Courses</h1>
                    <p class="content-subtitle">Manage course catalog and information</p>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">All Courses</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search courses..." id="search-courses">
                            <button class="btn btn-white" onclick="openModal('add-course')">Add Course</button>
                        </div>
                    </div>
                    <div id="courses-table-content" class="loading">
                        <div class="spinner"></div>
                        <p>Loading courses...</p>
                    </div>
                </div>
            </section>

            <!-- Departments Section -->
            <section id="departments" class="section">
                <div class="content-header">
                    <h1 class="content-title">Departments</h1>
                    <p class="content-subtitle">Manage academic departments</p>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">All Departments</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search departments..." id="search-departments">
                            <button class="btn btn-white" onclick="openModal('add-department')">Add Department</button>
                        </div>
                    </div>
                    <div id="departments-table-content" class="loading">
                        <div class="spinner"></div>
                        <p>Loading departments...</p>
                    </div>
                </div>
            </section>

            <!-- Prerequisites Section -->
            <section id="prerequisites" class="section">
                <div class="content-header">
                    <h1 class="content-title">Course Prerequisites</h1>
                    <p class="content-subtitle">Manage course requirements and dependencies</p>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">All Prerequisites</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search prerequisites..." id="search-prerequisites">
                            <button class="btn btn-white" onclick="openModal('add-prerequisite')">Add Prerequisite</button>
                        </div>
                    </div>
                    <div id="prerequisites-table-content" class="loading">
                        <div class="spinner"></div>
                        <p>Loading prerequisites...</p>
                    </div>
                </div>
            </section>

            <!-- Registrations Section -->
            <section id="registrations" class="section">
                <div class="content-header">
                    <h1 class="content-title">Course Registrations</h1>
                    <p class="content-subtitle">Monitor and manage student registrations</p>
                </div>

                <div class="table-container">
                    <div class="table-header">
                        <h2 class="table-title">All Registrations</h2>
                        <div class="table-actions">
                            <input type="text" class="search-box" placeholder="Search registrations..." id="search-registrations">
                        </div>
                    </div>
                    <div id="registrations-table-content" class="loading">
                        <div class="spinner"></div>
                        <p>Loading registrations...</p>
                    </div>
                </div>
            </section>

            <!-- Other sections would follow similar pattern -->
        </main>
    </div>

    <!-- Modals will be added dynamically -->
    <div id="modal-container"></div>

    <script src="js/dashboard.js"></script>
</body>
</html>