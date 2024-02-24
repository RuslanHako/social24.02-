<?php
session_start();
include("db.php");

function setSession($id, $us_name, $admin, $age)
{
    $_SESSION['id'] = $id;
    $_SESSION['login'] = $us_name;
    $_SESSION['admin'] = $admin;
    $_SESSION['age'] = $age;
}

// Регистрация нового пользователя

// isset — Определяет, была ли установлена переменная значением, отличным от null
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-reg'])) {
    // Получаем данные из формы
    $us_name = $_POST['login'];
    $email = $_POST['email'];
    $age = $_POST['age'];
    $pass_first = $_POST['pass-first'];
    $pass_second = $_POST['pass-second'];

    // Проверяем, совпадают ли пароли
    if ($pass_first !== $pass_second) {
        echo "Пароли не совпадают";
    } else {
        // Хэшируем пароль
        $hashed_password = password_hash($pass_first, PASSWORD_DEFAULT);

        // Проверяем, существует ли пароль с таким email
        $check_email_query = "SELECT * FROM users WHERE email='$email'";
        // Для выполнения запросов у объекта mysqli вызывается метод query(), в который передается выполняемая команда SQL
        $check_email_result = $conn->query($check_email_query);

        // num_rows — Получает количество строк в наборе результатов
        if ($check_email_result->num_rows > 0) {
            echo "Пользователь с таким адресом электронной почты уже существует";
        } else {
            // Устанавливаем значения поля admin
            $admin = 0;
        }

        // Подготавливаем и выполняем запрос на вставку данных в базу
        $stmt = $conn->prepare("INSERT INTO users (admin, us_name, email, age, password) VALUES (?, ?, ?, ?, ?)");

        $stmt->bind_param("issss", $admin, $us_name, $email, $age, $hashed_password);

        if ($stmt->execute()) {
            echo "Регистрация успешна.";
            setSession($conn->insert_id, $us_name, 0, $age);
            header("Location: profile/accaunt.php");
            exit();
        } else {
            echo "Ошибка при регистрации: " . $conn->error;
        }
        $stmt->close();
    }
}

// Авторизация пользователя
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-log'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Подготавливаем запрос на выборку данных пользователя из базы
    $stmt = $conn->prepare("SELECT id, us_name, admin, age, password FROM users WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        // Проверяем введенный пароль с хэшированным паролем в базе
        if (password_verify($password, $row['password'])) {
            echo "Авторизация успешна. Добро пожаловать, " . $row['us_name'];
            setSession(
                $row['id'],
                $row['us_name'],
                $row['admin'],
                $row['age']
            );
            header("Location: profile/accaunt.php");
            exit(); // Добавлено
        } else {
            echo "Неверный пароль.";
        }
    } else {
        echo "Пользователь с таким адресом электронной почты не найден.";
    }
    $stmt->close();
}











// Редактирование профиля пользователя

// isset — Определяет, была ли установлена переменная значением, отличным от null
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['button-upd'])) {
    // Получаем данные из формы
    $id=$_SESSION['id'];
    $us_name = $_POST['user-name'];
    $email = $_POST['user-email'];
    $age = $_POST['user-age'];
    $info = $_POST['user-info'];
    $pass_first = $_POST['pass-first'];
    $pass_second = $_POST['pass-second'];

    // Проверяем, совпадают ли пароли
    if ($pass_first !== $pass_second) {
        echo "Пароли не совпадают";
    } else {
        //был ли введен новый пароль, если да, то хэщируем и помещаем в кусочек запроса,
        //иначе в заппросе новый пароль не указываем 
        if(!empty($pass_first)){
    // Хэшируем пароль
        $hashed_password = password_hash($pass_first, PASSWORD_DEFAULT);
        $password_update=", password=hashed_passowrd";
        } else{
            //если пароль не введен, то оптсавляем прежний пароль
            $password_update="";
        }
        // првоеряем существуе ли пользователь с  таким email, исключая текущенго пользователя
        $check_email_query = "SELECT * FROM users WHERE email='$email' AND id |=$id";
    
        // Для выполнения запросов у объекта mysqli вызывается метод query(), в который передается выполняемая команда SQL
        $check_email_result = $conn->query($check_email_query);

        // num_rows — Получает количество строк в наборе результатов
        if ($check_email_result && $check_email_result->num_rows > 0) {
            echo "Пользователь с таким адресом электронной почты уже существует";
        } else {
            // подготавливаем и выполняеи запрос на обновление данных в базе
            $info_escaped=mysql_real_escape_string($conn,$info);
            $update_qery = "update users SEt us_name = '$us_name', email = '$email', age= '$age', $password_update, info='$info_escated' WHERE id = $id";
            if($conn-> query($update_qery) === TRUE){
                echo "Данные Успешно Изменены.";
                $_SESSION['login'] = $us_name;
                $_SESSION['age'] = $age;
                header("Location:http://localhost/Social/profile/accaunt.php")
                exit();
                
            } else{
                "Ошибка при обновлении данных:" $conn+->error
            }
            }
        }
    }
