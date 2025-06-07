// modal.js
document.addEventListener('DOMContentLoaded', function() {
    // Элементы модальных окон
    const loginModal = document.getElementById('loginModal');
    const registerModal = document.getElementById('registerModal');
    const loginBtn = document.getElementById('loginBtn');
    const showRegister = document.getElementById('showRegister');
    const showLogin = document.getElementById('showLogin');
    const closeButtons = document.querySelectorAll('.close');
    
    // Если есть кнопка входа, добавляем обработчик
    if (loginBtn) {
        loginBtn.onclick = function() {
            loginModal.style.display = 'block';
            document.body.style.overflow = 'hidden'; // Блокируем прокрутку страницы
        }
    }
    
    // Переключение между окнами входа и регистрации
    if (showRegister) {
        showRegister.onclick = function(e) {
            e.preventDefault();
            loginModal.style.display = 'none';
            registerModal.style.display = 'block';
        }
    }
    
    if (showLogin) {
        showLogin.onclick = function(e) {
            e.preventDefault();
            registerModal.style.display = 'none';
            loginModal.style.display = 'block';
        }
    }
    
    // Закрытие модальных окон при клике на крестик
    closeButtons.forEach(function(btn) {
        btn.onclick = function() {
            loginModal.style.display = 'none';
            registerModal.style.display = 'none';
            document.body.style.overflow = 'auto'; // Возвращаем прокрутку
        }
    });
    
    // Закрытие модальных окон при клике вне их области
    window.onclick = function(event) {
        if (event.target == loginModal) {
            loginModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
        if (event.target == registerModal) {
            registerModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    }
    
    // Обработка формы входа
    // Обработка формы входа
if (document.getElementById('loginForm')) {
    document.getElementById('loginForm').onsubmit = function(e) {
        e.preventDefault();
        
        // Показываем индикатор загрузки
        const submitBtn = this.querySelector('.auth-btn');
        const originalBtnText = submitBtn.textContent;
        submitBtn.textContent = 'Вход...';
        submitBtn.disabled = true;
        
        // Собираем данные формы
        const formData = new FormData(this);
        
        // Отправляем данные на сервер
        fetch('auth/login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Закрываем модальное окно
                loginModal.style.display = 'none';
                document.body.style.overflow = 'auto';
                
                // Обновляем страницу
                window.location.reload();
            } else {
                alert(data.message || 'Ошибка входа');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при входе');
        })
        .finally(() => {
            submitBtn.textContent = originalBtnText;
            submitBtn.disabled = false;
        });
        
        return false;
    }
}
    
    // Обработка формы регистрации
    if (document.getElementById('registerForm')) {
        document.getElementById('registerForm').onsubmit = function(e) {
            e.preventDefault();
            
            // Проверка совпадения паролей
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('regConfirmPassword').value;
            
            if (password !== confirmPassword) {
                alert('Пароли не совпадают!');
                return false;
            }
            
            // Здесь AJAX запрос для регистрации
            const formData = new FormData(this);
            
            fetch('auth/register.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Регистрация успешна! Теперь вы можете войти.');
                    registerModal.style.display = 'none';
                    loginModal.style.display = 'block';
                } else {
                    alert(data.message || 'Ошибка регистрации');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при регистрации');
            });
            
            return false;
        }
    }
});

