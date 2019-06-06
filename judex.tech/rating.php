<?php
include_once "include/standart.php";
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <title>TOP 50</title>
</head>

<body background="img/124.png">
<?php include_once "views/navbar.php";?>
<center>
    <h1>Рейтинг</h1>
    <table>
        <tr>
            <th>Место</th>
            <th>Пользователь</th>
            <th>Кол-во решенных задач</th>
            <th>Рейтинг</th>
        </tr>
    <?php
    $query = "select id, login, rating from users order by rating desc limit 0,3";
    $result = mysqli_query($link, $query);
    $userArr = mysqli_fetch_all($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    $tmptmp = 1;
    foreach ($userArr as $tmpObj){
        $query = "select problem_id from user_result where user_id = ".$tmpObj['id']." and solved = 1";
        $tmpResult = mysqli_query($link,$query);
        $tmpCount = mysqli_num_rows($tmpResult);
        echo "<tr ".(($tmpObj['login'] == $userLogin)?"style='background-color:lightgreen;'":"")."><td>$tmptmp</td><td><a href='user.php?id=".$tmpObj['id']."'>".$tmpObj['login']."</a></td><td>".($tmpCount?$tmpCount:"0")."</td><td>".$tmpObj['rating']."</td></tr>";
        $tmptmp+=1;
    }
    ?>
    </table>
    <br><br>
    <table>
        <tr>
            <th>Место</th>
            <th>Пользователь</th>
            <th>Кол-во решенных задач</th>
            <th>Рейтинг</th>
        </tr>
            <?php
                $query = "select id, login, rating from users order by rating desc limit 4,50";
                $result = mysqli_query($link, $query);
                $userArr = mysqli_fetch_all($result, MYSQLI_ASSOC);
                mysqli_free_result($result);
                $tmptmp = 4;
                foreach ($userArr as $tmpObj){
                    $query = "select problem_id from user_result where user_id = ".$tmpObj['id']." and solved = 1";
                    $tmpResult = mysqli_query($link,$query);
                    $tmpCount = mysqli_num_rows($tmpResult);
                    echo "<tr ".(($tmpObj['login'] == $userLogin)?"style='background-color:lightgreen;'":"")."><td>$tmptmp</td><td><a href='user.php?id=".$tmpObj['id']."'>".$tmpObj['login']."</a></td><td>".($tmpCount?$tmpCount:"0")."</td><td>".$tmpObj['rating']."</td></tr>";
                    $tmptmp++;
                }
            ?>
    </table>
</center>
</body>

</html>
