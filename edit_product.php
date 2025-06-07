<?php
session_start();

if (!isset($_SESSION['user']) || empty($_SESSION['user']['is_admin'])) {
    header("Location: /");
    exit;
}

require_once 'auth/db_config.php';

$productId = $_GET['id'] ?? 0;

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Получаем данные товара
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if (!$product) {
        header("Location: admin.php");
        exit;
    }
    
    // Получаем категории и типы
    $categories = $db->query("SELECT * FROM categories")->fetchAll();
    $types = $db->query("SELECT * FROM product_types")->fetchAll();
    
    // Обработка формы редактирования
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $categoryId = (int)$_POST['category_id'];
        $typeId = (int)$_POST['type_id'];
        $deleteImage = isset($_POST['delete_image']);
        
        $imageName = $product['image'];
        
        // Удаление текущего изображения
        if ($deleteImage && $imageName) {
            if (file_exists("uploads/products/" . $imageName)) {
                unlink("uploads/products/" . $imageName);
            }
            $imageName = null;
        }
        
        // Загрузка нового изображения
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            // Удаляем старое изображение, если есть
            if ($imageName && file_exists("uploads/products/" . $imageName)) {
                unlink("uploads/products/" . $imageName);
            }
            
            $uploadDir = 'uploads/products/';
            $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $imageName = uniqid() . '.' . $extension;
            move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $imageName);
        }
        
        // Обновляем товар в БД
        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, image = ?, category_id = ?, type_id = ? WHERE id = ?");
        $stmt->execute([$name, $description, $price, $imageName, $categoryId, $typeId, $productId]);
        
        header("Location: admin.php?success=Товар успешно обновлен!");
        exit;
    }
    
} catch(PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Редактирование товара</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .edit-title {
            color: #03571A;
            margin-bottom: 30px;
            font-family: roboto_bold;
            font-size: 32px;
            text-align: center;
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
        .current-image {
            max-width: 200px;
            margin: 10px 0;
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            margin: 10px 0;
        }
        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }
        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <?php require_once 'templates/header.php' ?>
    
    <div class="edit-container">
        <h1 class="edit-title">Редактирование товара</h1>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Название товара</label>
                <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>
            
            <div class="form-group">
                <label for="description">Описание</label>
                <textarea id="description" name="description" class="form-control" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Цена</label>
                <input type="number" id="price" name="price" class="form-control" step="0.01" min="0" value="<?= $product['price'] ?>" required>
            </div>
            
            <div class="form-group">
                <label for="category_id">Категория</label>
                <select id="category_id" name="category_id" class="form-select" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= $category['id'] ?>" <?= $category['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= $category['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="type_id">Тип товара</label>
                <select id="type_id" name="type_id" class="form-select" required>
                    <?php foreach ($types as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= $type['id'] == $product['type_id'] ? 'selected' : '' ?>>
                            <?= $type['name'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Текущее изображение</label>
                <?php if ($product['image']): ?>
                    <img src="uploads/products/<?= $product['image'] ?>" class="current-image">
                    <div class="checkbox-group">
                        <input type="checkbox" id="delete_image" name="delete_image">
                        <label for="delete_image">Удалить текущее изображение</label>
                    </div>
                <?php else: ?>
                    <p>Нет изображения</p>
                <?php endif; ?>
            </div>
            
            <div class="form-group">
                <label for="image">Новое изображение товара</label>
                <input type="file" id="image" name="image" class="form-control" accept="image/*">
            </div>
            
            <div class="form-actions">
                <a href="admin.php" class="btn" style="background-color: #666;">Отмена</a>
                <button type="submit" class="btn">Сохранить изменения</button>
            </div>
        </form>
    </div>
    
    <?php require_once 'templates/footer.php' ?>
</body>
</html>