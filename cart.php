<?php
// Bật hiển thị lỗi để debug (trừ Notice để tránh thông báo không cần thiết)
error_reporting(E_ALL & ~E_NOTICE);
ini_set('display_errors', 1);

include 'config.php';

try {
    // Kiểm tra kết nối cơ sở dữ liệu
    if (!$conn_store) {
        throw new Exception("Không thể kết nối đến cơ sở dữ liệu!");
    }

    // Lấy hoặc gán user_id (mặc định là 0 nếu chưa đăng nhập)
    $user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

    // Xử lý xóa sản phẩm khỏi giỏ hàng
    if (isset($_GET['remove'])) {
        $cart_id = (int)$_GET['remove'];
        $stmt = $conn_store->prepare('DELETE FROM cart WHERE id = ? AND user_id = ?');
        $stmt->execute([$cart_id, $user_id]);
        header('Location: cart.php');
        exit;
    }

    // Xử lý thêm sản phẩm vào giỏ hàng
    $message = '';
    if (isset($_GET['id'])) {
        $product_id = (int)$_GET['id'];

        if ($product_id <= 0) {
            $message = "ID sản phẩm không hợp lệ!";
        } else {
            // Kiểm tra sản phẩm có tồn tại
            $stmt = $conn_store->prepare('SELECT id FROM products WHERE id = ?');
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            if ($product) {
                // Kiểm tra sản phẩm đã có trong giỏ hàng chưa
                $stmt = $conn_store->prepare('SELECT id, quantity FROM cart WHERE product_id = ? AND user_id = ?');
                $stmt->execute([$product_id, $user_id]);
                $cart_item = $stmt->fetch();

                if ($cart_item) {
                    // Tăng số lượng nếu sản phẩm đã có trong giỏ
                    $new_quantity = $cart_item['quantity'] + 1;
                    $stmt = $conn_store->prepare('UPDATE cart SET quantity = ? WHERE id = ?');
                    $stmt->execute([$new_quantity, $cart_item['id']]);
                    $message = "Đã tăng số lượng sản phẩm (ID: $product_id) trong giỏ hàng!";
                } else {
                    // Thêm sản phẩm mới vào giỏ hàng
                    $stmt = $conn_store->prepare('INSERT INTO cart (product_id, quantity, user_id) VALUES (?, 1, ?)');
                    $stmt->execute([$product_id, $user_id]);
                    $message = "Đã thêm sản phẩm (ID: $product_id) vào giỏ hàng!";
                }
            } else {
                $message = "Sản phẩm (ID: $product_id) không tồn tại!";
            }
        }
    }

    // Lấy danh sách sản phẩm trong giỏ hàng
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

<div class="cart-wrapper">
    <div class="container animate-in">
        <h1>Giỏ Hàng Của Bạn</h1>

        <?php if (!empty($message)): ?>
            <div class="message"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Giá</th>
                        <th>Số Lượng</th>
                        <th>Tổng</th>
                        <th>Hành Động</th>
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
                            <td><?php echo $item['quantity']; ?></td>
                            <td class="price"><?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td>
                                <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?');">Xóa</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cart_items)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">Giỏ hàng của bạn hiện đang trống</td>
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
                <a href="checkout.php" class="btn btn-checkout">Tiến Hành Thanh Toán</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
/* Tái sử dụng phong cách từ products.php */
.cart-wrapper {
    min-height: 100vh;
    padding: 40px 20px;
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.animate-in {
    opacity: 0;
    transform: translateY(20px);
    animation: fadeInUp 0.6s ease-in-out forwards;
}

@keyframes fadeInUp {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

h1 {
    color: #fff;
    margin-bottom: 30px;
    text-align: center;
    font-size: 2.5em;
    font-weight: 500;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
}

.message {
    background: #dff0d8;
    color: #3c763d;
    padding: 15px;
    margin-bottom: 20px;
    border-radius: 5px;
    text-align: center;
}

.table-wrapper {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 18px 20px;
    text-align: left;
    transition: all 0.3s ease;
}

th {
    background: linear-gradient(45deg, #9c5ffd, #1de0ff);
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

tbody tr:hover {
    background: rgba(156, 95, 253, 0.1);
    transform: scale(1.01);
}

.price {
    color: #ff4081;
    font-weight: 500;
}

.btn-delete {
    padding: 8px 18px;
    margin: 0 5px;
    border-radius: 25px;
    color: white;
    font-size: 0.9em;
    background: linear-gradient(45deg, #e74c3c, #c0392b);
    text-decoration: none;
}

.btn-delete:hover {
    background: linear-gradient(45deg, #c0392b, #e74c3c);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(231, 76, 60, 0.3);
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-style: italic;
}

.cart-total {
    margin-top: 30px;
    text-align: right;
}

.cart-total h3 {
    color: #fff;
    margin-bottom: 20px;
    font-size: 1.5em;
}

.btn-checkout {
    display: inline-flex;
    align-items: center;
    padding: 12px 25px;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    font-weight: 500;
    color: white;
    background: linear-gradient(45deg, #3498db, #2980b9);
}

.btn-checkout:hover {
    background: linear-gradient(45deg, #2980b9, #3498db);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
}

@media (max-width: 768px) {
    .cart-wrapper {
        padding: 20px 10px;
    }

    .container {
        margin: 0;
        padding: 10px;
    }

    table {
        display: block;
        overflow-x: auto;
    }

    th, td {
        min-width: 150px;
    }

    .cart-total {
        text-align: center;
    }
}
</style>