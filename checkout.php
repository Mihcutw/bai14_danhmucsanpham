<?php
// Bật hiển thị lỗi để debug (trừ Notice để tránh thông báo không cần thiết)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

include 'config.php';

// Chỉ gọi session_start() nếu chưa có session active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

try {
    // Kiểm tra kết nối cơ sở dữ liệu
    if (!$conn_store) {
        throw new Exception("Không thể kết nối đến cơ sở dữ liệu!");
    }

    // Lấy hoặc gán user_id
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    // Xử lý xác nhận thanh toán
    $message = '';
    if (isset($_GET['confirm']) && $_GET['confirm'] === 'true') {
        // Lấy danh sách sản phẩm trong giỏ hàng
        $stmt = $conn_store->prepare('
            SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ');
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        if (!empty($cart_items)) {
            // Tính tổng tiền
            $total = 0;
            foreach ($cart_items as $item) {
                $total += $item['price'] * $item['quantity'];
            }

            // Thêm đơn hàng vào bảng orders
            $stmt = $conn_store->prepare('INSERT INTO orders (user_id, total) VALUES (?, ?)');
            $stmt->execute([$user_id, $total]);
            $order_id = $conn_store->lastInsertId();

            // Thêm chi tiết đơn hàng vào bảng order_items
            $stmt = $conn_store->prepare('
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ');
            foreach ($cart_items as $item) {
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            }

            // Xóa giỏ hàng
            $stmt = $conn_store->prepare('DELETE FROM cart WHERE user_id = ?');
            $stmt->execute([$user_id]);

            $message = "Thanh toán thành công! Cảm ơn bạn đã mua hàng.";
        } else {
            $message = "Giỏ hàng trống, không thể thanh toán!";
        }
    }

    // Lấy danh sách sản phẩm trong giỏ hàng để hiển thị
    $stmt = $conn_store->prepare('
        SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ');
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();

} catch (Exception $e) {
    echo "Lỗi: " . $e->getMessage();
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="checkout-wrapper">
    <div class="checkout-container">
        <h2>Thanh Toán</h2>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if (empty($message)): ?>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Ảnh</th>
                            <th>Tên Sản Phẩm</th>
                            <th>Giá</th>
                            <th>Số Lượng</th>
                            <th>Tổng</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <?php if ($item['image'] && file_exists($item['image'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="max-width: 50px; max-height: 50px;">
                                    <?php else: ?>
                                        <span>Không có ảnh</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td class="price"><?php echo number_format($item['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                                <td class="stock"><?php echo $item['quantity']; ?></td>
                                <td class="price"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.') . ' VNĐ'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($cart_items)): ?>
                            <tr>
                                <td colspan="5" class="empty-message">Giỏ hàng của bạn hiện đang trống</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if (!empty($cart_items)): ?>
                <div class="cart-total">
                    <h3>Tổng cộng: <?php
                        $total = 0;
                        foreach ($cart_items as $item) {
                            $total += $item['price'] * $item['quantity'];
                        }
                        echo number_format($total, 0, ',', '.') . ' VNĐ';
                    ?></h3>
                    <a href="checkout.php?confirm=true" 
                       class="btn btn-confirm" 
                       onclick="return confirm('Bạn có chắc chắn muốn xác nhận thanh toán?');">Xác Nhận Thanh Toán</a>
                    <a href="cart.php" class="back-link">Quay Lại Giỏ Hàng</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="success-message">
                <p>Đơn hàng của bạn đã được xử lý. Bạn có thể quay lại <a href="products.php">Danh sách sản phẩm</a> để tiếp tục mua sắm.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
body {
    font-family: Arial, sans-serif;
    background-image: url(images/123.jpg);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    margin: 0;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.checkout-wrapper {
    flex: 1;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-bottom: 60px;
}

.checkout-container {
    background-color: #fff;
    padding: 2rem;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(147, 112, 219, 0.1);
    border: 2px solid transparent;
    background: linear-gradient(#fff, #fff) padding-box, linear-gradient(90deg, #00eaff, #ff007a) border-box;
    width: 100%;
    max-width: 800px;
    animation: fadeInDown 0.6s ease-in-out;
}

@keyframes fadeInDown {
    0% { opacity: 0; transform: translateY(-20px); }
    100% { opacity: 1; transform: translateY(0); }
}

h2 {
    background: linear-gradient(90deg, #3915bb, #b424b4);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-align: center;
    margin-bottom: 1.5rem;
}

h3 {
    color: #4682b4;
    margin-bottom: 1rem;
    text-align: center;
}

.message {
    background: #d4edda;
    color: #155724;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

.success-message {
    background: #d4edda;
    color: #155724;
    padding: 20px;
    border-radius: 5px;
    text-align: center;
}

.success-message a {
    color: #9370db;
    text-decoration: none;
}

.success-message a:hover {
    color: #4682b4;
    text-decoration: underline;
}

.table-wrapper {
    margin-bottom: 1rem;
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #b0c4de;
}

th {
    background: linear-gradient(90deg, #3915bb, #b424b4);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

tbody tr:hover {
    background: rgba(147, 112, 219, 0.1);
}

.price {
    color: #ff4081;
    font-weight: 500;
}

.stock {
    color: #2ecc71;
    font-weight: 500;
}

.empty-message {
    text-align: center;
    padding: 20px;
    color: #7f8c8d;
    font-style: italic;
}

.cart-total {
    text-align: center;
    margin-top: 1rem;
}

.btn-confirm {
    display: inline-block;
    padding: 0.8rem 2rem;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    background: linear-gradient(90deg, #9370db, #4682b4);
    color: white;
    font-weight: bold;
    text-decoration: none;
    margin-top: 1rem;
}

.btn-confirm:hover {
    background: linear-gradient(90deg, #7b68ee, #87ceeb);
    box-shadow: 0 2px 10px rgba(70, 130, 180, 0.5);
}

.back-link {
    display: block;
    text-align: center;
    margin-top: 1rem;
    color: #9370db;
    text-decoration: none;
}

.back-link:hover {
    color: #4682b4;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .checkout-container {
        padding: 1rem;
        max-width: 100%;
    }

    table {
        display: block;
        overflow-x: auto;
    }

    th, td {
        min-width: 120px;
    }
}
</style>