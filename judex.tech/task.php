<?php
 include_once "standart.php";
if (isset($_GET['id'])){
    $taskId = $_GET['id'];
    if (!file_exists("../Archive/$taskId") || $taskId <= 0){
        header("Location: /404.php");
    } else {
        $jsonText = file_get_contents("../Archive/$taskId/statement.json");
        $mainObj = json_decode($jsonText, true);
    }
} else {
    header("Location: http://judex.tech/404.php");
}
$submissionId = 0;
$userId = getCurrentUserId($link, $_COOKIE["token"]);



function getCurrentUserId($link, $token){
    $query = "select user_id from auth where token = '$token'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row['user_id'];
}


if (isset($_GET['submission'])){
   $submissionId = (int)$_COOKIE["submission_id"];
   setcookie("submission_id", "", time()-5);
}
?>
<html>
<head>
    <link rel = "stylesheet" type = "text/css" href = "style.css">
    <meta charset="utf-8">
    <link href="js/prism/prism.css" rel="stylesheet">
	<title><?php echo $mainObj["name"]; ?></title>

    <style>
    </style>
</head>
<body background="img/124.png">
<?php
include_once "views/navbar.php";
?>
<!--<style>
    .leftBlock{
        background-color: #7fb1bf;
        width: 200px;
        height: 700px;
        display: inline-block;
        position: absolute;
    }
</style>
<div class="leftBlock">kekekeke</div>-->
<center>



    <?php

            echo "<h1>".$mainObj["name"]."</h1>";
   if ($taskId) echo "<p>Task ID: $taskId</p>"
   ?>

    <h2>Задание</h2>
    <?php
        $cond = $mainObj["statement_text"];
        echo "<p class='taskText'>$cond</p>";
        ?>
    <div class="tableContainer">

    </div>
    <?php
        if ($mainObj["comments"]){
            $com = $mainObj["comments"];
            echo "<h2>Комментарии</h2><p class='taskText' >$com</p>";
        }
    ?>


    <h2>Примеры</h2>
    <div class="tableContainer">
    <table border="2" width="55%" cellpadding="0" class="example" >
        <tr>
            <th>Входные данные</th>
            <th>Выходные данные</th>
        </tr>
        <?php

            foreach ($mainObj["examples"] as $val){

                $tmpExample["in"] = file_get_contents("../Archive/$taskId/tests/$val");
                $tmpExample["out"] = file_get_contents("../Archive/$taskId/answers/$val");
                
                $str = "<tr class='exampleDataTr'><td class='exampleDataTd'><pre   style='background-color: white;' ><code class='language-none'><xmp>".$tmpExample["in"]."</xmp></code></pre></td><td class='exampleDataTd'><pre style='background-color: white;'   ><code class='language-none'><xmp>".$tmpExample["out"]."</xmp></code></pre></td></tr>";
                echo $str;
                //$str = "<tr class='exampleDataTr'><td style='padding: 0;'><pre style='background-color: white; margin: 0; padding:1.4em;' class='exampleDataTd' ><code class='language-none'><xmp>".$val["in"]."</xmp></code></pre></td><td style='padding: 0;'><pre style='background-color: white; margin: 0; padding: 1.4em' class='exampleDataTd' ><code class='language-none'><xmp>".$val["out"]."</xmp></code></pre></td></tr>";
            }
        ?>
    </table>
    </div>
	<h2>Загрузка</h2>
    <form name="upload" enctype="multipart/form-data" action="/submit.php" method="POST">
        Выберите язык и файл:
        <select required name="language">
            <option value="C++">C++</option>
            <option value="Python3">Python3</option>
            <!--- <option value="PascalABC">PascalABC</option>
            <option value="Java">Java</option> !-->
        </select>
        <input required type="file" name="uploading_file" onchange="selectLanguage();">
        <input type="hidden" name="problem_id" value="<?php echo $taskId;?>">
        <input type="submit" value="Отправить" name="submit" class="btn green small" >
    </form><br><br>

    <?php
    $query = "select id from submissions where user_id=$userId and problem_id=$taskId limit 1";
    $row = mysqli_fetch_assoc(mysqli_query($link,$query));
    if ($row) {
        $tmpBool = true;
        echo "<h2>Последние 5 посылок</h2>
    <div class ='tableContainer' >
        <table border=\"1\" width=\"100%\" cellpadding=\"5\" id=\"lastSubmission\">
            <tr>
                <th>ID</th>
                <th>Дата</th>
                <th>Язык</th>
                <th>Статус</th>
                <th>Пройдено тестов</th>
                <th>Баллы</th>
                <th>Подробнее</th>
            </tr>";

    }
?>

        <?php
        $query = "select id, time, language from submissions where user_id=$userId and problem_id=$taskId order by time desc limit 5";
        $result = mysqli_query($link, $query);

        $needToUpdateArr;
        while ($row = mysqli_fetch_assoc($result)){
            $jsonText = file_get_contents("../Submissions/".$row["id"]."/result.json");
            $mainObj2 = json_decode($jsonText, true);
            $tmpNormalStatus = getNormalStatus($mainObj2["status"]);
            $needToUpdateArr[$row['id']] = !$tmpNormalStatus["isEnd"];
            echo "<tr id = '".$row['id']."'><td>".$row['id']."</td><td>".$row["time"]."</td><td>".$row['language']."</td><td>".$tmpNormalStatus["status"]."</td><td>".$mainObj2["tests_passed"]."</td><td>".$mainObj2['sum']."</td><td><input type='button' value='Подробнее' class='btn mini cyan' onclick='location=`protocol.php?submission_id=".$row['id']."`'></td></tr>";
        }
        mysqli_free_result($result);
        if ($tmpBool){
            echo "</table>
    </div>
    <p><input type=\"button\" class=\"btn purple small\" value=\"Все посылки\" onclick=\"location = 'submissions.php?task_id=$taskId'\"></p>";
        } else {
            echo "
    <input type=\"button\" class=\"btn purple small\" value=\"Назад\" onclick=\"history.back(); return false;\">
    <br><br>";
        }
        ?>

</center>
<script type="text/javascript">

    function exampleDataFunc(){
        var tds = document.getElementsByClassName("toolbar");
        for (var i = 0; i < tds.length; ++i){
            tds[i].firstChild.remove();
            if (i%2 != 0){
                tds[i].firstChild.remove();
            }
            tds[i].style.opacity = 1;
        }
    }
    setTimeout(exampleDataFunc,100);



    <?php
            if ($needToUpdateArr){
                echo "var obj = ".json_encode($needToUpdateArr).";\n";
            } else {
                echo "var obj = [];\n";
            }

    ?>

    for (var key in obj){
        if (obj[key]){
            document.getElementById(key).style.backgroundColor = "#E6E6FA";
        }
    }

    var objectKeys = Object.keys(obj).reverse();
    var it = 0;
    var stop = 0;

    next();

    function next() {
        ++stop;
        if (stop > 600){
            return;
        }
        if (!check()) return;
        if (it <4){
            ++it;
        } else {
            it = 0;
        }
        if (obj[objectKeys[it-1]]){

            update(objectKeys[it-1]);
        } else {
            next();
        //    setTimeout(next,100);
        }

    }

    function check(){
        for (var key in obj){
            if (obj[key]){
                return true;
            }
        }
        return false;
    }


    function update (id){
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "/updateSubmissionStatus.php", true);
        xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
        xhr.onload = ()=>{
                var mainObj = JSON.parse(xhr.responseText);
                var tr = document.getElementById(""+id);
                if (mainObj["isEnd"]){
                    tr.style.backgroundColor = "white";
                } else {
                    tr.style.backgroundColor = "#E6E6FA";
                }
                var tdArr = tr.getElementsByTagName("td");
                tdArr[3].innerHTML = mainObj["status"];
                tdArr[4].innerHTML = mainObj["tests_passed"];
                tdArr[5].innerHTML = mainObj["sum"];
                if(mainObj['isEnd']){
                    obj[id] = false;
                }
                setTimeout(next,500);
        }
        xhr.onerror = xhr.onabort = ()=>{
               alert("Ошибка при обновлении статуса задачи");
        }
        xhr.send("submission_id="+id);

    }

    function selectLanguage(){
        var select = document.getElementsByName("language")[0];
        var file = document.getElementsByName("uploading_file")[0].files[0];
        if (file) {
            var fileName = document.getElementsByName("uploading_file")[0].files[0]['name'];
            var tmpArr = fileName.split(".");
            var ext = tmpArr[tmpArr.length - 1];
            if (ext === "cpp" || ext == "cxx") {
                select.value = "C++";

            } else if (ext === "py") {
                select.value = "Python3";

            } else if (ext === "pas") {
                select.value = "PascalABC";

            } else if (ext === "java") {
                select.value = "Java";

            }
        }
    }



</script>
<script src="js/prism/prism.js"></script>
<script src="js/btnCopyScript.js"></script>
<script type="text/x-mathjax-config">
    MathJax.Hub.Config({
      tex2jax: {inlineMath: [['$$$','$$$']]}
    });
    </script>
<script type="text/javascript" async src="https://assets.codeforces.com/mathjax/MathJax.js?config=TeX-AMS_HTML-full"></script>
</body>
</html>
