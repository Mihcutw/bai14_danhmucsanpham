<?php
include 'config.php';

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

<div class="update-product-wrapper">
    <div class="update-product-container">
        <h2>Cập Nhật Sản Phẩm</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
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
                            <td><?php echo $product['id']; ?></td>
                            <td><?php echo htmlspecialchars($product['name']); ?></td>
                            <td><?php echo number_format($product['price'], 0, ',', '.') . ' VNĐ'; ?></td>
                            <td><?php echo $product['quantity']; ?></td>
                            <td><?php echo htmlspecialchars($product['description'] ?? 'Không có mô tả'); ?></td>
                            <td>
                                <a href="edit.php?id=<?php echo $product['id']; ?>" class="btn btn-edit">Chọn</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($products)): ?>
                        <tr>
                            <td colspan="6" class="empty-message">Hiện chưa có sản phẩm nào để cập nhật</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="back-link">
            <a href="products.php">Quay lại danh sách</a>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}

.update-product-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    padding: 20px;
}

.update-product-container {
    background: rgba(255, 255, 255, 0.9);
    backdrop-filter: blur(5px);
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    border: 1px solid rgba(147, 112, 219, 0.3);
    width: 100%;
    max-width: 1200px;
    animation: fadeInDown 0.6s ease-in-out;
}

@keyframes fadeInDown {
    0% {
        opacity: 0;
        transform: translateY(-20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

h2 {
    color: #9c5ffd;
    text-align: center;
    margin-bottom: 2rem;
    font-size: 1.8rem;
    font-weight: 500;
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

tbody tr {
    transition: all 0.3s ease;
}

tbody tr:hover {
    background: rgba(156, 95, 253, 0.1);
    transform: scale(1.01);
}

.btn-edit {
    padding: 8px 18px;
    margin: 0 5px;
    border-radius: 25px;
    color: white;
    font-size: 0.9em;
    background: linear-gradient(45deg, #3498db, #2980b9);
    text-decoration: none;
}

.btn-edit:hover {
    background: linear-gradient(45deg, #2980b9, #3498db);
    transform: translateY(-2px);
    box-shadow: 0 3px 10px rgba(52, 152, 219, 0.3);
}

.empty-message {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
    font-style: italic;
}

.back-link {
    text-align: center;
    margin-top: 1.5rem;
}

.back-link a {
    color: #9c5ffd;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.back-link a:hover {
    color: #1de0ff;
    text-decoration: underline;
}

@media (max-width: 768px) {
    .update-product-container {
        padding: 1.5rem;
        max-width: 100%;
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