<?php
include "standart.php";
$viewUserObj;
if (isset($_GET['id'])){
    $viewUserId = $_GET['id'];
    if ($viewUserId == $userId){
    header("Location: /profile.php");
    }
    $query = "select login, name, rating from users where id = $viewUserId";
    $result = mysqli_query($link, $query);
    $viewUserObj = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    if (!$viewUserObj){
        header("Location: /404.php");
    }
} else {
    header("Location: /404.php");
}





?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title><?php echo $viewUserObj['login']; ?></title>
</head>

<body background="img/124.png">
<?php include "views/navbar.php";?>
<center>
    <h1>Страница пользователя</h1>
    <h2><?php echo $viewUserObj['login']." (".$viewUserObj['name'].")"; ?></h2>
    <h3>Рейтинг: <?php echo $viewUserObj['rating'];?></h3>
    <input type="button" class="btn green small" value="Покекать" onclick="alert('Кек, кек, кек');">
</center>
</body>

</html>
