<?php
/**
 * View Helper Functions
 * PTIT Student Management System
 * 
 * Provides functions to help with rendering views and managing view data
 */

// Define base paths
define('BASE_PATH', dirname(__DIR__));
define('VIEWS_PATH', BASE_PATH . '/views');

/**
 * Render a view file with data
 * 
 * @param string $view The view file path relative to views directory
 * @param array $data Data to extract into the view
 * @return void
 */
function render($view, $data = []) {
    if (!empty($data)) {
        extract($data);
    }
    
    // Include the header based on user type
    if (isset($is_admin) && $is_admin) {
        include_once VIEWS_PATH . '/includes/header.php';
        include_once VIEWS_PATH . '/includes/admin_sidebar.php';
    } elseif (isset($is_student) && $is_student) {
        include_once VIEWS_PATH . '/includes/header.php';
        include_once VIEWS_PATH . '/includes/student_sidebar.php';
    } else {
        include_once VIEWS_PATH . '/includes/header.php';
    }
    
    // Include the main view file
    include_once VIEWS_PATH . '/' . $view . '.php';
    
    // Include the footer
    include_once VIEWS_PATH . '/includes/footer.php';
}

/**
 * Redirect to a URL
 * 
 * @param string $url The URL to redirect to
 * @return void
 */
function redirectTo($url) {
    header('Location: ' . $url);
    exit;
}

/**
 * Set a flash message to be displayed on the next page load
 * 
 * @param string $type The type of message (success, error, warning, info)
 * @param string $message The message text
 * @return void
 */
function setViewFlashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash_messages'][] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Display all flash messages and clear them
 * 
 * @return string HTML for the flash messages
 */
function displayViewFlashMessages() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $output = '';
    
    if (isset($_SESSION['flash_messages']) && !empty($_SESSION['flash_messages'])) {
        foreach ($_SESSION['flash_messages'] as $message) {
            $alertClass = 'alert-info';
            
            switch ($message['type']) {
                case 'success':
                    $alertClass = 'alert-success';
                    break;
                case 'error':
                    $alertClass = 'alert-danger';
                    break;
                case 'warning':
                    $alertClass = 'alert-warning';
                    break;
            }
            
            $output .= '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
            $output .= $message['message'];
            $output .= '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            $output .= '</div>';
        }
        
        // Clear flash messages
        $_SESSION['flash_messages'] = [];
    }
    
    return $output;
}

/**
 * Get the correct path for assets based on the current page location
 * 
 * @param string $path The path to the asset relative to the assets directory
 * @return string The full path to the asset
 */
function asset($path) {
    // Determine if we're in a subdirectory
    $is_subdir = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || 
                 strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false ||
                 strpos($_SERVER['SCRIPT_NAME'], '/controllers/') !== false;
    
    return ($is_subdir ? '../' : '') . 'assets/' . ltrim($path, '/');
}

/**
 * Get the URL for a controller
 * 
 * @param string $controller The controller path
 * @return string The URL to the controller
 */
function controllerUrl($controller) {
    // Determine if we're in a subdirectory
    $is_subdir = strpos($_SERVER['SCRIPT_NAME'], '/admin/') !== false || 
                 strpos($_SERVER['SCRIPT_NAME'], '/student/') !== false ||
                 strpos($_SERVER['SCRIPT_NAME'], '/views/') !== false;
    
    return ($is_subdir ? '../' : '') . 'controllers/' . ltrim($controller, '/');
}

/**
 * Include a view template
 * 
 * @param string $template Path to the template file, relative to the views directory
 * @param array $data Data to pass to the template
 * @return void
 */
function includeView($template, $data = []) {
    // Extract data to make variables available in the template
    extract($data);
    
    $templatePath = __DIR__ . '/../views/' . $template;
    
    if (!file_exists($templatePath)) {
        echo "Template not found: $template";
        return;
    }
    
    include $templatePath;
}

/**
 * Include a layout
 * 
 * @param string $layout Layout name (e.g., 'admin', 'student', 'public')
 * @param string $template Template to include in the layout
 * @param array $data Data to pass to the template
 * @param string $pageTitle Page title
 * @return void
 */
function includeLayout($layout, $template, $data = [], $pageTitle = '') {
    $data['page_title'] = $pageTitle;
    $data['content_template'] = $template;
    
    includeView("layouts/$layout.php", $data);
}

/**
 * Render the admin layout
 * 
 * @param string $template Template to include in the layout
 * @param array $data Data to pass to the template
 * @param string $pageTitle Page title
 * @return void
 */
function renderAdminView($template, $data = [], $pageTitle = '') {
    includeLayout('admin', $template, $data, $pageTitle);
}

/**
 * Render the student layout
 * 
 * @param string $template Template to include in the layout
 * @param array $data Data to pass to the template
 * @param string $pageTitle Page title
 * @return void
 */
function renderStudentView($template, $data = [], $pageTitle = '') {
    includeLayout('student', $template, $data, $pageTitle);
}

/**
 * Render the public layout
 * 
 * @param string $template Template to include in the layout
 * @param array $data Data to pass to the template
 * @param string $pageTitle Page title
 * @return void
 */
function renderPublicView($template, $data = [], $pageTitle = '') {
    includeLayout('public', $template, $data, $pageTitle);
}

/**
 * Escape HTML output to prevent XSS
 * 
 * @param string $string String to escape
 * @return string Escaped string
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Get current page name from URL
 * 
 * @return string Current page name
 */
function getCurrentPage() {
    $path = $_SERVER['REQUEST_URI'];
    $path = parse_url($path, PHP_URL_PATH);
    $path = ltrim($path, '/');
    
    // Remove file extension and query string
    $path = preg_replace('/\.[^.]+$/', '', $path);
    
    // Get the last part of the path
    $parts = explode('/', $path);
    $page = end($parts);
    
    return $page ?: 'index';
}

/**
 * Check if the current page matches a given page name
 * 
 * @param string $page Page name to check
 * @return bool True if current page matches
 */
function isCurrentPage($page) {
    return getCurrentPage() === $page;
}

/**
 * Generate HTML for a menu item, with 'active' class if current page
 * 
 * @param string $url URL of the menu item
 * @param string $label Label of the menu item
 * @param string $icon FontAwesome icon class
 * @param string $page Page name to check for active state
 * @return string HTML for the menu item
 */
function menuItem($url, $label, $icon, $page = '') {
    $page = $page ?: basename($url, '.php');
    $active = isCurrentPage($page) ? 'active' : '';
    
    return "
        <li class='nav-item'>
            <a class='nav-link $active' href='$url'>
                <i class='fas fa-$icon me-2'></i>
                $label
            </a>
        </li>
    ";
}

/**
 * Display error message
 * 
 * @param string $errorMessage Error message to display
 * @return string HTML for error message
 */
function displayError($errorMessage) {
    if (empty($errorMessage)) {
        return '';
    }
    
    return "
        <div class='alert alert-danger alert-dismissible fade show' role='alert'>
            $errorMessage
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    ";
}

/**
 * Display success message
 * 
 * @param string $successMessage Success message to display
 * @return string HTML for success message
 */
function displaySuccess($successMessage) {
    if (empty($successMessage)) {
        return '';
    }
    
    return "
        <div class='alert alert-success alert-dismissible fade show' role='alert'>
            $successMessage
            <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
        </div>
    ";
}

/**
 * Format form errors
 * 
 * @param array $errors Array of error messages
 * @return string HTML for errors
 */
function formatFormErrors($errors) {
    if (empty($errors)) {
        return '';
    }
    
    $html = "<div class='alert alert-danger'><ul class='mb-0'>";
    
    foreach ($errors as $error) {
        $html .= "<li>$error</li>";
    }
    
    $html .= "</ul></div>";
    
    return $html;
}

/**
 * Generate HTML for a breadcrumb
 * 
 * @param array $items Breadcrumb items (url => label)
 * @return string HTML for breadcrumb
 */
function breadcrumb($items) {
    $html = "
        <nav aria-label='breadcrumb'>
            <ol class='breadcrumb'>
    ";
    
    $count = count($items);
    $i = 0;
    
    foreach ($items as $url => $label) {
        $i++;
        $active = ($i === $count) ? 'active' : '';
        $ariaCurrent = ($i === $count) ? 'aria-current="page"' : '';
        
        if ($i === $count) {
            $html .= "<li class='breadcrumb-item $active' $ariaCurrent>$label</li>";
        } else {
            $html .= "<li class='breadcrumb-item'><a href='$url'>$label</a></li>";
        }
    }
    
    $html .= "
            </ol>
        </nav>
    ";
    
    return $html;
}

/**
 * Generate a select dropdown for students
 * 
 * @param string $name Input name
 * @param string $id Input ID
 * @param mixed $selectedId Selected student ID
 * @param string $class CSS class(es)
 * @param bool $required Whether the field is required
 * @return string HTML for select dropdown
 */
function studentSelectDropdown($name, $id, $selectedId = '', $class = 'form-select', $required = true) {
    include_once __DIR__ . '/db_config.php';
    
    $students = fetchAll(
        "SELECT id, full_name, student_id FROM users WHERE role = 'student' ORDER BY full_name",
        []
    );
    
    $requiredAttr = $required ? 'required' : '';
    
    $html = "<select name='$name' id='$id' class='$class' $requiredAttr>";
    $html .= "<option value=''>-- Chọn sinh viên --</option>";
    
    foreach ($students as $student) {
        $selected = ($student['id'] == $selectedId) ? 'selected' : '';
        $html .= "<option value='{$student['id']}' $selected>{$student['full_name']} ({$student['student_id']})</option>";
    }
    
    $html .= "</select>";
    
    return $html;
}

/**
 * Generate a select dropdown for courses
 * 
 * @param string $name Input name
 * @param string $id Input ID
 * @param mixed $selectedId Selected course ID
 * @param string $class CSS class(es)
 * @param bool $required Whether the field is required
 * @return string HTML for select dropdown
 */
function courseSelectDropdown($name, $id, $selectedId = '', $class = 'form-select', $required = true) {
    include_once __DIR__ . '/db_config.php';
    
    $courses = fetchAll(
        "SELECT c.id, c.name, c.course_code, s.name as semester_name, s.academic_year 
         FROM courses c
         LEFT JOIN semesters s ON c.semester_id = s.id
         ORDER BY s.academic_year DESC, s.name DESC, c.name",
        []
    );
    
    $requiredAttr = $required ? 'required' : '';
    
    $html = "<select name='$name' id='$id' class='$class' $requiredAttr>";
    $html .= "<option value=''>-- Chọn môn học --</option>";
    
    foreach ($courses as $course) {
        $selected = ($course['id'] == $selectedId) ? 'selected' : '';
        $semesterInfo = !empty($course['semester_name']) ? " ({$course['semester_name']} {$course['academic_year']})" : '';
        $html .= "<option value='{$course['id']}' $selected>{$course['name']} ({$course['course_code']})$semesterInfo</option>";
    }
    
    $html .= "</select>";
    
    return $html;
}

/**
 * Generate a select dropdown for semesters
 * 
 * @param string $name Input name
 * @param string $id Input ID
 * @param mixed $selectedId Selected semester ID
 * @param string $class CSS class(es)
 * @param bool $required Whether the field is required
 * @return string HTML for select dropdown
 */
function semesterSelectDropdown($name, $id, $selectedId = '', $class = 'form-select', $required = true) {
    include_once __DIR__ . '/db_config.php';
    
    $semesters = fetchAll(
        "SELECT id, name, academic_year, is_current FROM semesters ORDER BY academic_year DESC, name DESC",
        []
    );
    
    $requiredAttr = $required ? 'required' : '';
    
    $html = "<select name='$name' id='$id' class='$class' $requiredAttr>";
    $html .= "<option value=''>-- Chọn học kỳ --</option>";
    
    foreach ($semesters as $semester) {
        $selected = ($semester['id'] == $selectedId) ? 'selected' : '';
        $current = $semester['is_current'] ? ' (Hiện tại)' : '';
        $html .= "<option value='{$semester['id']}' $selected>{$semester['name']} {$semester['academic_year']}$current</option>";
    }
    
    $html .= "</select>";
    
    return $html;
}

/**
 * Generate a confirmation modal
 * 
 * @param string $id Modal ID
 * @param string $title Modal title
 * @param string $message Modal message
 * @param string $confirmButton Text for confirm button
 * @param string $confirmUrl URL for confirm action
 * @param string $buttonClass CSS class for confirm button
 * @return string HTML for confirmation modal
 */
function confirmationModal($id, $title, $message, $confirmButton, $confirmUrl, $buttonClass = 'btn-danger') {
    return "
        <div class='modal fade' id='$id' tabindex='-1' aria-labelledby='{$id}Label' aria-hidden='true'>
            <div class='modal-dialog'>
                <div class='modal-content'>
                    <div class='modal-header'>
                        <h5 class='modal-title' id='{$id}Label'>$title</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                    </div>
                    <div class='modal-body'>
                        $message
                    </div>
                    <div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Hủy</button>
                        <a href='$confirmUrl' class='btn $buttonClass'>$confirmButton</a>
                    </div>
                </div>
            </div>
        </div>
    ";
}

/**
 * Generate sort link for table headers
 * 
 * @param string $column Column name
 * @param string $label Column label
 * @param string $currentSort Current sort column
 * @param string $currentOrder Current sort order
 * @return string HTML for sort link
 */
function sortLink($column, $label, $currentSort, $currentOrder) {
    $url = $_SERVER['PHP_SELF'];
    $queryParams = $_GET;
    
    $queryParams['sort'] = $column;
    $queryParams['order'] = ($currentSort === $column && $currentOrder === 'asc') ? 'desc' : 'asc';
    
    $sortUrl = $url . '?' . http_build_query($queryParams);
    
    $icon = '';
    if ($currentSort === $column) {
        $icon = ($currentOrder === 'asc') ? 
            '<i class="fas fa-sort-up ms-1"></i>' : 
            '<i class="fas fa-sort-down ms-1"></i>';
    } else {
        $icon = '<i class="fas fa-sort ms-1 text-muted"></i>';
    }
    
    return "<a href='$sortUrl' class='text-decoration-none text-dark'>$label $icon</a>";
}
