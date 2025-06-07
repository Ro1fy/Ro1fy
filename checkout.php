<?php
session_start();
require_once 'auth/db_config.php';

if (!isset($_SESSION['user'])) {
    header("Location: /");
    exit;
}



$userId = $_SESSION['user']['id'];
$cartId = isset($_GET['cart_id']) ? (int)$_GET['cart_id'] : null;

if (!$cartId) {
    die('<p>Корзина не найдена</p>');
}

if (!$cartId) {
    die('<p>Корзина не найдена</p>');
}

try {
    // Получаем товары из корзины
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("
        SELECT ci.quantity, p.name, p.price 
        FROM cart_items ci
        JOIN products p ON ci.product_id = p.id
        WHERE ci.cart_id = ?
    ");
    $stmt->execute([$cartId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$items) {
        die('<p>Корзина пуста</p>');
    }

} catch (PDOException $e) {
    die('Ошибка базы данных: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .checkout-container {
            max-width: 600px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            font-family: roboto_regular;
        }

        .checkout-title {
            font-family: roboto_bold;
            font-size: 32px;
            color: #03571A;
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-family: roboto_bold;
            margin-bottom: 8px;
            color: #171312;
        }

        input[type="text"],
        input[type="tel"],
        input[type="textarea"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-family: roboto_regular;
            font-size: 16px;
        }

        .submit-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background-color: #03571A;
            color: #DEEEED;
            border: none;
            border-radius: 8px;
            font-family: roboto_medium;
            font-size: 18px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .submit-btn:hover {
            background-color: #00C47D;
        }
    </style>
</head>
<body>

<?php require_once 'templates/header.php'; ?>

<div class="checkout-container">
    <h1 class="checkout-title">Оформление заказа</h1>

    <form id="checkoutForm">
        <div class="form-group">
            <label for="address">Адрес доставки:</label>
            <input type="text" id="address" name="address" placeholder="Введите ваш адрес" required>
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone" placeholder="+7 (999) 999-99-99">
        </div>

        <button type="submit" class="submit-btn">Подтвердить заказ</button>
</form>
</div>

<script>
document.getElementById('checkoutForm').addEventListener('submit', async function (event) {
    event.preventDefault();

    const address = document.querySelector('#address').value.trim();
    const phone = document.querySelector('#phone').value.trim();

    if (!address) {
        alert('Пожалуйста, укажите адрес доставки');
        return;
    }

    try {
    const response = await fetch('process_checkout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            cart_id: <?= json_encode($cartId) ?>,
            address: address,
            phone: phone
        })
    });

    if (!response.ok) {
        const text = await response.text(); // Получаем текст ответа
        console.error('Server error:', text); // Смотри в DevTools
        alert('Ошибка сервера: ' + text);
        throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json(); // Парсим JSON

    if (data.success) {
        window.location.href = '/thankyou.php';
    } else {
        alert(data.message || 'Не удалось оформить заказ');
    }

} catch (error) {
    console.error('Network error:', error);
    alert('Произошла сетевая ошибка');
}
});
</script>

<?php require_once 'templates/footer.php'; ?>
</body>
</html>