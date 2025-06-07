<?php
header('Content-Type: application/json');

// Подключение к базе данных
$host = 'localhost';
$dbname = 'kursach';
$username = 'root';
$password = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit;
}

// Получаем данные из формы
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';

// Валидация данных
if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
    echo json_encode(['success' => false, 'message' => 'Все поля обязательны для заполнения']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Пароль должен содержать минимум 6 символов']);
    exit;
}

if ($password !== $confirmPassword) {
    echo json_encode(['success' => false, 'message' => 'Пароли не совпадают']);
    exit;
}

// Проверяем, существует ли пользователь с таким email
try {
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email уже существует']);
        exit;
    }
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при проверке пользователя']);
    exit;
}

// Хешируем пароль
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

// Добавляем пользователя в базу данных
try {
    $stmt = $db->prepare("INSERT INTO users (name, email, password, is_admin) VALUES (:name, :email, :password, 0)");
    $stmt->execute([
        ':name' => $name,
        ':email' => $email,
        ':password' => $hashedPassword
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Регистрация успешна! Теперь вы можете войти.']);
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при регистрации: ' . $e->getMessage()]);
}