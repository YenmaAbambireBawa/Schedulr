/**
 * Admin Dashboard JavaScript
 * Handles all CRUD operations and dynamic content
 */

// API Base URL
const API_BASE = '/Schedulr/api/admin.php';

// ============================================
// INITIALIZATION
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    loadDashboardStats();
    setupNavigation();
    setupSearchBoxes();
});

// ============================================
// NAVIGATION
// ============================================
function setupNavigation() {
    document.querySelectorAll('.sidebar-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelectorAll('.sidebar-item').forEach(i => i.classList.remove('active'));
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            
            this.classList.add('active');
            
            const sectionId = this.dataset.section;
            document.getElementById(sectionId).classList.add('active');
            
            loadSectionData(sectionId);
        });
    });
}

function loadSectionData(section) {
    switch(section) {
        case 'dashboard':
            loadDashboardStats();
            break;
        case 'users':
            loadUsers();
            break;
        case 'courses':
            loadCourses();
            break;
        case 'departments':
            loadDepartments();
            break;
        case 'prerequisites':
            loadPrerequisites();
            break;
        case 'registrations':
            loadRegistrations();
            break;
    }
}

// ============================================
// DASHBOARD STATS
// ============================================
async function loadDashboardStats() {
    try {
        const response = await fetch(`${API_BASE}?action=stats`);
        const result = await response.json();
        
        if (result.success) {
            const stats = result.data;
            document.getElementById('stat-users').textContent = stats.users;
            document.getElementById('stat-courses').textContent = stats.courses;
            document.getElementById('stat-registrations').textContent = stats.active_registrations;
            document.getElementById('stat-departments').textContent = stats.departments;
            
            document.querySelectorAll('.stat-change').forEach(el => {
                el.textContent = 'As of today';
            });
        } else {
            showAlert('Error loading stats: ' + result.error);
        }
    } catch (error) {
        console.error('Error loading stats:', error);
        showAlert('Error loading stats: ' + error.message);
    }
}

// ============================================
// USERS
// ============================================
async function loadUsers(search = '') {
    const container = document.getElementById('users-table-content');
    showLoading(container);
    
    try {
        const response = await fetch(`${API_BASE}?action=users&search=${encodeURIComponent(search)}`);
        const result = await response.json();
        
        if (result.success) {
            renderUsersTable(result.data, container);
        } else {
            showError(container, result.error);
        }
    } catch (error) {
        showError(container, error.message);
    }
}

function renderUsersTable(users, container) {
    if (users.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">👤</div>
                <div class="empty-state-text">No users found</div>
                <button class="btn btn-white" onclick="openUserModal()">Add First User</button>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Student ID</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    users.forEach(user => {
        html += `
            <tr>
                <td>${user.id}</td>
                <td>${escapeHtml(user.full_name)}</td>
                <td>${escapeHtml(user.email)}</td>
                <td>${user.student_id || '—'}</td>
                <td><span class="badge ${user.role === 'admin' ? 'badge-danger' : 'badge-info'}">${user.role}</span></td>
                <td><span class="badge ${user.is_active ? 'badge-success' : 'badge-warning'}">${user.is_active ? 'Active' : 'Inactive'}</span></td>
                <td>${formatDate(user.created_at)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn btn-icon btn-edit" onclick="editUser(${user.id})">Edit</button>
                        <button class="btn btn-icon btn-delete" onclick="deleteUser(${user.id}, '${escapeHtml(user.full_name)}')">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function editUser(id) {
    try {
        const response = await fetch(`${API_BASE}?action=users&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            openUserModal(result.data);
        }
    } catch (error) {
        showAlert('Error loading user details');
    }
}

async function deleteUser(id, name) {
    if (!confirm(`Are you sure you want to delete user "${name}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=users`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('User deleted successfully');
            loadUsers();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert('Error deleting user');
    }
}

function openUserModal(userData = null) {
    const isEdit = userData !== null;
    const modalHtml = `
        <div class="modal active" id="user-modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">${isEdit ? 'Edit User' : 'Add User'}</h2>
                    <button class="modal-close" onclick="closeModal('user-modal')">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="user-form">
                        ${isEdit ? `<input type="hidden" name="id" value="${userData.id}">` : ''}
                        
                        <div class="form-group">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-input" name="full_name" value="${isEdit ? escapeHtml(userData.full_name) : ''}" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-input" name="email" value="${isEdit ? escapeHtml(userData.email) : ''}" required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Student ID</label>
                            <input type="text" class="form-input" name="student_id" value="${isEdit && userData.student_id ? escapeHtml(userData.student_id) : ''}">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" required>
                                <option value="student" ${isEdit && userData.role === 'student' ? 'selected' : ''}>Student</option>
                                <option value="admin" ${isEdit && userData.role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        </div>
                        
                        ${!isEdit ? `
                        <div class="form-group">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-input" name="password" required>
                        </div>
                        ` : ''}
                        
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="is_active">
                                <option value="1" ${isEdit && userData.is_active ? 'selected' : ''}>Active</option>
                                <option value="0" ${isEdit && !userData.is_active ? 'selected' : ''}>Inactive</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" onclick="closeModal('user-modal')">Cancel</button>
                    <button class="btn btn-primary" onclick="saveUser(${isEdit})">${isEdit ? 'Update' : 'Create'} User</button>
                </div>
            </div>
        </div>
    `;
    
    document.getElementById('modal-container').innerHTML = modalHtml;
}

async function saveUser(isEdit) {
    const form = document.getElementById('user-form');
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    try {
        const response = await fetch(`${API_BASE}?action=users`, {
            method: isEdit ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert(`User ${isEdit ? 'updated' : 'created'} successfully`);
            closeModal('user-modal');
            loadUsers();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert(`Error ${isEdit ? 'updating' : 'creating'} user`);
    }
}

// ============================================
// COURSES
// ============================================
async function loadCourses(search = '') {
    const container = document.getElementById('courses-table-content');
    showLoading(container);
    
    try {
        const response = await fetch(`${API_BASE}?action=courses&search=${encodeURIComponent(search)}`);
        const result = await response.json();
        
        if (result.success) {
            renderCoursesTable(result.data, container);
        } else {
            showError(container, result.error);
        }
    } catch (error) {
        showError(container, error.message);
    }
}

function renderCoursesTable(courses, container) {
    if (courses.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📚</div>
                <div class="empty-state-text">No courses found</div>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Credits</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    courses.forEach(course => {
        html += `
            <tr>
                <td><strong>${escapeHtml(course.course_code)}</strong></td>
                <td>${escapeHtml(course.course_name)}</td>
                <td>${escapeHtml(course.dept_name)}</td>
                <td>${course.credits}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn btn-icon btn-edit" onclick="editCourse(${course.course_id})">Edit</button>
                        <button class="btn btn-icon btn-delete" onclick="deleteCourse(${course.course_id}, '${escapeHtml(course.course_code)}')">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function editCourse(id) {
    showAlert('Course editing coming soon!');
}

async function deleteCourse(id, code) {
    if (!confirm(`Are you sure you want to delete course "${code}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=courses`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ course_id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Course deleted successfully');
            loadCourses();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert('Error deleting course');
    }
}

// ============================================
// DEPARTMENTS
// ============================================
async function loadDepartments() {
    const container = document.getElementById('departments-table-content');
    showLoading(container);
    
    try {
        const response = await fetch(`${API_BASE}?action=departments`);
        const result = await response.json();
        
        if (result.success) {
            renderDepartmentsTable(result.data, container);
        } else {
            showError(container, result.error);
        }
    } catch (error) {
        showError(container, error.message);
    }
}

function renderDepartmentsTable(departments, container) {
    if (departments.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🏛️</div>
                <div class="empty-state-text">No departments found</div>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Courses</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    departments.forEach(dept => {
        html += `
            <tr>
                <td>${dept.dept_id}</td>
                <td><strong>${escapeHtml(dept.dept_code)}</strong></td>
                <td>${escapeHtml(dept.dept_name)}</td>
                <td><span class="badge badge-info">${dept.course_count}</span></td>
                <td>${formatDate(dept.created_at)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn btn-icon btn-edit" onclick="editDepartment(${dept.dept_id})">Edit</button>
                        <button class="btn btn-icon btn-delete" onclick="deleteDepartment(${dept.dept_id}, '${escapeHtml(dept.dept_name)}')">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function editDepartment(id) {
    showAlert('Department editing coming soon!');
}

async function deleteDepartment(id, name) {
    if (!confirm(`Are you sure you want to delete department "${name}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=departments`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ dept_id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Department deleted successfully');
            loadDepartments();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert('Error deleting department');
    }
}

// ============================================
// PREREQUISITES
// ============================================
async function loadPrerequisites() {
    const container = document.getElementById('prerequisites-table-content');
    showLoading(container);
    
    try {
        const response = await fetch(`${API_BASE}?action=prerequisites`);
        const result = await response.json();
        
        if (result.success) {
            renderPrerequisitesTable(result.data, container);
        } else {
            showError(container, result.error);
        }
    } catch (error) {
        showError(container, error.message);
    }
}

function renderPrerequisitesTable(prerequisites, container) {
    if (prerequisites.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">🔗</div>
                <div class="empty-state-text">No prerequisites found</div>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>Course</th>
                    <th>Course Name</th>
                    <th>Prerequisite</th>
                    <th>Prerequisite Name</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    prerequisites.forEach(prereq => {
        html += `
            <tr>
                <td><strong>${escapeHtml(prereq.course_code)}</strong></td>
                <td>${escapeHtml(prereq.course_name)}</td>
                <td><strong>${escapeHtml(prereq.prereq_code)}</strong></td>
                <td>${escapeHtml(prereq.prereq_name)}</td>
                <td><span class="badge ${prereq.prerequisite_type === 'required' ? 'badge-danger' : 'badge-warning'}">${prereq.prerequisite_type}</span></td>
                <td>
                    <div class="action-btns">
                        <button class="btn btn-icon btn-delete" onclick="deletePrerequisite(${prereq.prereq_id})">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function deletePrerequisite(id) {
    if (!confirm('Are you sure you want to delete this prerequisite?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=prerequisites`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ prereq_id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Prerequisite deleted successfully');
            loadPrerequisites();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert('Error deleting prerequisite');
    }
}

// ============================================
// REGISTRATIONS
// ============================================
async function loadRegistrations(search = '') {
    const container = document.getElementById('registrations-table-content');
    showLoading(container);
    
    try {
        const response = await fetch(`${API_BASE}?action=registrations&search=${encodeURIComponent(search)}`);
        const result = await response.json();
        
        if (result.success) {
            renderRegistrationsTable(result.data, container);
        } else {
            showError(container, result.error);
        }
    } catch (error) {
        showError(container, error.message);
    }
}

function renderRegistrationsTable(registrations, container) {
    if (registrations.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <div class="empty-state-icon">📋</div>
                <div class="empty-state-text">No registrations found</div>
            </div>
        `;
        return;
    }
    
    let html = `
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Student</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
    `;
    
    registrations.forEach(reg => {
        const statusClass = {
            'pending': 'badge-warning',
            'verified': 'badge-info',
            'processing': 'badge-info',
            'completed': 'badge-success',
            'failed': 'badge-danger'
        }[reg.registration_status] || 'badge-info';
        
        html += `
            <tr>
                <td>${reg.registration_id}</td>
                <td>${escapeHtml(reg.full_name || 'Unknown')}</td>
                <td>${escapeHtml(reg.student_email)}</td>
                <td><span class="badge ${statusClass}">${reg.registration_status}</span></td>
                <td>${formatDate(reg.submitted_at)}</td>
                <td>
                    <div class="action-btns">
                        <button class="btn btn-icon btn-delete" onclick="deleteRegistration(${reg.registration_id})">Delete</button>
                    </div>
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function deleteRegistration(id) {
    if (!confirm('Are you sure you want to delete this registration?')) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}?action=registrations`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ registration_id: id })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showAlert('Registration deleted successfully');
            loadRegistrations();
        } else {
            showAlert(result.error);
        }
    } catch (error) {
        showAlert('Error deleting registration');
    }
}

// ============================================
// UTILITY FUNCTIONS
// ============================================
function setupSearchBoxes() {
    const searchBoxes = {
        'search-users': () => loadUsers(document.getElementById('search-users').value),
        'search-courses': () => loadCourses(document.getElementById('search-courses').value),
        'search-registrations': () => loadRegistrations(document.getElementById('search-registrations').value)
    };
    
    Object.keys(searchBoxes).forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener('input', debounce(searchBoxes[id], 300));
        }
    });
}

function showLoading(container) {
    container.innerHTML = '<div class="loading"><div class="spinner"></div><p>Loading...</p></div>';
}

function showError(container, message) {
    container.innerHTML = `<div class="empty-state"><div class="empty-state-text">Error: ${escapeHtml(message)}</div></div>`;
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.remove();
    }
}

function showAlert(message) {
    alert(message);
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString) {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}