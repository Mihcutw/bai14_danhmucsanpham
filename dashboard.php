<?php
// Chỉ gọi session_start() nếu chưa có session active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Thông tin kết nối
$host = "localhost";
$username = "root"; // Thay bằng username MySQL của bạn
$password = ""; // Thay bằng password MySQL của bạn
$dbname_store = "store_management";
$dbname_user = "user_management";

try {
    // Kết nối đến store_management
    $conn_store = new PDO("mysql:host=$host;dbname=$dbname_store", $username, $password);
    $conn_store->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_store->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Store connection failed: " . $e->getMessage());
}

try {
    // Kết nối đến user_management
    $conn_user = new PDO("mysql:host=$host;dbname=$dbname_user", $username, $password);
    $conn_user->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn_user->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("User connection failed: " . $e->getMessage());
}

$page_title = "Dashboard";

// Khởi tạo biến số liệu
$product_count = 0;
$user_count = 0;
$order_count = 0;

// Đếm số sản phẩm từ bảng products (store_management)
try {
    $stmt = $conn_store->prepare("SELECT COUNT(*) AS count FROM products");
    $stmt->execute();
    $product_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $product_count = "Lỗi: " . $e->getMessage();
}

// Đếm số người dùng từ bảng users (user_management)
try {
    $stmt = $conn_user->prepare("SELECT COUNT(*) AS count FROM users");
    $stmt->execute();
    $user_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $user_count = "Lỗi: " . $e->getMessage();
}

// Đếm số đơn hàng từ bảng orders (store_management)
try {
    $stmt = $conn_store->prepare("SELECT COUNT(*) AS count FROM orders");
    $stmt->execute();
    $order_count = $stmt->fetch()['count'];
} catch (PDOException $e) {
    $order_count = "Lỗi: " . $e->getMessage();
}

include 'header.php';
?>

<div class="stats">
    <div class="stat-box">
        <h3>Sản phẩm</h3>
        <p><?php echo htmlspecialchars($product_count); ?></p>
    </div>
    <div class="stat-box">
        <h3>Người dùng</h3>
        <p><?php echo htmlspecialchars($user_count); ?></p>
    </div>
    <div class="stat-box">
        <h3>Đơn hàng</h3>
        <p><?php echo htmlspecialchars($order_count); ?></p>
    </div>  
</div>

<a href="products.php" class="btn">Xem Danh Sách Sản Phẩm</a>

<?php include 'footer.php'; ?>

<style>
    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .stat-box {
        background: #fff;
        padding: 25px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .stat-box:hover {
        transform: translateY(-5px);
    }

    .stat-box h3 {
        color: #666;
        font-size: 16px;
        margin-bottom: 15px;
    }

    .stat-box p {
        font-size: 28px;
        font-weight: bold;
        color: #333;
    }

    .btn {
        display: inline-block;
        padding: 12px 25px;
        background: #1a73e8;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

    .btn:hover {
        background-color: #1557b0;
    }

    @media (max-width: 768px) {
        .stats {
            grid-template-columns: 1fr;
        }
    }
</style>