    </div> <!-- Close container from header -->
    <footer class="bg-dark text-light mt-5 py-3">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>Vehicle Accessory Store</h5>
                    <p>Your one-stop shop for all vehicle accessories and parts.</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="/index.php" class="text-light">Home</a></li>
                        <li><a href="/products/add.php" class="text-light">Add Product</a></li>
                        <li><a href="/cart.php" class="text-light">Cart</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Account</h5>
                    <ul class="list-unstyled">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="/dashboard.php" class="text-light">Dashboard</a></li>
                            <li><a href="/auth/logout.php" class="text-light">Logout</a></li>
                        <?php else: ?>
                            <li><a href="/auth/login.php" class="text-light">Login</a></li>
                            <li><a href="/auth/register.php" class="text-light">Register</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <hr class="mt-4">
            <div class="text-center">
                <p class="mb-0">&copy; <?php echo date('Y'); ?> Vehicle Accessory Store. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 