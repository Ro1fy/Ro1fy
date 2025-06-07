<?php
session_start();
// подключение бд
require_once 'auth/db_config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $name = trim(strip_tags($input['name'] ?? ''));
    $text = trim(strip_tags($input['text'] ?? ''));

    if (empty($name) || empty($text)) {
        throw new Exception('Заполните все поля');
    }

    // Сохраняем отзыв в БД
    $stmt = $db->prepare("INSERT INTO reviews (name, text) VALUES (?, ?)");
    $stmt->execute([$name, $text]);

    echo json_encode([
        'success' => true,
        'message' => 'Отзыв успешно добавлен',
        'review' => [
            'name' => $name,
            'text' => $text,
            'created_at' => date('Y-m-d H:i:s')
        ]
    ]);

} catch (PDOException $e) {
    error_log("DB Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Ошибка базы данных'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>