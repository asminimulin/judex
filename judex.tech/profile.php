<?php
 include_once "include/standart.php";
 $query = "select first_name, last_name from users where id = $userId";
 $result = mysqli_query($link, $query);
 $userName  = mysqli_fetch_assoc($result);
 mysqli_free_result($result);

?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <title>Profile</title>
</head>

<body background="img/124.png">
<?php include_once "views/navbar.php";?>
<center>
    <h1>Профиль</h1>
    <h2><?php echo $userName["first_name"]." ".$userName["last_name"] ;?></h2>
    <h3>Ваш рейтинг: <?php
        $query = "select rating from users where id=$userId";
        $result = mysqli_query($link, $query);
        $row = mysqli_fetch_assoc($result);
        mysqli_free_result($result);
        echo $row['rating'];


        ?></h3>
    <input type="button" value="Редактировать" class="btn blue small" onclick="location='/editprofile.php'"><br>
    <input type="button" value="Выйти" class="btn red small" onclick="logout();">
</center>
<script>
    function logout(){
        setCookie("token", "", {expires:-1});
        location = '/login.php';
    }

    function setCookie(name, value, options) {
        options = options || {};

        var expires = options.expires;

        if (typeof expires == "number" && expires) {
            var d = new Date();
            d.setTime(d.getTime() + expires * 1000);
            expires = options.expires = d;
        }
        if (expires && expires.toUTCString) {
            options.expires = expires.toUTCString();
        }

        value = encodeURIComponent(value);

        var updatedCookie = name + "=" + value;

        for (var propName in options) {
            updatedCookie += "; " + propName;
            var propValue = options[propName];
            if (propValue !== true) {
                updatedCookie += "=" + propValue;
            }
        }

        document.cookie = updatedCookie;
    }
</script>
</body>

</html>
