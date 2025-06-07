<?php
session_start();

// Логируем содержимое сессии
error_log("Session data: " . print_r($_SESSION, true));
?>

<?php



if (!isset($_SESSION['cart_id'])){
    $_SESSION['cart_id'] = null;
}

require_once 'auth/db_config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем категории
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    
    // Получаем выбранную категорию (если есть)
    $selectedCategory = $_GET['category'] ?? null;
    
    // Получаем товары
    $query = "
        SELECT p.*, c.name as category_name, t.name as type_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN product_types t ON p.type_id = t.id
    ";
    
    $params = [];
    
    if ($selectedCategory) {
        $query .= " WHERE c.id = ?";
        $params[] = $selectedCategory;
    }
    
    $query .= " ORDER BY p.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Каталог товаров</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .catalog-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .catalog-title {
            color: #000000;
            font-family: roboto_bold;
            font-size: 54px;
            margin-bottom: 16px;
            text-align: center;
        }
        .category-filter {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 30px;
            justify-content: center;
        }
        .category-btn {
            padding: 8px 16px;
            background-color: #ffff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-family: roboto_regular;
            transition: all 0.3s;
            color: #171312;
            text-decoration: none;
        }
        .category-btn:hover, .category-btn.active {
            background-color: #03571A;
            color: white;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 30px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
            background-color: #ffff ;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .product-info {
            padding: 15px;
        }
        .product-name {
            font-family: roboto_bold;
            font-size: 18px;
            margin-bottom: 8px;
        }
        .product-category {
            display: inline-block;
            background-color: #f0f0f0;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-right: 5px;
        }
        .product-price {
            font-family: roboto_bold;
            color: #03571A;
            font-size: 20px;
            margin: 8px 0;
        }
        .add-to-cart {
            display: block;
            width: 100%;
            padding: 10px;
            margin-top: 16px;
            background-color: #03571A;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: roboto_medium;
            transition: background-color 0.3s;
        }
        .add-to-cart:hover {
            background-color: #00C47D;
        }

        @media (max-width: 480px) {
            .search_form {
                width: 380px;
            }
        }
    </style>
</head>
<body>
    <?php require_once 'templates/header.php' ?>
    
    <div class="catalog-container">
        <h1 class="catalog-title">Каталог товаров</h1>  
        <div class="category-filter">
            <a href="catalog.php" class="category-btn <?= !$selectedCategory ? 'active' : '' ?>">Все товары</a>
            <?php foreach ($categories as $category): ?>
                <a href="catalog.php?category=<?= $category['id'] ?>" class="category-btn <?= $selectedCategory == $category['id'] ? 'active' : '' ?>">
                    <?= $category['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="products-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-card">
                    <?php if ($product['image']): ?>
                        <img src="uploads/products/<?= $product['image'] ?>" alt="<?= $product['name'] ?>" class="product-image">
                    <?php else: ?>
                        <div class="product-image" style="background-color: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                            <span>Нет изображения</span>
                        </div>
                    <?php endif; ?>
                    <div class="product-info">
                        <h3 class="product-name"><?= $product['name'] ?></h3>
                        <span class="product-category"><?= $product['category_name'] ?></span>
                        <span class="product-category"><?= $product['type_name'] ?></span>
                        <div class="product-price"><?= number_format($product['price'], 2, '.', ' ') ?> ₽</div>
                        <p><?= $product['description'] ?></p>
                        <button class="add-to-cart" data-product-id="<?= $product['id'] ?>">В корзину</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <?php require_once 'templates/footer.php' ?>
        <script>
    document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', async function () {
        const productId = this.getAttribute('data-product-id');
        const originalText = this.textContent;
        const button = this;

        try {
            button.disabled = true;
            button.textContent = 'Добавляем...';

            const response = await fetch('cart/add.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ product_id: productId })
            });

            const data = await response.json();

            if (data.success) {
                // Обновляем счётчик корзины, если есть функция updateCartCounter()
                if (typeof updateCartCounter === 'function') {
                    updateCartCounter();
                }
                window.location.href = 'cart.php';
            } else {
                alert(data.message || 'Ошибка при добавлении в корзину');
                button.textContent = originalText;
                button.disabled = false;
            }

        } catch (error) {
            console.error('Network error:', error);
            alert('Произошла сетевая ошибка');
            button.textContent = originalText;
            button.disabled = false;
        }
    });
});
        </script>
</body>
</html>