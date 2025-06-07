<?php
session_start();
require_once '../auth/db_config.php';

header('Content-Type: application/json');

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $productId = (int)$_POST['product_id'];
    
    // Находим корзину
    $cartId = null;
    
    if (isset($_SESSION['user'])) {
        $stmt = $db->prepare("
            SELECT id 
            FROM cart 
            WHERE user_id = ? AND NOT EXISTS (
                SELECT 1 FROM cart_items WHERE cart_id = cart.id
            )
            ORDER BY created_at DESC
            LIMIT 1
        ");
        $stmt->execute([$_SESSION['user']['id']]);
        $cart = $stmt->fetch();
        $cartId = $cart ? $cart['id'] : null;
    } elseif (isset($_SESSION['cart_id'])) {
        $cartId = $_SESSION['cart_id'];
    }
    
    if (!$cartId) {
        throw new Exception('Корзина не найдена');
    }
    
    // Удаляем товар
    $stmt = $db->prepare("DELETE FROM cart_items WHERE cart_id = ? AND product_id = ?");
    $stmt->execute([$cartId, $productId]);
    
    echo json_encode(['success' => true]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка базы данных']);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}