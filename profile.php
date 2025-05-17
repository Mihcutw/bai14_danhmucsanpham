<?php
// Bắt đầu session
session_start();

// Kết nối cơ sở dữ liệu
require_once 'config_user.php';

// Kiểm tra nếu không có session user_id thì chuyển hướng về login.php
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Lấy thông tin người dùng từ database
try {
    $stmt = $conn->prepare("SELECT username, email, avatar, password, created_at FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if ($user) {
        $username = $user['username'];
        $email = $user['email'] ? $user['email'] : 'Chưa cập nhật email';
        // Nếu avatar không tồn tại hoặc rỗng, sử dụng avatar mặc định
        $avatar = !empty($user['avatar']) ? $user['avatar'] : 'https://i.pinimg.com/originals/b2/ea/a0/b2eaa0d4918d54021f9c7aa3fc3d3cf3.jpg';
        $hashed_password = $user['password'];
        $created_at = $user['created_at'] ? date('d/m/Y H:i:s', strtotime($user['created_at'])) : 'Chưa có thông tin';
    } else {
        // Nếu không tìm thấy user, hủy session và chuyển hướng
        session_destroy();
        header("Location: login.php");
        exit();
    }
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_info'])) {
    try {
        // Lấy dữ liệu từ form
        $new_username = $_POST['username'] ?? $username;
        $new_email = $_POST['email'] ?? $email;

        // Cập nhật thông tin vào database
        $stmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
        $stmt->execute([$new_username, $new_email, $_SESSION['user_id']]);

        // Cập nhật lại biến để hiển thị
        $username = $new_username;
        $email = $new_email;

        $success_info = "Cập nhật thông tin thành công!";
    } catch (PDOException $e) {
        $error_info = "Lỗi khi cập nhật: " . $e->getMessage();
    }
}

// Xử lý đổi mật khẩu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    try {
        $old_password = $_POST['old_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Kiểm tra mật khẩu cũ
        if (!password_verify($old_password, $hashed_password)) {
            $error_password = "Mật khẩu cũ không đúng.";
        } elseif ($new_password !== $confirm_password) {
            $error_password = "Mật khẩu mới và xác nhận mật khẩu không khớp.";
        } elseif (strlen($new_password) < 6) {
            $error_password = "Mật khẩu mới phải có ít nhất 6 ký tự.";
        } else {
            // Mã hóa mật khẩu mới
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Cập nhật mật khẩu mới vào database
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$new_hashed_password, $_SESSION['user_id']]);

            $success_password = "Đổi mật khẩu thành công!";
            $hashed_password = $new_hashed_password;
        }
    } catch (PDOException $e) {
        $error_password = "Lỗi khi đổi mật khẩu: " . $e->getMessage();
    }
}

// Xử lý đổi avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_avatar'])) {
    try {
        // Xử lý tải lên avatar
        $new_avatar = $avatar;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_name = $_FILES['avatar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            // Kiểm tra định dạng file
            if (in_array($file_ext, $allowed_ext)) {
                // Đặt tên file mới để tránh trùng
                $new_file_name = 'avatar_' . $_SESSION['user_id'] . '_' . time() . '.' . $file_ext;
                $upload_path = 'uploads/' . $new_file_name;

                // Tạo thư mục uploads nếu chưa có
                if (!is_dir('uploads')) {
                    mkdir('uploads', 0755, true);
                }

                // Di chuyển file vào thư mục uploads
                if (move_uploaded_file($file_tmp, $upload_path)) {
                    $new_avatar = $upload_path;
                } else {
                    $error_avatar = "Lỗi khi tải lên hình ảnh.";
                }
            } else {
                $error_avatar = "Chỉ hỗ trợ định dạng JPG, JPEG, PNG, GIF.";
            }
        }

        // Cập nhật avatar vào database
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE id = ?");
        $stmt->execute([$new_avatar, $_SESSION['user_id']]);

        // Cập nhật lại biến để hiển thị
        $avatar = $new_avatar;

        $success_avatar = "Cập nhật hình đại diện thành công!";
    } catch (PDOException $e) {
        $error_avatar = "Lỗi khi cập nhật hình đại diện: " . $e->getMessage();
    }
}
?>

<?php include 'header.php'; ?>

<style>
    .profile-form input[type="text"],
    .profile-form input[type="email"],
    .profile-form input[type="password"],
    .profile-form input[type="file"] {
        width: 100%;
        padding: 10px;
        margin: 5px 0 10px;
        border-radius: 8px;
        border: 1px solid #ccc;
        font-size: 14px;
        transition: border-color 0.3s ease;
    }
    .profile-form input:focus {
        border-color: #ff007a;
        outline: none;
    }
    .profile-form label {
        font-size: 14px;
        color: #333;
        font-weight: 500;
    }
    .profile-form .cta-button {
        width: 100%;
        margin-top: 10px;
        padding: 12px;
        font-size: 16px;
    }
    .user-welcome {
        background: rgba(255, 255, 255, 0.95);
        padding: 30px;
        border-radius: 15px;
        margin: 0 auto 30px;
        max-width: 500px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        text-align: center;
    }
    .user-info {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .user-avatar img {
        width: 120px;
        height: 120px;
        border: 4px solid #00eaff;
        border-radius: 50%;
        margin-bottom: 20px;
    }
    .user-details {
        width: 100%;
    }
    .user-details h2 {
        font-size: 28px;
        margin-bottom: 15px;
    }
    .user-details p {
        font-size: 16px;
        color: #666;
        margin: 10px 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 5px 0;
        border-bottom: 1px solid rgba(0, 0, 0, 0.1);
    }
    .user-details p span.label {
        font-weight: 500;
        color: #333;
    }
    .avatar-current img,
    .avatar-preview img {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        border: 2px solid #00eaff;
        margin-top: 10px;
    }
    .avatar-preview {
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .avatar-preview img {
        display: none;
    }
    .avatar-label {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        margin-top: 10px;
    }
</style>

<div class="main-content">
    <!-- Tiêu đề chính -->
    <h1>Hồ sơ của bạn</h1>
    <p>Quản lý thông tin cá nhân và bảo mật tài khoản</p>

    <!-- Phần hiển thị thông tin người dùng -->
    <div class="user-welcome">
        <div class="user-info">
            <div class="user-avatar">
                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar">
            </div>
            <div class="user-details">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p><span class="label">Email:</span> <?php echo htmlspecialchars($email); ?></p>
                <p><span class="label">Ngày gia nhập:</span> <?php echo htmlspecialchars($created_at); ?></p>
            </div>
        </div>
    </div>

    <!-- Form cập nhật thông tin, đổi mật khẩu, và đổi avatar -->
    <div class="suggestions">
        <h3>Cập nhật thông tin cá nhân</h3>
        <div class="suggestion-cards">
            <!-- Form cập nhật thông tin -->
            <div class="card">
                <?php if (isset($success_info)): ?>
                    <p style="color: green; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($success_info); ?></p>
                <?php endif; ?>
                <?php if (isset($error_info)): ?>
                    <p style="color: red; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($error_info); ?></p>
                <?php endif; ?>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="update_info" value="1">
                    <div>
                        <label for="username">Tên người dùng:</label>
                        <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>" required>
                    </div>
                    <div>
                        <label for="email">Email:</label>
                        <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email === 'Chưa cập nhật email' ? '' : $email); ?>">
                    </div>
                    <button type="submit" class="cta-button dashboard-btn">Cập nhật thông tin</button>
                </form>
            </div>

            <!-- Form đổi mật khẩu -->
            <div class="card">
                <?php if (isset($success_password)): ?>
                    <p style="color: green; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($success_password); ?></p>
                <?php endif; ?>
                <?php if (isset($error_password)): ?>
                    <p style="color: red; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($error_password); ?></p>
                <?php endif; ?>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="change_password" value="1">
                    <div>
                        <label for="old_password">Mật khẩu cũ:</label>
                        <input type="password" name="old_password" id="old_password" required>
                    </div>
                    <div>
                        <label for="new_password">Mật khẩu mới:</label>
                        <input type="password" name="new_password" id="new_password" required>
                    </div>
                    <div>
                        <label for="confirm_password">Xác nhận mật khẩu mới:</label>
                        <input type="password" name="confirm_password" id="confirm_password" required>
                    </div>
                    <button type="submit" class="cta-button dashboard-btn">Đổi mật khẩu</button>
                </form>
            </div>

            <!-- Form đổi avatar -->
            <div class="card">
                <?php if (isset($success_avatar)): ?>
                    <p style="color: green; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($success_avatar); ?></p>
                <?php endif; ?>
                <?php if (isset($error_avatar)): ?>
                    <p style="color: red; font-size: 14px; margin-bottom: 10px;"><?php echo htmlspecialchars($error_avatar); ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data" class="profile-form">
                    <input type="hidden" name="update_avatar" value="1">
                    <div>
                        <label class="avatar-label">Avatar hiện tại:</label>
                        <div class="avatar-current">
                            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Current Avatar">
                        </div>
                    </div>
                    <div>
                        <label for="avatar">Chọn avatar mới:</label>
                        <input type="file" name="avatar" id="avatar" accept="image/*">
                    </div>
                    <div>
                        <label class="avatar-label">Avatar mới:</label>
                        <div class="avatar-preview">
                            <img id="avatar-preview-img" src="#" alt="Preview">
                        </div>
                    </div>
                    <button type="submit" class="cta-button dashboard-btn">Cập nhật hình đại diện</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    // JavaScript để hiển thị preview avatar
    document.getElementById('avatar').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const previewImg = document.getElementById('avatar-preview-img');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                previewImg.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            previewImg.style.display = 'none';
        }
    });
</script>

<?php include 'footer.php'; ?>
</body>
</html>