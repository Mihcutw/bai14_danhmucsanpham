<?php
include '../minh/config.php';

try {
    // Lấy dữ liệu từ bảng products
    $stmt = $conn_store->query('SELECT * FROM products');
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    echo "Lỗi truy vấn: " . $e->getMessage();
    exit;
}
?>

<?php include 'header.php'; ?>

<div class="products-wrapper">
    <div class="container animate-in">
        <h1>Danh Sách Sản Phẩm</h1>

        <div class="action-buttons">
            <a href="create.php" class="btn btn-add">+ Thêm Sản Phẩm</a>
            <a href="update.php" class="btn btn-update">Cập nhật Sản Phẩm</a>
        </div>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Ảnh</th>
                        <th>ID</th>
                        <th>Tên Sản Phẩm</th>
                        <th>Giá</th>
                        <th>Số lượng</th>
                        <th>Mô tả</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <?php if ($product['image'] && file_exists($product['image'])): ?>
                                    <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" style="max-width: 50px; max-height: 50px;">
                                <?php else: ?>
                                    <span>Không có ảnh</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td class="price"><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($product['description'] ?? 'Không có mô tả'); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Sửa</a>
                                <a href="delete.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-delete" 
                                   onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?');">Xóa</a>
                                <a href="cart.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-cart" 
                                   onclick="return confirm('Thêm sản phẩm có ID <?php echo $product['id']; ?> vào giỏ hàng?');">Giỏ hàng</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="7" class="empty-message">Hiện chưa có sản phẩm nào trong danh sách</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<style>
/* Reset mặc định */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif; /* Sử dụng font Roboto */
}

/* Wrapper cho toàn bộ trang */
.products-wrapper {
    min-height: 100vh;
    padding: 40px 20px;
}

/* Container */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Hiệu ứng animate-in */
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

/* Tiêu đề */
h1 {
    color: #fff; /* Màu trắng để nổi bật trên gradient */
    margin-bottom: 30px;
    text-align: center;
    font-size: 2.5em;
    font-weight: 500;
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
}

/* Action buttons */
.action-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 30px;
}

/* Nút chung */
.btn {
    display: inline-flex;
    align-items: center;
    padding: 12px 25px;
    text-decoration: none;
    border-radius: 50px;
    transition: all 0.3s ease;
    font-weight: 500;
    color: white;
}

/* Nút Thêm Sản Phẩm */
.btn-add {
    background: linear-gradient(45deg, #00c853, #00e676);
    box-shadow: 0 4px 15px rgba(0, 200, 83, 0.3);
}

.btn-add:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0, 200, 83, 0.4);
    background: linear-gradient(45deg, #00e676, #00c853);
}

/* Nút Cập nhật Sản Phẩm */
.btn-update {
    background: linear-gradient(45deg, #ff9800, #ffb300);
    box-shadow: 0 4px 15px rgba(255, 152, 0, 0.3);
}

.btn-update:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(255, 152, 0, 0.4);
    background: linear-gradient(45deg, #ffb300, #ff9800);
}

/* Nút Giỏ Hàng */
.btn-cart {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
    box-shadow: 0 4px 15px rgba(46, 204, 113, 0.3);
    padding: 8px 18px;
    margin: 0 5px;
    border-radius: 25px;
    color: white;
    font-size: 0.9em;
}

.btn-cart:hover {
    background: linear-gradient(45deg, #27ae60, #2ecc71);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(46, 204, 113, 0.3);
}

/* Table wrapper */
.table-wrapper {
    background: rgba(255, 255, 255, 0.9); /* Nền trắng trong suốt */
    backdrop-filter: blur(5px); /* Hiệu ứng mờ nền */
    border-radius: 15px;
    box-shadow: 0 5px 25px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}

/* Table */
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
    background: linear-gradient(45deg, #9c5ffd, #1de0ff); /* Gradient tím-xanh lam */
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
}

tbody tr {
    transition: all 0.3s ease;
}

tbody tr:hover {
    background: rgba(156, 95, 253, 0.1); /* Nền tím nhạt khi hover */
    transform: scale(1.01);
}

.price {
    color: #ff4081; /* Màu hồng để nổi bật */
    font-weight: 500;
}

/* Nút Sửa và Xóa */
.btn-edit, .btn-delete {
    padding: 8px 18px;
    margin: 0 5px;
    border-radius: 25px;
    color: white;
    font-size: 0.9em;
}

.btn-edit {
    background: linear-gradient(45deg, #3498db, #2980b9);
}

.btn-delete {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
}

.btn-edit:hover {
    background: linear-gradient(45deg, #2980b9, #3498db);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
}

.btn-delete:hover {
    background: linear-gradient(45deg, #c0392b, #e74c3c);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(231, 76, 60, 0.3);
}

/* Thông báo trống */
.empty-message {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-style: italic;
}

/* Responsive */
@media (max-width: 768px) {
    .products-wrapper {
        padding: 20px 10px;
    }

    .container {
        margin: 0;
        padding: 10px;
    }

    .action-buttons {
        flex-direction: column;
        gap: 10px;
    }

    .btn-add, .btn-update {
        width: 100%;
        text-align: center;
    }

    table {
        display: block;
        overflow-x: auto;
    }

    th, td {
        min-width: 150px;
    }
}
</style>