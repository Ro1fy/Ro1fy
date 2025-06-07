<?php
session_start();

// Проверка прав администратора
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: /");
    exit;
}

require_once 'auth/db_config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем категории и типы для формы
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    $types = $db->query("SELECT * FROM product_types")->fetchAll();
    
    // Обработка добавления товара
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $categoryId = (int)$_POST['category_id'];
        $typeId = (int)$_POST['type_id'];
        
        // Обработка загрузки изображения
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
        
        // Добавляем товар в БД
        $stmt = $db->prepare("INSERT INTO products (name, description, price, image, category_id, type_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, $categoryId, $typeId]);
        
        $success = "Товар успешно добавлен!";
    }
    
    // Получаем список товаров
    $products = $db->query("
        SELECT p.*, c.name as category_name, t.name as type_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN product_types t ON p.type_id = t.id
        ORDER BY p.created_at DESC
    ")->fetchAll();
    
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Управление товарами</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .admin-title {
            color: #03571A;
            margin-bottom: 30px;
            font-family: roboto_bold;
            font-size: 32px;
            text-align: center;
        }
        .admin-section {
            margin-bottom: 40px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            font-family: roboto_medium;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: roboto_regular;
        }
        .form-select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: roboto_regular;
        }
        .btn {
            padding: 10px 20px;
            background-color: #03571A;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: roboto_medium;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #00C47D;
        }
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
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
            margin-bottom: 10px;
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
            margin: 10px 0;
        }
        .success-message {
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/header.php' ?>
    
    <div class="admin-container">
        <h1 class="admin-title">Управление товарами</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="admin-section">
            <h2>Добавить новый товар</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Название товара</label>
                    <input type="text" id="name" name="name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Описание</label>
                    <textarea id="description" name="description" class="form-control" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Цена</label>
                    <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="category_id">Категория</label>
                    <select id="category_id" name="category_id" class="form-select" required>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="type_id">Тип товара</label>
                    <select id="type_id" name="type_id" class="form-select" required>
                        <?php foreach ($types as $type): ?>
                            <option value="<?= $type['id'] ?>"><?= $type['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="image">Изображение товара</label>
                    <input type="file" id="image" name="image" class="form-control" accept="image/*">
                </div>
                
                <button type="submit" class="btn">Добавить товар</button>
            </form>
        </div>
        
        <div class="admin-section">
            <h2>Список товаров</h2>
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
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php

// Проверка прав администратора
if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: /");
    exit;
}

require_once 'auth/db_config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Обработка удаления товара
    if (isset($_GET['delete'])) {
        $productId = (int)$_GET['delete'];
        
        // Сначала получаем информацию о товаре для удаления изображения
        $stmt = $db->prepare("SELECT image FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch();
        
        if ($product) {
            // Удаляем изображение, если оно есть
            if ($product['image'] && file_exists("uploads/products/" . $product['image'])) {
                unlink("uploads/products/" . $product['image']);
            }
            
            // Удаляем товар из БД
            $stmt = $db->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$productId]);
            
            $success = "Товар успешно удален!";
        }
    }
    
    // Получаем категории и типы для формы
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    $types = $db->query("SELECT * FROM product_types")->fetchAll();
    
    // Обработка добавления товара
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $categoryId = (int)$_POST['category_id'];
        $typeId = (int)$_POST['type_id'];
        
        // Обработка загрузки изображения
        $imageName = null;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
        
        // Добавляем товар в БД
        $stmt = $db->prepare("INSERT INTO products (name, description, price, image, category_id, type_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$name, $description, $price, $imageName, $categoryId, $typeId]);
        
        $success = "Товар успешно добавлен!";
    }
    
    // Получаем список товаров
    $products = $db->query("
        SELECT p.*, c.name as category_name, t.name as type_name 
        FROM products p
        JOIN categories c ON p.category_id = c.id
        JOIN product_types t ON p.type_id = t.id
        ORDER BY p.created_at DESC
    ")->fetchAll();
    
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- ... предыдущие метаданные и стили ... -->
    <style>
        /* Добавим стили для кнопки удаления */
        .product-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .delete-btn {
            padding: 8px 15px;
            background-color: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: roboto_medium;
            transition: background-color 0.3s;
        }
        .delete-btn:hover {
            background-color: #d32f2f;
        }
        .edit-btn {
            padding: 8px 15px;
            background-color: #2196F3;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: roboto_medium;
            transition: background-color 0.3s;
        }
        .edit-btn:hover {
            background-color: #0b7dda;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/header.php' ?>
    
    <div class="admin-container">
        <h1 class="admin-title">Управление товарами</h1>
        
        <?php if (isset($success)): ?>
            <div class="success-message"><?= $success ?></div>
        <?php endif; ?>
        
        <!-- Форма добавления товара (остается без изменений) -->
        
        <div class="admin-section">
            <h2>Список товаров</h2>
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
                            <div class="product-actions">
                                <a href="edit_product.php?id=<?= $product['id'] ?>" class="edit-btn">Редактировать</a>
                                <a href="admin.php?delete=<?= $product['id'] ?>" class="delete-btn" onclick="return confirm('Вы уверены, что хотите удалить этот товар?')">Удалить</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>


    <script>
    // Подтверждение удаления
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Вы уверены, что хотите удалить этот товар?')) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>
    <?php require_once 'templates/footer.php' ?>
</body>
</html>