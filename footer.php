<footer style="text-align: center; padding: 20px; color: #7f8c8d; margin-top: 50px;">
        <p>&copy; 2026 Online Accessory Store. All Rights Reserved.</p>
    </footer>

    <?php if (isset($_SESSION['popup'])): ?>
        <div id="toast-message" style="position: fixed; top: 30px; left: 50%; transform: translateX(-50%); background: #2c3e50; color: white; padding: 12px 24px; border-radius: 8px; box-shadow: 0 5px 15px rgba(0,0,0,0.2); z-index: 9999; font-weight: bold; font-size: 1em; text-align: center; transition: opacity 0.5s ease; border-top: 4px solid #2ecc71;">
            <?= htmlspecialchars($_SESSION['popup']) ?>
        </div>
        <script>
            setTimeout(() => {
                let toast = document.getElementById('toast-message');
                if(toast) {
                    toast.style.opacity = '0';
                    setTimeout(() => toast.remove(), 500);
                }
            }, 3000);
        </script>
        <?php unset($_SESSION['popup']); ?>
    <?php endif; ?>
</body>
</html>