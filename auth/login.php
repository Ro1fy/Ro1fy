<!--авторизация -->
<?php
session_start();
require_once '../auth/db_config.php';

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'Заполните все поля']);
    exit;
}

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        unset($user['password']); // Не храним пароль в сессии
        $_SESSION['user'] = $user;

        echo json_encode([
            'success' => true,
            'redirect' => '/?login=success'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Неверный логин или пароль'
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>