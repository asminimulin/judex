<?php
include_once "include/functions.php";
    function get_dbconnection() {
        $dbinfo = parse_config("../conf.d/database.conf");
        $result = mysqli_connect($dbinfo["host"], $dbinfo["username"], $dbinfo["password"], $dbinfo["dbname"]);
        if(!$result) {
            $strErr = "[".date("Y-m-d H:i:s")."] ERROR : Can't connect to DB on page ".$_SERVER['REQUEST_URI']."\n";
            $file = fopen("../logs/main.log","a");
            fwrite($file,$strErr);
            fclose($file);
            die("DB ERROR");
        }
        return $result;
    }
    $link = get_dbconnection();
    $login = ""; $email = ""; $name = "";
    $message = "";

    if (isset($_POST['submit'])) {
        $login = $_POST["login"];
        $password = $_POST["password"];
        $repeat_password = $_POST["repeat_password"];
        $email = $_POST["email"];
        $first_name = $_POST['first_name'];
        $last_name = $_POST["last_name"];
        $capchaResponse = $_POST['g-recaptcha-response'];
        if (!$capchaResponse){
            $message = "Пройдите reCAPTCHA";
            goto flag;
        }
        //var_dump($capchaResponse);
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $data = array("secret" => "6LeLap0UAAAAAB4H2yLmi9yoa8aIYDmSWaXitnZ8", "response" => $capchaResponse);
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $res = file_get_contents($url, false, $context);
        //var_dump($res);
        if(!preg_match("/[a-zA-Z0-9_.]{4,50}/", $login)) {
            $message = "Некорректный логин";
            goto flag;
        } else if(!preg_match("/[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}/", $password)) {
            $message = "Некорректный пароль";
            goto flag;
        } else if(!preg_match("/[a-zA-Z0-9\.]+@[a-zA-Z0-0]+.[a-zA-Z]+/", $email) || strlen($email) > 50 ) {
            $message = "Некорректный email";
            goto flag;
        } else if (!preg_match("/[a-zA-Zа-яА-Я-]+/",$first_name) || strlen($first_name) > 50){
            $message = "Некорректное имя";
            goto flag;
        } else if (!preg_match("/[a-zA-Zа-яА-Я-]+/",$last_name) || strlen($last_name) > 50){
            $message = "Некорректная фамилия";
            goto flag;
        } else if ($password != $repeat_password) {
            $message = "Пароли не совпадают";
            goto flag;
        } else if (!$res['success']){
            $message = "Пройдите reCAPTCHA";
            goto flag;
        }

        $query = "select id from users where login = '".$login."'";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row){
             $message = "Данный логин уже занят";
            mysqli_free_result($result);
            goto flag;
        }
        mysqli_free_result($result);

        $query = "select id from users where email = '".$email."'";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_assoc($result);
        if ($row){
            $message = "Данный email уже занят";
            mysqli_free_result($result);
            goto flag;
        }
        mysqli_free_result($result);

        flag:
        if ($message == "") {
            $query = "INSERT INTO users(login, password, email, first_name, last_name) VALUES('$login', MD5('$password'), '$email', '$first_name', '$last_name')";
            if (!mysqli_query($link, $query)) {
                echo mysqli_error($link) . "\n";
                die("Cannot insert new user to database: $query");
            }
            $message = "OK";
            mysqli_close($link);
        }

    }

    
?>



<html>
<head>
    <meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width" />
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Registation</title>
</head>

<body background="img/124.png">
<center>
    <div class="authContainer">

        <div class="authFormDiv">
            <form class="authForm" name="loginForm" action="registration.php" method="POST">
                <label class="authLabel">Регистрация</label>
                <input class="authInput" type="text" pattern="[a-zA-Z0-9_.]{4,50}" name="login" required autofocus id="login" title="[a-zA-Z0-9_.]: 4-50 символов" placeholder="Логин" <?php  echo "value='".$login."'"?>>
                <input class="authInput" type="email"  pattern="[a-zA-Z0-9\.]+@[a-zA-Z0-0]+.[a-zA-Z]+" required name="email" id="email" title="example@mail.dev" placeholder="Email" <?php  echo "value='$email'"?>>
                <input class="authInput" type="text" pattern="[a-zA-Zа-яА-Я-]+" required name="first_name" id="first_name" title="[a-zA-Zа-яА-Я-]: 1-50 символов"  placeholder="Имя" <?php  echo "value='$first_name'"?>>
                <input class="authInput" type="text" pattern="[a-zA-Zа-яА-Я-]+" required name="last_name" id="last_name" title="[a-zA-Zа-яА-Я-]: 1-50 символов"  placeholder="Фамилия" <?php  echo "value='$last_name'"?>>
                <input class="authInput" type="password" pattern="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}"  required name="password" id="password" title="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]: 6-50 символов"  placeholder="Пароль">
                <input class="authInput" type="password" pattern="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}"  required name="repeat_password" id="repeat_password"  title="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]: 6-50 символов"  placeholder="Повторите пароль">
                <div class = "g-recaptcha" data-sitekey = "6LeLap0UAAAAAOPK8DYOXpSsrNAS8tk9mAK6sDpu" ></div>
                <p class="authNotification"><?php if($message != 'OK') {echo $message;} ?></p>
                <input class="authButton" type="submit"  value="Зарегистрироваться"  name="submit" ><br>
                <span class="authChangePage">Есть аккаунт? <a href="login.php">Войти!</a></span>
            </form>

        </div>
    </div>
</center>
<script src = "https://www.google.com/recaptcha/api.js" async defer></script>
<script>
    <?php
    if ($message == 'OK') echo "alert('Вы успешно зарегистрированы');\nlocation = 'login.php'";
    ?>
</script>
</body>

</html>
