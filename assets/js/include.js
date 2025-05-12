/**
 * PTIT Student Management System
 * Dynamic HTML includes loader
 */

document.addEventListener('DOMContentLoaded', function() {
    // Load Header if element with id 'header' exists
    const headerElement = document.getElementById('header');
    if (headerElement) {
        loadHtmlComponent('../../views/includes/header.html', headerElement).then(() => {
            // After header is loaded, set active link and user info
            updateUserInfo();
            setupHeaderLinks();
        });
    }

    // Load Footer if element with id 'footer' exists
    const footerElement = document.getElementById('footer');
    if (footerElement) {
        loadHtmlComponent('../../views/includes/footer.html', footerElement);
    }

    // Load Admin Sidebar if element with id 'admin-sidebar' exists
    const adminSidebarElement = document.getElementById('admin-sidebar');
    if (adminSidebarElement) {
        loadHtmlComponent('../../views/includes/admin_sidebar.html', adminSidebarElement).then(() => {
            setActiveSidebarLink();
        });
    }

    // Load Student Sidebar if element with id 'student-sidebar' exists
    const studentSidebarElement = document.getElementById('student-sidebar');
    if (studentSidebarElement) {
        loadHtmlComponent('../../views/includes/student_sidebar.html', studentSidebarElement).then(() => {
            setActiveSidebarLink();
        });
    }

    // Load flash messages if they exist in session
    loadFlashMessages();
});

/**
 * Load HTML component from a URL and inject into a target element
 * @param {string} url - The URL of the HTML component to load
 * @param {HTMLElement} targetElement - The element to inject the HTML into
 * @returns {Promise} - A promise that resolves when the component is loaded
 */
function loadHtmlComponent(url, targetElement) {
    return fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error(`Failed to load ${url}: ${response.status} ${response.statusText}`);
            }
            return response.text();
        })
        .then(html => {
            targetElement.innerHTML = html;
            // Execute any scripts in the loaded HTML
            const scripts = targetElement.querySelectorAll('script');
            scripts.forEach(script => {
                const newScript = document.createElement('script');
                Array.from(script.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });
                newScript.appendChild(document.createTextNode(script.innerHTML));
                script.parentNode.replaceChild(newScript, script);
            });
            return targetElement;
        })
        .catch(error => {
            console.error('Error loading component:', error);
            targetElement.innerHTML = `<div class="alert alert-danger">Failed to load component: ${error.message}</div>`;
        });
}

/**
 * Update user information in the header
 */
function updateUserInfo() {
    const userNameElement = document.getElementById('user-name');
    if (userNameElement) {
        // Fetch current user information from session API
        fetch('../../controllers/public/get_current_user.php')
            .then(response => response.json())
            .then(data => {
                if (data.logged_in) {
                    userNameElement.textContent = data.full_name || data.username;
                    
                    // Update profile link based on user role
                    const profileLink = document.getElementById('profile-link');
                    if (profileLink && data.role) {
                        if (data.role === 'admin') {
                            profileLink.href = '../../views/admin/profile.html';
                        } else {
                            profileLink.href = '../../views/student/profile.html';
                        }
                    }
                    
                    // Update header home link based on user role
                    const homeLink = document.getElementById('header-home-link');
                    if (homeLink && data.role) {
                        if (data.role === 'admin') {
                            homeLink.href = '../../views/admin/dashboard.html';
                        } else if (data.role === 'student') {
                            homeLink.href = '../../views/student/dashboard.html';
                        }
                    }
                } else {
                    // User not logged in
                    document.querySelector('#navbarNav').innerHTML = `
                        <ul class="navbar-nav ms-auto">
                            <li class="nav-item">
                                <a class="nav-link" href="../../views/public/login.html">
                                    <i class="fas fa-sign-in-alt me-1"></i> Đăng nhập
                                </a>
                            </li>
                        </ul>
                    `;
                }
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                userNameElement.textContent = 'Guest';
            });
    }
}

/**
 * Setup event listeners for header links
 */
function setupHeaderLinks() {
    // No additional setup needed for now
}

/**
 * Set active link in sidebar based on current page
 */
function setActiveSidebarLink() {
    // Get the current page path
    const currentPath = window.location.pathname;
    const filename = currentPath.split('/').pop();
    
    // Handle admin_sidebar.html (list-group-item-action)
    document.querySelectorAll('.list-group-item-action').forEach(link => {
        // Remove active class from all links
        link.classList.remove('active');
        
        // Get the href attribute
        const href = link.getAttribute('href');
        
        // If href ends with current filename, add active class
        if (href && href.endsWith(filename)) {
            link.classList.add('active');
        }
    });
    
    // Handle student_sidebar.html (nav-link)
    document.querySelectorAll('.sidebar .nav-link').forEach(link => {
        // Remove active class from all links
        link.classList.remove('active');
        
        // Get the href attribute
        const href = link.getAttribute('href');
        
        // If href ends with current filename, add active class
        if (href && href.endsWith(filename)) {
            link.classList.add('active');
        }
    });
    
    // Also set active based on sidebar ID
    const pageId = getPageId(filename);
    if (pageId) {
        const sidebarLink = document.getElementById(pageId);
        if (sidebarLink) {
            sidebarLink.classList.add('active');
        }
    }
}

/**
 * Get page ID for sidebar active link
 * @param {string} filename - The current page filename
 * @returns {string|null} - The sidebar link ID or null
 */
function getPageId(filename) {
    const pageIds = {
        'dashboard.html': 'sidebar-dashboard',
        'students.html': 'sidebar-students',
        'grades.html': 'sidebar-grades',
        'subjects.html': 'sidebar-subjects',
        'semesters.html': 'sidebar-semesters',
        'reports.html': 'sidebar-reports',
        'notifications.html': 'sidebar-notifications',
        'profile.html': 'sidebar-profile'
    };
    
    return pageIds[filename] || null;
}

/**
 * Load flash messages from session
 */
function loadFlashMessages() {
    const flashMessagesElement = document.getElementById('flash-messages');
    if (flashMessagesElement) {
        fetch('../../controllers/public/get_flash_messages.php')
            .then(response => response.json())
            .then(data => {
                if (data.messages && data.messages.length > 0) {
                    const messagesHtml = data.messages.map(message => {
                        return `
                            <div class="alert alert-${message.type} alert-dismissible fade show" role="alert">
                                ${message.text}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        `;
                    }).join('');
                    
                    flashMessagesElement.innerHTML = messagesHtml;
                }
            })
            .catch(error => {
                console.error('Error loading flash messages:', error);
            });
    }
} 