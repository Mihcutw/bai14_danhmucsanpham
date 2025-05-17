<?php
include 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = htmlspecialchars(trim($_POST['product-name'] ?? ''));
    $price = filter_var($_POST['price'] ?? 0, FILTER_VALIDATE_FLOAT);
    $quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT);
    $description = htmlspecialchars(trim($_POST['description'] ?? ''));
    $image = '';

    // Validate dữ liệu
    if (empty($name)) {
        $error = "Tên sản phẩm không được để trống.";
    } elseif ($price === false || $price < 0) {
        $error = "Giá phải là số dương hợp lệ.";
    } elseif ($quantity === false || $quantity < 0) {
        $error = "Số lượng phải là số nguyên dương hợp lệ.";
    } else {
        // Xử lý upload hình ảnh
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = mime_content_type($_FILES['image']['tmp_name']);
            
            if (!in_array($file_type, $allowed_types)) {
                $error = "Chỉ chấp nhận file JPG, PNG hoặc GIF.";
            } else {
                $uploadDir = 'uploads/';
                $imageName = time() . '_' . basename($_FILES['image']['name']);
                $imagePath = $uploadDir . $imageName;

                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
                    $error = "Lỗi khi tải lên hình ảnh.";
                } else {
                    $image = $imagePath;
                }
            }
        }

        // Tiếp tục nếu không có lỗi upload
        if (!$error) {
            try {
                // Lấy tất cả các id hiện có trong bảng
                $stmt = $conn_store->query('SELECT id FROM products');
                $existing_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Tìm id nhỏ nhất chưa sử dụng trong khoảng 1 đến 10
                $new_id = null;
                for ($i = 1; $i <= 10; $i++) {
                    if (!in_array($i, $existing_ids)) {
                        $new_id = $i;
                        break;
                    }
                }

                // Nếu không tìm thấy id khả dụng
                if ($new_id === null) {
                    $error = "Không thể thêm sản phẩm: Đã đạt giới hạn 10 sản phẩm (id từ 1 đến 10).";
                } else {
                    // Thêm sản phẩm với id được chọn
                    $stmt = $conn_store->prepare('INSERT INTO products (id, name, price, quantity, description, image) VALUES (?, ?, ?, ?, ?, ?)');
                    $stmt->execute([$new_id, $name, $price, $quantity, $description, $image]);
                    header('Location: products.php');
                    exit;
                }
            } catch (PDOException $e) {
                $error = "Lỗi khi thêm sản phẩm: " . $e->getMessage();
            }
        }
    }
}
?>

<?php include 'header.php'; ?>

<div class="form-wrapper">
    <div class="form-container">
        <h2>Thêm Sản Phẩm</h2>
        <?php if ($error): ?>
            <div class="error-messages">
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            </div>
        <?php endif; ?>
        <form id="add-product-form" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="product-name">Tên sản phẩm</label>
                <input type="text" id="product-name" name="product-name" placeholder="Nhập tên sản phẩm" required>
            </div>
            <div class="form-group">
                <label for="price">Giá (VND)</label>
                <input type="number" id="price" name="price" placeholder="Nhập giá (VNĐ)" step="0.01" min="0" required>
            </div>
            <div class="form-group">
                <label for="quantity">Số lượng</label>
                <input type="number" id="quantity" name="quantity" placeholder="Nhập số lượng" min="0" required>
            </div>
            <div class="form-group">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description" placeholder="Nhập mô tả sản phẩm"></textarea>
            </div>
            <div class="form-group">
                <label for="image">Hình ảnh</label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/gif">
            </div>
            <button type="submit" class="btn-add-product">
                <span class="btn-icon">+</span> Thêm Sản Phẩm
            </button>
            <a href="products.php" class="back-link">Quay lại danh sách</a>
        </form>
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
input::placeholder, textarea::placeholder {
    color: #b0b0b0;
    font-style: italic;
}
.btn-add-product {
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
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.btn-add-product .btn-icon {
    font-size: 1.2rem;
    font-weight: bold;
}
.btn-add-product:hover {
    background: linear-gradient(90deg, #00e676, #00c853);
    box-shadow: 0 2px 10px rgba(0, 200, 83, 0.5);
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
</style>

<?php include 'footer.php'; ?>