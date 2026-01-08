        </div> <!-- content-wrapper -->
        
        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <p class="mb-0">
                            &copy; <?php echo date('Y'); ?> HRIS Management System. All rights reserved.
                        </p>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <p class="mb-0">
                            Version 1.0.0 | 
                            <a href="#" class="text-decoration-none">Documentation</a> | 
                            <a href="#" class="text-decoration-none">Support</a>
                        </p>
                    </div>
                </div>
            </div>
        </footer>
    </div> <!-- main-content -->
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Custom JS -->
    <script src="/hrm/assets/js/main.js"></script>
    
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });
        
        // Submenu toggle
        document.querySelectorAll('.submenu-toggle').forEach(function(element) {
            element.addEventListener('click', function(e) {
                e.preventDefault();
                const parent = this.parentElement;
                parent.classList.toggle('open');
            });
        });
        
        // Auto-open active submenu
        document.querySelectorAll('.menu-item.active.has-submenu').forEach(function(element) {
            element.classList.add('open');
        });
        
        // Display flash messages
        <?php
        $flash = getFlashMessage();
        if ($flash):
        ?>
        Swal.fire({
            icon: '<?php echo $flash['type'] === 'success' ? 'success' : 'error'; ?>',
            title: '<?php echo $flash['type'] === 'success' ? 'Success!' : 'Error!'; ?>',
            text: '<?php echo addslashes($flash['message']); ?>',
            timer: 3000,
            showConfirmButton: false
        });
        <?php endif; ?>
    </script>
</body>
</html>
