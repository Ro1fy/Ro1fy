<?php
session_start();
require_once 'auth/db_config.php';

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['user'])) {
        throw new Exception('Пользователь не авторизован');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    $cartId = isset($input['cart_id']) ? (int)$input['cart_id'] : null;
    $address = trim(strip_tags($input['address'] ?? ''));
    $phone = trim(strip_tags($input['phone'] ?? ''));

    if (!$cartId || !$address) {
        throw new Exception('Не все поля заполнены');
    }

    $userId = $_SESSION['user']['id'];

    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Обновляем адрес пользователя
    $stmt = $db->prepare("UPDATE users SET address = ?, phone = ? WHERE id = ?");
    $stmt->execute([$address, $phone, $userId]);

    // Получаем товары из корзины
    $stmt = $db->prepare("SELECT product_id, quantity FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll();

    if (!$items) {
        throw new Exception('Корзина пуста');
    }

    // Создаём заказ
    $totalPrice = 0;
    foreach ($items as $item) {
        $stmt = $db->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->execute([$item['product_id']]);
        $price = $stmt->fetchColumn();
        $totalPrice += $price * $item['quantity'];
    }

    $stmt = $db->prepare("INSERT INTO orders (user_id, cart_id, address, phone, total_price) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $cartId, $address, $phone, $totalPrice]);

    // Очищаем корзину
    $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ?");
    $stmt->execute([$cartId]);

    echo json_encode([
        'success' => true,
        'message' => 'Заказ оформлен'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>