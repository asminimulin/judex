<?php
 include "standart.php";
 $query = "select first_name, last_name from users where id = $userId";
 $result = mysqli_query($link, $query);
 $userName  = mysqli_fetch_assoc($result);
 mysqli_free_result($result);

?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>Profile</title>
</head>

<body background="img/124.png">
<?php include "views/navbar.php";?>
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
    <input type="button" value="Выйти" class="btn red small" onclick="location = '/logout.php'">
</center>

</body>

</html>
