<?php
session_start();
require_once 'auth/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}

$user = $_SESSION['user'];
$isAdmin = $user['is_admin'] ?? false;

// Получаем историю заказов
try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        SELECT o.id AS order_id, o.order_date, SUM(oi.quantity * oi.price) AS total
        FROM orders o
        LEFT JOIN order_items oi ON o.id = oi.order_id
        WHERE o.user_id = ?
        GROUP BY o.id
        ORDER BY o.order_date DESC
    ");
    $stmt->execute([(int)$user['id']]);
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Ошибка подключения к базе данных');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Личный кабинет</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .personal-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .user-info h1 {
            color: #03571A;
            margin-bottom: 20px;
        }
        .user-details {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        .user-detail-label {
            font-weight: bold;
            color: #03571A;
            font-family: roboto_medium;
        }
        .order-history {
            margin-top: 40px;
            margin-bottom: 32px;
        }
        .order-item {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .order-header {
            font-family: roboto_bold;
            color: #03571A;
        }
        .order-date {
            font-family: roboto_regular;
            color: #555;
            font-size: 14px;
        }
        .order-products {
            margin-top: 10px;
        }
        .product-line {
            font-family: roboto_regular;
            color: #000;
        }

        .auth-btn {
            text-decoration: none;
        }
    </style>
</head>
<body>

<?php require_once 'templates/header.php'; ?>

<div class="personal-container">
    <div class="user-info">
        <h1>Личный кабинет</h1>
        <div class="user-details">
            <span class="user-detail-label">Имя:</span>
            <span><?= htmlspecialchars($user['name']) ?></span>

            <span class="user-detail-label">Email:</span>
            <span><?= htmlspecialchars($user['email']) ?></span>

            <?php if (!empty($user['address'])): ?>
                <span class="user-detail-label">Адрес:</span>
                <span><?= htmlspecialchars($user['address'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>

            <?php if (!empty($user['phone'])): ?>
                <span class="user-detail-label">Телефон:</span>
                <span><?= htmlspecialchars($user['phone'], ENT_QUOTES, 'UTF-8') ?></span>
            <?php endif; ?>
        </div>

        

        <?php if ($isAdmin): ?>
            <a href="/admin.php" class="admin-link">Перейти в админ-панель</a>
        <?php endif; ?>
    </div>

    <!-- История заказов -->
    <div class="order-history">
        <h2 style="font-family: roboto_bold; color: #03571A;">История заказов</h2>
        <?php if (!empty($orders)): ?>
            <?php foreach ($orders as $order): ?>
                <div class="order-item">
                    <div class="order-header">Заказ №<?= $order['order_id'] ?></div>
                    <div class="order-date"><?= $order['order_date'] ?></div>
                    <div class="order-products">
                        <?php
                        $stmt = $db->prepare("
                            SELECT p.name, oi.quantity, oi.price 
                            FROM order_items oi
                            JOIN products p ON oi.product_id = p.id
                            WHERE oi.order_id = ?
                        ");
                        $stmt->execute([$order['order_id']]);
                        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($products as $product):
                        ?>
                            <div class="product-line">
                                <?= htmlspecialchars($product['name']) ?> — <?= $product['quantity'] ?> шт. по <?= number_format($product['price'], 2, '.', ' ') ?> ₽
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div style="font-family: roboto_bold; margin-top: 10px;">
                        Итого: <?= number_format($order['total'] ?: 0, 2, '.', ' ') ?> ₽
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #aaa; font-family: roboto_regular;">Пока нет заказов</p>
        <?php endif; ?>
    </div>

    <a href="auth/logout.php" class="auth-btn">Выйти</a>
</div>

<?php require_once 'templates/footer.php'; ?>
</body>
</html>