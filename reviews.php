<?php
session_start();
require_once 'auth/db_config.php';

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $dbpassword);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Получаем все отзывы
    $stmt = $db->query("SELECT * FROM reviews ORDER BY created_at DESC");
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die('Ошибка подключения к БД: ' . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Отзывы - Pet Shop</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<?php require_once 'templates/header.php'; ?>

<section class="reviews-section">
    <h1 class="reviews-title">Все отзывы</h1>

    <?php if ($reviews): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <p class="review-text"><?= htmlspecialchars($review['text']) ?></p>
                <p class="review-name"><?= htmlspecialchars($review['name']) ?></p>
                <p class="review-date">Дата: <?= htmlspecialchars($review['created_at']) ?></p>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-reviews">Пока нет отзывов.</p>
    <?php endif; ?>
</section>

<a href="index.php" class="catalog_link_btn">← Вернуться</a>

<?php require_once 'templates/footer.php'; ?>

</body>
</html>