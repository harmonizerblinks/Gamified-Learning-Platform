<!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><?php echo SITE_NAME; ?></h3>
                    <p>Transform learning into an adventure</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/pages/about.php">About</a></li>
                        <li><a href="/pages/contact.php">Contact</a></li>
                        <li><a href="/pages/faq.php">FAQ</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Legal</h4>
                    <ul>
                        <li><a href="/pages/terms.php">Terms of Service</a></li>
                        <li><a href="/pages/privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    <?php if(isset($extra_js)) echo $extra_js; ?>
</body>
</html>