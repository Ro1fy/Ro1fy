<?php
session_start();
require_once __DIR__ . '/../auth/db_config.php'; // Проверьте путь

header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $input = json_decode(file_get_contents('php://input'), true);
    $productId = isset($input['product_id']) ? (int)$input['product_id'] : 0;

    if ($productId <= 0) {
        throw new Exception('Неверный ID товара');
    }

    // Проверяем существование товара
    $stmt = $db->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    if (!$stmt->fetch()) {
        throw new Exception("Товар не найден");
    }

    $userId = isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null;
    $sessionId = session_id();

    if ($userId) {
        // Для авторизованного пользователя
        $stmt = $db->prepare("
            SELECT id FROM cart 
            WHERE user_id = ?
            ORDER BY created_at DESC LIMIT 1
        ");
        $stmt->execute([$userId]);
        $cart = $stmt->fetch();

        if ($cart) {
            $cartId = $cart['id'];
        } else {
            $stmt = $db->prepare("INSERT INTO cart (user_id, session_id) VALUES (?, ?)");
            $stmt->execute([$userId, $sessionId]);
            $cartId = $db->lastInsertId();
        }
    } else {
        // Для гостя
        if (!isset($_SESSION['cart_id'])) {
            $stmt = $db->prepare("INSERT INTO cart (session_id) VALUES (?)");
            $stmt->execute([$sessionId]);
            $_SESSION['cart_id'] = $db->lastInsertId();
        }
        $cartId = $_SESSION['cart_id'];
    }

    // Проверяем, существует ли корзина
    $stmt = $db->prepare("SELECT id FROM cart WHERE id = ?");
    $stmt->execute([$cartId]);
    if (!$stmt->fetch()) {
        throw new Exception("Корзина с ID $cartId не найдена");
    }

    // Добавляем товар
    $stmt = $db->prepare("
        INSERT INTO cart_items (cart_id, product_id, quantity)
        VALUES (?, ?, 1)
        ON DUPLICATE KEY UPDATE quantity = quantity + 1
    ");
    $stmt->execute([$cartId, $productId]);

    // Сохраняем cart_id в сессию
    $_SESSION['cart_id'] = $cartId;

    echo json_encode([
        'success' => true,
        'message' => 'Товар успешно добавлен',
        'cart_id' => $cartId
    ]);

} catch (PDOException $e) {
    error_log('DB Error: ' . $e->getMessage());
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