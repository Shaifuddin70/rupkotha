<!-- ======================= FOOTER SECTION ======================= -->

<footer class="footer bg-dark text-light pt-5">
    <div class="container">
        <div class="row">
            <!-- About -->
            <div class="col-md-4">
                <h5 class="text-uppercase mb-3">About Us</h5>
                <p>Rupkotha Properties Bangladesh is committed to offering the best real estate and product solutions with premium quality and support.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-md-2">
                <h5 class="text-uppercase mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="index" class="text-light">Home</a></li>
                    <li><a href="shop" class="text-light">Shop</a></li>
                    <li><a href="cart" class="text-light">Cart</a></li>
                    <li><a href="contact" class="text-light">Contact</a></li>
                </ul>
            </div>

            <!-- Categories -->
            <div class="col-md-3">
                <h5 class="text-uppercase mb-3">Categories</h5>
                <ul class="list-unstyled">
                    <?php
                    $cats = $pdo->query("SELECT id, name FROM categories ORDER BY name")->fetchAll();
                    foreach ($cats as $c) {
                        echo '<li><a href="category?id=' . $c['id'] . '" class="text-light">' . htmlspecialchars($c['name']) . '</a></li>';
                    }
                    ?>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-md-3">
                <h5 class="text-uppercase mb-3">Contact</h5>
                <p><strong>Phone:</strong> +8801791912323</p>
                <p><strong>Email:</strong> info@rupkotha.com</p>
                <p><strong>Address:</strong> Dhaka, Bangladesh</p>
                <div class="social-links mt-3">
                    <a href="#" class="text-light me-2"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="text-light me-2"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="text-light"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>

        <hr class="border-top border-light mt-4">
        <div class="text-center py-2">
            &copy; <?= date('Y') ?> Rupkotha Properties Bangladesh. All rights reserved.
        </div>
    </div>
</footer>
<!-- Place this script tag typically at the end of your <body> or in your footer.php -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" xintegrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>

</body>
</html>