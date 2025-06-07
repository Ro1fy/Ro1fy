<?php
session_start();
error_log("Starting checkout process");
require_once '../auth/db_config.php';

header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    error_log("Database connection established");

    // Получаем данные из POST
    $input = json_decode(file_get_contents('php://input'), true);
    $cartId = isset($input['cart_id']) ? (int)$input['cart_id'] : null;

    if (!$cartId || !isset($_SESSION['user']['id'])) {
        throw new Exception('Неверная корзина или пользователь не авторизован');
    }

    $userId = (int)$_SESSION['user']['id'];

    // Начинаем транзакцию
    $db->beginTransaction();

    // Создаём заказ
    $stmt = $db->prepare("INSERT INTO orders (user_id, cart_id) VALUES (?, ?)");
    $stmt->execute([$userId, $cartId]);
    $orderId = $db->lastInsertId();

    // Переносим товары в историю заказов
    $stmt = $db->prepare("
        INSERT INTO order_items (order_id, product_id, quantity, price)
        SELECT ?, product_id, quantity, (SELECT price FROM products WHERE id = product_id)
        FROM cart_items WHERE cart_id = ?
    ");
    $stmt->execute([$orderId, $cartId]);

    // Очищаем корзину
    $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    // Завершаем транзакцию
    $db->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Заказ успешно оформлен',
        'order_id' => $orderId
    ]);

} catch (Exception $e) {
    error_log("Error in checkout: " . $e->getMessage());
    $db->rollBack();
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>