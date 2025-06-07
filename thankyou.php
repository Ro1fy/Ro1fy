<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Заказ оформлен</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .thankyou-container {
            max-width: 600px;
            margin: 80px auto;
            padding: 40px;
            background: #fff;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }

        .thankyou-title {
            font-family: roboto_bold;
            font-size: 32px;
            color: #03571A;
            margin-bottom: 20px;
        }

        .thankyou-message {
            font-family: roboto_regular;
            font-size: 18px;
            margin-bottom: 30px;
        }

        .back-to-catalog {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #03571A;
            color: #DEEEED;
            text-decoration: none;
            border-radius: 8px;
            font-family: roboto_medium;
            font-size: 16px;
        }

        .back-to-catalog:hover {
            background-color: #00C47D;
        }
    </style>
</head>
<body>

<?php require_once 'templates/header.php'; ?>

<div class="thankyou-container">
    <h1 class="thankyou-title">✅ Заказ оформлен!</h1>
    <p class="thankyou-message">Благодарим вас за покупку. Наш менеджер свяжется с вами для подтверждения.</p>
    <a href="/catalog.php" class="back-to-catalog">← Вернуться к товарам</a>
</div>

<?php require_once 'templates/footer.php'; ?>
</body>
</html>