/**
 * PTIT Student Management System
 * Main JavaScript file
 */

// Wait for document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Bootstrap tooltips
    initTooltips();
    
    // Initialize DataTables if available
    initDataTables();
    
    // Setup alert auto-dismiss
    setupAlertAutoDismiss();
    
    // Setup dynamic date display
    setupDynamicDate();
    
    // Setup confirmation modals
    setupConfirmationModals();
    
    // Print functionality
    setupPrintButtons();
});

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize DataTables if jQuery and DataTables are available
 */
function initDataTables() {
    if (typeof $ !== 'undefined' && $.fn.DataTable) {
        // Default DataTable configuration
        $('.datatable').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/vi.json'
            }
        });
    }
}

/**
 * Setup auto-dismiss for alert messages after 5 seconds
 */
function setupAlertAutoDismiss() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            // Create and dispatch close event
            var closeEvent = new Event('close.bs.alert');
            alert.dispatchEvent(closeEvent);
            
            // Remove the alert
            if (bootstrap && bootstrap.Alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            } else {
                alert.style.display = 'none';
            }
        });
    }, 5000);
}

/**
 * Setup dynamic date display in footer
 */
function setupDynamicDate() {
    var currentYearElements = document.querySelectorAll('.current-year');
    var currentYear = new Date().getFullYear();
    
    currentYearElements.forEach(function(element) {
        element.textContent = currentYear;
    });
}

/**
 * Setup confirmation modals
 */
function setupConfirmationModals() {
    var confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
                return false;
            }
        });
    });
}

/**
 * Setup print buttons
 */
function setupPrintButtons() {
    var printButtons = document.querySelectorAll('.btn-print');
    
    printButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            window.print();
        });
    });
}

/**
 * Format number as grade (1 decimal place)
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
function formatGrade(num) {
    return parseFloat(num).toFixed(1);
}

/**
 * Format date to Vietnamese format (DD/MM/YYYY)
 * @param {string} dateString - Date string to format
 * @returns {string} Formatted date
 */
function formatDate(dateString) {
    var date = new Date(dateString);
    return date.getDate().toString().padStart(2, '0') + '/' + 
           (date.getMonth() + 1).toString().padStart(2, '0') + '/' + 
           date.getFullYear();
}

/**
 * Export table to Excel
 * @param {string} tableId - ID of the table to export
 * @param {string} filename - Filename for the exported Excel file
 */
function exportTableToExcel(tableId, filename = '') {
    if (!filename) filename = 'export_' + new Date().toISOString().slice(0, 10);
    
    var downloadLink;
    var dataType = 'application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableId);
    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
    
    // Create download link element
    downloadLink = document.createElement("a");
    
    document.body.appendChild(downloadLink);
    
    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename + '.xls');
    } else {
        // Create a link to the file
        downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
        
        // Setting the file name
        downloadLink.download = filename + '.xls';
        
        // Triggering the function
        downloadLink.click();
    }
}

/**
 * Search functionality for tables
 * @param {string} inputId - ID of the input field
 * @param {string} tableId - ID of the table to search
 */
function searchTable(inputId, tableId) {
    var input, filter, table, tr, td, i, j, txtValue, found;
    input = document.getElementById(inputId);
    filter = input.value.toUpperCase();
    table = document.getElementById(tableId);
    tr = table.getElementsByTagName("tr");
    
    // Loop through all table rows except the header
    for (i = 1; i < tr.length; i++) {
        found = false;
        td = tr[i].getElementsByTagName("td");
        
        // Loop through all table cells in this row
        for (j = 0; j < td.length; j++) {
            if (td[j]) {
                txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        // Show or hide the row based on whether a match was found
        tr[i].style.display = found ? "" : "none";
    }
}

/**
 * Toggle password visibility
 * @param {string} inputId - ID of the password input field
 * @param {string} iconId - ID of the icon element
 */
function togglePasswordVisibility(inputId, iconId) {
    var passwordInput = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        passwordInput.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}