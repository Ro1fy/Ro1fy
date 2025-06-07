
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
    
    <!-- Модальное окно входа -->
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Вход</h2>
            <form id="loginForm" class="auth-form">
                <div class="form-group">
                    <label for="loginEmail">Электронная почта</label>
                    <input type="email" id="loginEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Пароль</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="auth-btn">Войти</button>
                <div class="auth-switch">
                    Нет аккаунта? <a href="#" id="showRegister">Зарегистрироваться</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Модальное окно регистрации -->
    <div id="registerModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>Регистрация</h2>
            <form id="registerForm" class="auth-form">
                <div class="form-group">
                    <label for="regName">Имя</label>
                    <input type="text" id="regName" name="name" required>
                </div>
                <div class="form-group">
                    <label for="regEmail">Электронная почта</label>
                    <input type="email" id="regEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="regPassword">Пароль</label>
                    <input type="password" id="regPassword" name="password" required>
                </div>
                <div class="form-group">
                    <label for="regConfirmPassword">Подтвердите пароль</label>
                    <input type="password" id="regConfirmPassword" name="confirmPassword" required>
                </div>
                <button type="submit" class="auth-btn">Зарегистрироваться</button>
                <div class="auth-switch">
                    Уже есть аккаунт? <a href="#" id="showLogin">Войти</a>
                </div>
            </form>
        </div>
    </div>
    <script src="js/main.js"></script>
</body>
</html>

