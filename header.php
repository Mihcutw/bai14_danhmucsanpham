<?php
// Đảm bảo session đã được bắt đầu trước khi include header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap" rel="stylesheet">
<header>
    <div class="logo-container">
        <div class="logo">
            <img src="./images/pngtree-colorful-blue-purple-gradient-technology-future-sense-streamer-flashing-e-commerce-picture-image_1621913.jpg" alt="Logo">
        </div>
        <div class="web-title">
            <h1>SIGMA SITE</h1>
            <span class="intro-text">Khám phá trải nghiệm mới</span>
            <link href="styles.css" rel="stylesheet">
        </div>
    </div>
    <input type="checkbox" id="nav-toggle" class="nav-toggle">
    <label for="nav-toggle" class="nav-toggle-label"><span></span></label>
    <nav>
        <ul>
            <!-- Điều chỉnh link Trang Chủ dựa trên trạng thái đăng nhập -->
            <li><a href="products.php">Cửa Hàng</a></li>
            <li><a href="cart.php">Giỏ Hàng </a></li>
            <?php
            // Thêm Dashboard và Trang Cá Nhân nếu đã đăng nhập
            if (isset($_SESSION['user'])) {
                echo '<li><a href="dashboard.php">Bảng điều khiển</a></li>';
                echo '<li><a href="profile.php">Trang Cá Nhân</a></li>';
            }
            // Hiển thị Đăng Xuất hoặc Đăng Nhập
            if (isset($_SESSION['user'])) {
                echo '<li><a href="logout.php">Đăng Xuất</a></li>';
            } else {
                echo '<li><a href="login.php">Đăng Nhập</a></li>';
            }
            ?>
        </ul>
    </nav>
</header>