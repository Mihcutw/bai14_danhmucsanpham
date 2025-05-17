<?php
require_once 'config.php';

// Chỉ gọi session_start() nếu chưa có session active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Khởi tạo biến
$errors = [];
$success = "";
$product = null;

// Lấy ID sản phẩm từ URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Lấy thông tin sản phẩm từ database
if ($product_id > 0) {
    try {
        $stmt = $conn_store->prepare("SELECT name, price, quantity, description, image FROM products WHERE id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        if (!$product) {
            $errors[] = "Sản phẩm không tồn tại.";
        }
    } catch (PDOException $e) {
        $errors[] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
    }
} else {
    $errors[] = "ID sản phẩm không hợp lệ.";
}

// Xử lý form chỉnh sửa
if ($_SERVER["REQUEST_METHOD"] == "POST" && empty($errors)) {
    $name = htmlspecialchars(trim($_POST["name"] ?? ""));
    $price = filter_var($_POST["price"] ?? 0, FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($_POST["quantity"] ?? 0, FILTER_VALIDATE_INT);
    $description = htmlspecialchars(trim($_POST["description"] ?? ""));
    $image = $product['image']; // Giữ hình ảnh cũ nếu không upload mới

    // Xử lý upload hình ảnh mới
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $imagePath = $uploadDir . $imageName;

        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imagePath;
            // Xóa hình ảnh cũ nếu tồn tại (tùy chọn)
            if ($product['image'] && file_exists($product['image'])) {
                unlink($product['image']);
            }
        } else {
            $errors[] = "Lỗi khi tải lên hình ảnh.";
        }
    }

    // Validate dữ liệu
    if (!$name) $errors["name"] = "Vui lòng nhập tên sản phẩm.";
    if ($price === false || $price < 0) $errors["price"] = "Giá phải là số không âm.";
    if ($quantity === false || $quantity < 0) $errors["quantity"] = "Số lượng phải là số không âm.";
    
    if (empty($errors)) {
        try {
            $stmt = $conn_store->prepare("UPDATE products SET name = ?, price = ?, quantity = ?, description = ?, image = ? WHERE id = ?");
            if ($stmt->execute([$name, $price, $quantity, $description, $image, $product_id])) {
                $success = "Cập nhật sản phẩm thành công!";
                // Cập nhật lại dữ liệu hiển thị
                $product = ['name' => $name, 'price' => $price, 'quantity' => $quantity, 'description' => $description, 'image' => $image];
            } else {
                $errors[] = "Không thể cập nhật sản phẩm.";
            }
        } catch (PDOException $e) {
            $errors[] = "Lỗi cơ sở dữ liệu: " . $e->getMessage();
        }
    }
}

$page_title = "Chỉnh Sửa Sản Phẩm";
include 'header.php';
?>

<div class="form-wrapper">
    <div class="form-container">
        <h2>Chỉnh Sửa Sản Phẩm</h2>
        <?php if (!empty($errors)) : ?>
            <div class="error-messages">
                <?php foreach ($errors as $error) : ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="success-message">
                <p class="success"><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        <?php if ($product) : ?>
            <form method="POST" action="edit.php?id=<?php echo $product_id; ?>" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Tên sản phẩm</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="price">Giá (VND)</label>
                    <input type="number" id="price" name="price" step="0.01" min="0" value="<?php echo number_format($product['price'], 2, '.', ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="quantity">Số lượng</label>
                    <input type="number" id="quantity" name="quantity" min="0" value="<?php echo htmlspecialchars($product['quantity']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="image">Hình ảnh hiện tại</label>
                    <?php if ($product['image']): ?>
                        <div style="margin-bottom: 10px;">
                            <img src="<?php echo htmlspecialchars($product['image']); ?>" alt="Hình ảnh hiện tại" style="max-width: 100px; max-height: 100px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="image" name="image" accept="image/*">
                    <p style="font-size: 0.9rem; color: #666;">(Để trống nếu không muốn thay đổi hình ảnh)</p>
                </div>
                <button type="submit">Cập Nhật</button>
                <a href="products.php" class="back-link">Quay lại danh sách</a>
            </form>
        <?php else : ?>
            <p>Sản phẩm không tồn tại. <a href="products.php">Quay lại danh sách</a></p>
        <?php endif; ?>
    </div>
</div>

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
    .form-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-bottom: 60px;
    }
    .form-container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(147, 112, 219, 0.1);
        border: 2px solid transparent;
        background: linear-gradient(#fff, #fff) padding-box, linear-gradient(90deg, #00eaff, #ff007a) border-box;
        width: 100%;
        max-width: 500px;
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
    .form-group {
        margin-bottom: 1rem;
    }
    label {
        display: block;
        color: #4682b4;
        margin-bottom: 0.5rem;
    }
    input, textarea {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #b0c4de;
        border-radius: 5px;
        box-sizing: border-box;
    }
    input[type="file"] {
        padding: 0.5rem;
    }
    textarea {
        height: 100px;
        resize: vertical;
    }
    button {
        width: 100%;
        padding: 0.8rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: all 0.3s ease;
        background: linear-gradient(90deg, #9370db, #4682b4);
        color: white;
        font-weight: bold;
        margin-top: 1rem;
    }
    button:hover {
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
    .error-messages {
        color: red;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1rem;
    }
    .error {
        background-color: #ffdddd;
        padding: 10px;
        border-radius: 5px;
    }
    .success-message {
        color: green;
        font-weight: bold;
        text-align: center;
        margin-bottom: 1rem;
    }
    .success {
        background-color: #ddffdd;
        padding: 10px;
        border-radius: 5px;
    }
</style>

<?php include 'footer.php'; ?>