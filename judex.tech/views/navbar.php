<meta name="viewport" content="initial-scale = 1.0, maximum-scale = 1.0, user-scalable = no, width = device-width" />
<meta charset="UTF-8">
<ul class="topnav">
    <li><a class="active" href="/">Главная</a></li>
    <li><a href="/archive.php">Архив</a></li>
    <li><a href="/submissions.php">Посылки</a></li>
    <li><a href="/tags.php">Теги</a></li>
    <!---<li><a href="/rating.php">Рейтинг</a></li>-->
    <?php
        if($PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1") {
            echo "<li><a href=\"/admin.php\">Админ</a></li>\n";
            echo "<li><a href=\"/upload.php\">Добавить задачу</a></li>\n";
            #echo "<li><a href=\"/createContest.php\">Создать контест</a></li>\n";
            echo "<li><a href=\"/edit.php\">Редактировать</a></li>\n";
        }
    ?>
    <?php
        echo "<li class='right'><a href='profile.php'>$userLogin</a></li>";
    ?>
</ul>
