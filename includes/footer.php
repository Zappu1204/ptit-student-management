    </main>
    
    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <p class="mb-0">
                        <i class="fas fa-school me-2"></i> Học viện Công nghệ Bưu chính Viễn thông PTIT
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-map-marker-alt me-2"></i> 122 Hoàng Quốc Việt, Cầu Giấy, Hà Nội
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-phone me-2"></i> (024) 3756 2963
                    </p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; <span class="current-year">2025</span> PTIT Student Management System</p>
                    <p class="mb-0">Version 1.0.0</p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo isset($is_admin) || isset($is_student) ? '../' : ''; ?>assets/js/script.js"></script>
    
    <?php if (isset($extra_scripts)) echo $extra_scripts; ?>
</body>
</html>