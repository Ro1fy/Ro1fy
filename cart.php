<?php
session_start();
require_once 'auth/db_config.php';


// if (!isset($_SESSION['cart_id'])) {
//     die('<p>Корзина не найдена</p>');
// }

$cartId = $_SESSION['cart_id'];

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        SELECT ci.quantity, p.name, p.price, p.image 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Ошибка БД: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Корзина</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .cart-container {
            max-width: 1120px;
            margin: 40px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .cart-title {
            font-family: roboto_bold;
            font-size: 36px;
            color: #03571A;
            text-align: center;
            margin-bottom: 30px;
        }

        .cart-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 0;
            border-bottom: 1px solid #ddd;
        }

        .item-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .item-name {
            font-family: roboto_bold;
            font-size: 18px;
            color: #171312;
        }

        .item-price {
            font-family: roboto_regular;
            font-size: 16px;
            color: #000;
        }

        .item-quantity {
            font-family: roboto_medium;
            font-size: 16px;
            color: #171312;
        }

        .cart-summary {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ccc;
        }

        .total-price {
            font-family: roboto_bold;
            font-size: 24px;
            color: #03571A;
        }

        .checkout-btn {
            background-color: #03571A;
            color: #DEEEED;
            border: none;
            padding: 12px 24px;
            font-size: 18px;
            font-family: roboto_medium;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s;
            text-decoration: none;
        }

        .checkout-btn:hover {
            background-color: #00C47D;
        }

        .continue-shopping {
            text-decoration: none;
            font-family: roboto_regular;
            color: #03571A;
            font-size: 16px;
            margin-right: 15px;
        }

        .empty-cart {
            text-align: center;
            font-family: roboto_regular;
            font-size: 20px;
            color: #afafaf;
            margin-top: 40px;
        }

        .empty-cart a {
            display: block;
            margin-top: 20px;
            color: #03571A;
            text-decoration: underline;
        }

        .empty-cart a:hover {
            color: #00C47D;
        }

        .cart_total {
            font-family: roboto_bold; font-size: 16px; text-align: right; margin-top: 8px;
        }

        .total_price {
            color: #03571A;
        }

        @media (max-width: 480px) {
            .cart-container {
                margin: 20px 10px;
                padding: 15px;
            }

            .cart-title {
                font-size: 28px;
            }

            .item-info {
                flex-direction: row;
                align-items: center;
            }

            .item-image {
                width: 60px;
                height: 60px;
            }

            .item-name {
                font-size: 16px;
            }

            .item-price,
            .item-quantity,
            .item-total {
                font-size: 14px;
            }

            .cart-actions {
                align-items: flex-end;
                margin-top: 10px;
            }

            .cart-summary {
                flex-direction: column;
                gap: 15px;
            }

            .checkout-btn {
                width: 100%;
                font-size: 16px;
                text-decoration: none;
            }

            .continue-shopping {
                margin-bottom: 10px;
                font-size: 14px;
            }

            .cart_total {
                font-size: 14px;
                text-align: left;
                margin-top: 10px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'templates/header.php'; ?>

    <div class="cart-container">
        <h1 class="cart-title">Ваша корзина</h1>

        <?php if ($items): ?>
        <?php $totalPrice = 0; ?>
        <?php foreach ($items as $item): ?>
            <div class="cart-item">
                <div class="item-info">
                    <img src="<?= 'uploads/products/' . htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                    <div>
                        <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                        <div class="item-price">Цена: <?= number_format($item['price'], 2, '.', ' ') ?> ₽ x <?= $item['quantity'] ?></div>
                    </div>
                </div>
                <div class="cart-actions">
                    <div class="item-quantity">Количество: <?= $item['quantity'] ?></div>
                    <div class="item-total">Итого: <?= number_format($item['price'] * $item['quantity'], 2, '.', ' ') ?> ₽</div>
                </div>
            </div>
            <?php $totalPrice += $item['price'] * $item['quantity']; ?>
        <?php endforeach; ?>

            <div class="cart-summary">
                <a href="catalog.php" class="continue-shopping">← Продолжить покупки</a>
                <a href="checkout.php?cart_id=<?= $cartId ?>" class="checkout-btn">Оформить заказ</a>
                <!-- <button class="checkout-btn" id="checkoutButton">Оформить заказ</button> -->
            </div>

        <div class="cart-total">
            <p class="cart_total">
                Общая сумма: <span class="total_price"><?= number_format($totalPrice, 2, '.', ' ') ?> ₽</span>
            </p>
        </div>

        <?php else: ?>
            <p class="empty-cart">Корзина пуста. <a href="catalog.php">Перейти к товарам</a></p>
        <?php endif; ?>
    </div>

    <?php require_once 'templates/footer.php'; ?>

    <script>
document.getElementById('checkoutButton').addEventListener('click', async function () {
    try {
        const response = await fetch('cart/checkout.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                cart_id: <?= json_encode($cartId) ?>
            })
        });

        if (!response.ok) {
            const text = await response.text(); // Получаем ответ как текст
            console.error("Ошибка сервера:", text);
            alert("Ошибка: " + text);
            throw new Error("HTTP error! status: " + response.status);
        }

        const data = await response.json();

        if (data.success) {
            alert('Заказ оформлен!');
            window.location.reload();
        } else {
            alert(data.message || 'Не удалось оформить заказ');
        }

    } catch (error) {
        console.error("Network error:", error);
        alert("Произошла сетевая ошибка");
    }
});
</script>

</body>
</html>