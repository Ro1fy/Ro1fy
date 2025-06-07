<?php
session_start();

if (!isset($_SESSION['cart_id'])){
    $_SESSION['cart_id'] = null;
}

if (isset($_GET['login']) && $_GET['login'] === 'success') {
    echo '<div class="success-message">Вы успешно вошли в систему!</div>';
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Pet Shop</title>
</head>
<body>
<?php require_once 'templates/header.php' ?>
    <section class="main">
        <div class="main_title">
            <h1 class="title_name">Качественный товар для любых животных</h1>
            <p class="title_discript">У нас самый лучший корм, и все питомцы остаются счатсливы!</p>
            <div class="main_btn">
                <a href="" class="main_btn_link">Подробнее</a>
            </div>
        </div>
        <img src="img/main_dog.png" class="main_img">
    </section>
    <section class="popular_categories">
        <h1 class="popular_categories_title" id="blog">Наш блог</h1>
        <div class="popular_cards">
            <div class="popular_card">
                <img src="img/dog_popular.png" class="popular_card_img">
                <p class="card_name">Сколько спят собаки</p>
                <div class="main_btn popular_btn">
                    <a href="" class="main_btn_link popular_btn_link">Подробнее</a>
                </div>
            </div>
            <div class="popular_card">
                <img src="img/popular_cat.png" class="popular_card_img">
                <p class="card_name">Как купать кошку</p>
                <div class="main_btn popular_btn">
                    <a href="" class="main_btn_link popular_btn_link">Подробнее</a>
                </div>
            </div>
            <div class="popular_card">
                <img src="img/popular_napoln.png" class="popular_card_img">
                <p class="card_name">Какое имя дать коту</p>
                <div class="main_btn popular_btn">
                    <a href="" class="main_btn_link popular_btn_link">Подробнее</a>
                </div>
            </div>
        </div>
    </section>
    <section class="comments">
        <h1 class="comments_title">Отзывы</h1>
        <div class="comment">
            <div class="comment_card">
                <p class="comment_text">“Заказываю корм уже третий раз — всегда быстрая доставка, свежий товар. Цены ниже, чем в соседних зоомагазинах.!”</p>
                <p class="comment_name">Алексей</p>
            </div>
            <div class="comment_card">
                <p class="comment_text">“Отличный магазин! Купили поводок и игрушку — качество на высоте. Пес не расстается с новой резиновой косточкой.”<br></p>
                <p class="comment_name">Мария</p>
            </div>
            <div class="comment_card">
                <p class="comment_text">“Впервые попробовал этот магазин — не разочаровался! Корм для попугаев качественный, зерна чистые.”<br></p>
                <p class="comment_name">Олег</p>
            </div>
        </div>
        <div class="main_btn comment_btn">
            <a href="reviews.php" class="main_btn_link popular_btn_link">Больше отзывов</a>
        </div>
    </section>
    <section class="add_comment">
        <div class="add_comment_items">
            <form class="comment_form">
                <h1 class="add_comment_title">Оставить отзыв</h1>
                <input type="text" class="add_comment_name" placeholder="Имя">
                <textarea name="comm" class="comm" id="comm" placeholder="Отзыв"></textarea>
                <input type="button" value="Оставить отзыв" class="comm_btn">
            </form>
            <img src="/img/comment.png" class="add_comment_img">
        </div>
    </section>
    <section class="newsletter">
        <div class="newsletter_items">
            <img src="/img/cat.png" class="newsletter_img">
            <form class="newsletter_form">
                <h1 class="newsletter_title">Подпишитесь на <br>нашу расслыку</h1>
                <input type="text" class="newsletter_name" placeholder="Имя">
                <input type="text" class="newsletter_email" placeholder="Эл. Почта">
                <input type="button" value="Подписаться" class="newsletter_btn">
            </form>
        </div>
        <div class="catalog_link">
            <a href="..\catalog.php" class="catalog_link_btn">Перейти в каталог</a>
        </div>
    </section> 
    <?php require_once 'templates/footer.php' ?></php>


    <script src="js/main.js"></script>
    <script>
document.querySelector('.comm_btn').addEventListener('click', async function () {
    const nameInput = document.querySelector('.add_comment_name');
    const textInput = document.querySelector('.comm');
    const name = nameInput.value.trim();
    const text = textInput.value.trim();

    if (!name || !text) {
        alert('Пожалуйста, заполните имя и текст отзыва');
        return;
    }

    try {
        const response = await fetch('add_review.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ name, text })
        });

        if (!response.ok) {
            const text = await response.text(); // Получаем ответ как текст
            console.error("Server error:", text);
            throw new Error("HTTP error! status: " + response.status);
        }

        const data = await response.json();

        if (data.success) {
            alert('Спасибо за отзыв!');
            nameInput.value = '';
            textInput.value = '';
        } else {
            alert(data.message || 'Не удалось сохранить отзыв');
        }

    } catch (error) {
        console.error('Network error:', error);
        alert('Произошла сетевая ошибка');
    }
});
</script>
</body>
</html>