/**
 * HRIS Management System - Main JavaScript
 */

(function($) {
    'use strict';

    // Initialize on document ready
    $(document).ready(function() {
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
        
        // Initialize popovers
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.map(function (popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });
        
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
    });

    // Initialize DataTable with default options
    window.initDataTable = function(selector, options = {}) {
        const defaultOptions = {
            responsive: true,
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search...",
                lengthMenu: "Show _MENU_ entries",
                info: "Showing _START_ to _END_ of _TOTAL_ entries",
                infoEmpty: "Showing 0 to 0 of 0 entries",
                infoFiltered: "(filtered from _MAX_ total entries)",
                zeroRecords: "No matching records found",
                emptyTable: "No data available in table",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        };
        
        return $(selector).DataTable($.extend({}, defaultOptions, options));
    };

    // Delete confirmation with SweetAlert2
    window.confirmDelete = function(url, title = 'Are you sure?', text = 'You won\'t be able to revert this!') {
        Swal.fire({
            title: title,
            text: text,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e74a3b',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = url;
            }
        });
    };

    // Show success message
    window.showSuccess = function(message, title = 'Success!') {
        Swal.fire({
            icon: 'success',
            title: title,
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    };

    // Show error message
    window.showError = function(message, title = 'Error!') {
        Swal.fire({
            icon: 'error',
            title: title,
            text: message,
            confirmButtonColor: '#667eea'
        });
    };

    // Show loading
    window.showLoading = function(message = 'Please wait...') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    };

    // Hide loading
    window.hideLoading = function() {
        Swal.close();
    };

    // Image preview
    window.previewImage = function(input, previewId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                $(previewId).html('<img src="' + e.target.result + '" alt="Preview">');
            };
            
            reader.readAsDataURL(input.files[0]);
        }
    };

    // Form validation
    window.validateForm = function(formId) {
        const form = document.getElementById(formId);
        
        if (!form.checkValidity()) {
            form.classList.add('was-validated');
            return false;
        }
        
        return true;
    };

    // Format currency
    window.formatCurrency = function(amount) {
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    };

    // Format date
    window.formatDate = function(date) {
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        return new Date(date).toLocaleDateString('id-ID', options);
    };

    // AJAX form submit
    window.submitFormAjax = function(formId, successCallback, errorCallback) {
        const form = $('#' + formId);
        const formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            type: form.attr('method'),
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                showLoading();
            },
            success: function(response) {
                hideLoading();
                if (successCallback) {
                    successCallback(response);
                }
            },
            error: function(xhr, status, error) {
                hideLoading();
                if (errorCallback) {
                    errorCallback(xhr, status, error);
                } else {
                    showError('An error occurred. Please try again.');
                }
            }
        });
    };

    // Export table to CSV
    window.exportTableToCSV = function(tableId, filename = 'export.csv') {
        const table = document.getElementById(tableId);
        let csv = [];
        const rows = table.querySelectorAll('tr');
        
        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            
            for (let j = 0; j < cols.length; j++) {
                row.push(cols[j].innerText);
            }
            
            csv.push(row.join(','));
        }
        
        downloadCSV(csv.join('\n'), filename);
    };

    // Download CSV
    function downloadCSV(csv, filename) {
        const csvFile = new Blob([csv], { type: 'text/csv' });
        const downloadLink = document.createElement('a');
        
        downloadLink.download = filename;
        downloadLink.href = window.URL.createObjectURL(csvFile);
        downloadLink.style.display = 'none';
        
        document.body.appendChild(downloadLink);
        downloadLink.click();
        document.body.removeChild(downloadLink);
    }

    // Print element
    window.printElement = function(elementId) {
        const printContents = document.getElementById(elementId).innerHTML;
        const originalContents = document.body.innerHTML;
        
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    };

    // Debounce function
    window.debounce = function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    };

    // Auto-generate employee code
    window.generateEmployeeCode = function() {
        $.ajax({
            url: '/hrm/admin/employees/ajax_handler.php',
            type: 'POST',
            data: { action: 'generate_code' },
            success: function(response) {
                if (response.success) {
                    $('#employee_code').val(response.code);
                }
            },
            error: function() {
                showError('Failed to generate employee code');
            }
        });
    };

    // Calculate age from date of birth
    window.calculateAge = function(dateOfBirth) {
        const today = new Date();
        const birthDate = new Date(dateOfBirth);
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }
        
        return age;
    };

})(jQuery);
