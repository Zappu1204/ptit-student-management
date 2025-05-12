    </main>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>PTIT Student Management System</h5>
                    <p>Hệ thống quản lý sinh viên hiện đại, tiện lợi và dễ sử dụng.</p>
                </div>
                <div class="col-md-3">
                    <h5>Liên kết</h5>
                    <ul class="list-unstyled">
                        <?php if (!isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo $base_path; ?>views/public/login.php" class="text-white">Đăng nhập</a></li>
                        <?php else: ?>
                        <li><a href="<?php echo $base_path; ?>controllers/public/logout_process.php" class="text-white">Đăng xuất</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Liên hệ</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-map-marker-alt me-2"></i> Hà Nội, Việt Nam</li>
                        <li><i class="fas fa-envelope me-2"></i> info@ptit.edu.vn</li>
                        <li><i class="fas fa-phone me-2"></i> (84) 123 456 789</li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p class="mb-0">© 2025 PTIT Student Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    <!-- Custom Script -->
    <script src="<?php echo $base_path; ?>assets/js/script.js"></script>
</body>
</html>
