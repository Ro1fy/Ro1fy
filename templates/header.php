<!-- header -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Pet Shop</title>
</head>
<body>
    <header class="header">
        <h1 class="header_logo"><a href="index.php" class="header_logo_link">Pet Shop</a></h1>
        <nav class="navigation">
            <ul class="navigation_list">
                <li class="navigation_item"><a class="navigation_link" href="../catalog.php">Каталог</a></li>
                <li class="navigation_item"><a class="navigation_link" href="#blog">Блог</a></li>
                <li class="navigation_item"><a class="navigation_link" href="#contacts">Контакты</a></li>
            </ul>
        </nav>
        <a href="lk.php" class="header_lk_link">
            <img src="img/1250689.png" class="headear_profile">
        </a>
        <a href="cart.php">
            <img src="img/korzina.png" class="header_been" alt="Корзина">
        </a>
        <div>
    <?php if(isset($_SESSION['user'])): ?>
        <button class="header_btn logged-in" disabled>  
            <?= htmlspecialchars($_SESSION['user']['name']) ?>
        </button>
    <?php else: ?>
        <button id="loginBtn" class="header_btn">Войти</button>
    <?php endif; ?>
</div>
    </header>


    <!-- <?php 
        require_once 'modal.php';
    ?> -->

    <?php
        if(isset($_SESSION['msg'])) {
            echo $_SESSION['msg'];
            unset($_SESSION['msg']);
        }
    ?>
    <script src="js/main.js"></script>
    
</body>
    