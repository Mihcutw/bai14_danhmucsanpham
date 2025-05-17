<?php
require_once 'config_user.php';

$page_title = "Đăng Ký";
include 'header.php';

$errors = [];
$success = "";
$username = '';
$email = '';

if (isset($_SESSION['user_id'])) {
    header("Location: index2.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST["username"] ?? ""));
    $email = htmlspecialchars(trim($_POST["email"] ?? ""));
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if (!$username) $errors["username"] = "Vui lòng nhập họ tên.";
    if (!$email) $errors["email"] = "Vui lòng nhập email.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors["email"] = "Email không hợp lệ.";
    if (!$password) $errors["password"] = "Vui lòng nhập mật khẩu.";
    elseif (strlen($password) < 6) $errors["password"] = "Mật khẩu phải có ít nhất 6 ký tự.";
    if ($password !== $confirm_password) $errors["confirm_password"] = "Mật khẩu xác nhận không khớp.";

    if (!$errors) {
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors["email"] = "Email đã được sử dụng.";
        }
    }

    if (!$errors) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if ($stmt->execute([$username, $email, $hashed_password])) {
            $success = "Đăng ký thành công! Chào mừng, $username.";
            $_POST = [];
            header("Location: login.php");
            exit();
        } else {
            $errors["database"] = "Đã có lỗi xảy ra. Vui lòng thử lại.";
        }
    }
}
?>

<div class="login-wrapper">
    <div class="login-container">
        <h2>Đăng Ký</h2>
        <?php if (!empty($errors)) : ?>
            <div class="error-messages">
                <?php foreach ($errors as $error) : ?>
                    <p class="error"><?php echo $error; ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)) : ?>
            <div class="success-message">
                <p class="success"><?php echo $success; ?></p>
            </div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="form-group">
                <label for="username">Tên người dùng</label>
                <input type="text" id="username" name="username" placeholder="Họ và Tên" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="Email" value="<?php echo htmlspecialchars($email); ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Mật khẩu</label>
                <input type="password" id="password" name="password" placeholder="Nhập mật khẩu" required>
            </div>
            <div class="form-group">
                <label for="confirm-password">Xác nhận mật khẩu</label>
                <input type="password" id="confirm-password" name="confirm_password" placeholder="Nhập lại mật khẩu" required>
            </div>
            <button type="submit">Đăng Ký</button>
        </form>
        <div class="links">
            <a href="login.php">Đăng nhập</a>
            <a href="reset-password.php">Reset Mật Khẩu</a>
        </div>
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
    .login-wrapper {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
        padding-bottom: 60px;
    }
    .login-container {
        background-color: #fff;
        padding: 2rem;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(147, 112, 219, 0.1);
        border: 2px solid transparent;
        background: linear-gradient(#fff, #fff) padding-box, linear-gradient(90deg, #00eaff, #ff007a) border-box;
        width: 100%;
        max-width: 400px;
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
    .form-group { margin-bottom: 1rem; }
    label { display: block; color: #4682b4; margin-bottom: 0.5rem; }
    input { width: 100%; padding: 0.8rem; border: 1px solid #b0c4de; border-radius: 5px; box-sizing: border-box; }
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
    .links { text-align: center; margin-top: 1rem; }
    .links a { color: #9370db; text-decoration: none; margin: 0 10px; }
    .links a:hover { color: #4682b4; text-decoration: underline; }
    .error-messages { color: red; font-weight: bold; text-align: center; margin-bottom: 1rem; }
    .error { background-color: #ffdddd; padding: 10px; border-radius: 5px; }
    .success-message { color: green; font-weight: bold; text-align: center; margin-bottom: 1rem; }
    .success { background-color: #ddffdd; padding: 10px; border-radius: 5px; }
</style>

<?php include 'footer.php'; ?>