<?php
include "functions.php";
$link = connect_to_db();

    if ($_COOKIE["token"]){
        $tokenFromClient = $_COOKIE["token"];
        $query = "select user_id from auth where token ='".$tokenFromClient."'";
        $result = mysqli_query($link,$query);
        $row = mysqli_fetch_row($result);
        if ($row){
          $authBool = true;
            header("Location:  http://judex.tech/");
        }  else {
            setcookie("token","",time()-5);
            $authBool = false;
        }
        //$userId = $row[0];
        mysqli_free_result($result);
    }

if (isset($_POST['submit'])){
        $login = $_POST["login"];
        $password = $_POST["password"];

        $query = "select id from users where login='".$login."' and password='".MD5($password)."'";
        $result = mysqli_query($link,$query);
        $row = mysqli_fetch_row($result);
        if ($row){
            $token = "";
            $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ123456789";
            $numChars = strlen($chars);
            for ($i = 0; $i < 32; $i++){
                $token.= substr($chars, rand(1,$numChars)-1, 1);
            }
            mysqli_free_result($result);
            $query = "delete from auth where user_id =".$row[0];
            mysqli_query($link,$query);
            $query = "insert into auth (user_id, token, date) values (".$row[0].", '$token', '".date("Y-m-d H:i:s")."' )";
            mysqli_query($link,$query);
            setcookie("token", "".$token, time()+(86400*3), "/");
            if ($_COOKIE["logoutFrom"]){
                $tmpUrl = $_COOKIE['logoutFrom'];
                setcookie("logoutFrom" ," " , time()-5);
                header("Location: $tmpUrl");
            } else {
                header("Location:  http://judex.tech/");
            }
        } else {
            $errorText = "Неверный логин или пароль";
        }

    }
?>


<html>
<header>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Login</title>
    <meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width" />
    <style>
        body{
            margin: 0;
            padding: 0;
        }

    </style>
</header>

<body background="img/124.png">
<center>
<div class="authContainer">

<div class="authFormDiv">
    <form class="authForm" name="loginForm" action="login.php" method="POST">
        <label class="authLabel">Вход</label>
        <input class="authInput" type="text" <?php if($authBool) echo "disabled"?> name="login" required autofocus id="login" title="Логин" placeholder="Введите логин">
        <input class="authInput" type="password" <?php if($authBool) echo "disabled"?> required name="password" id="password" title="Пароль"  placeholder="Введите пароль">
        <p><?php if ($errorText) echo $errorText;?></p>
        <input class="authButton" type="submit" <?php if($authBool) echo "disabled"?> value="Войти"  name="submit" ><br>
        <span class="authChangePage">Нет аккаунта? <a href="registration.php">Зарегистрироваться!</a></span>
    </form>

    </div>
    </div>
</center>

</body>

</html>
