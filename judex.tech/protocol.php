<?php
include_once "include/standart.php";
include_once "icnlude/functions.php";

if (isset($_GET["submission_id"])){
    $submissionId = $_GET["submission_id"];
    if ($PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1") {
        $query = "select problem_id,time,language from submissions where id=$submissionId";
    } else {
        $query = "select problem_id,time,language from submissions where id=$submissionId and user_id = $userId";
    }
    $result = mysqli_query($link, $query);
    if($row = mysqli_fetch_assoc($result)){
        mysqli_free_result($result);
        $language = $row["language"];
        $jsonText = file_get_contents(getSubmissionPath($submissionId) . "/result.json");
        $mainObj = json_decode($jsonText, true);
        $taskId = $row['problem_id'];
        $taskName = mysqli_fetch_assoc(mysqli_query($link, "select name from problems where id=$taskId"))['name'];
    } else {
        header("Location: 404.php");
    }
} else {
    header("Location: 404.php");
}
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <link href="js/prism/prism.css" rel="stylesheet">
    <title>Protocol</title>
    <style>
        .overlay {
            transition: 0.5s;
            background: #000;
            position: fixed;
            left: 0;
            right: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            opacity: .5;
        }
        .visible {
            transition: 0.5s;
            background: rgba(255, 255, 255, 0.75);
            position: fixed;
            left: 50%;
            top: 50%;
            border-radius: 10px;
            margin-top: -200px;
            overflow: hidden;
            z-index: 2000;
            width: 800px;
            padding: 0px;
            margin-left: -400px;
        }
        .content {
            transition: 0.5s;
            padding: 0 1em;
            border-top: 1px solid #ccc;
            border-bottom: 1px solid #ccc;
            background: WhiteSmoke;
            word-break: break-word;
        }
        .winTestViewHeader{
            font: normal 1.1em "Trebuchet MS";
        }
    </style>
</head>

<body background="img/124.png">
<?php include_once "views/navbar.php";?>
<center>

    <h2>Протокол посылки</h2>
    <div class="tableContainer">
    <table border="1" width="70%" cellpadding="4">
        <tr>
            <th>ID</th>
            <th>Пользователь</th>
            <th>Задача</th>
            <th>Язык</th>
            <th>Статус</th>
            <th>Пройдено тестов</th>
            <th>Баллы</th>
            <th>Максимальное время, с</th>
            <th>Среднее время, с</th>
            <th>Максимальная память, Кб</th>
            <th>Средняя память, Кб</th>
        </tr>
        <?php
        echo "<tr><td>$submissionId</td><td>$userLogin</td><td><a href='task.php?id=$taskId'>$taskName</a></td><td>".$row["language"]."</td><td>".getNormalStatus($mainObj['status'])['status']."</td><td>".$mainObj['tests_passed']."</td><td>".$mainObj['sum']."</td><td>".$mainObj["max_time"]."</td><td>".$mainObj['average_time']."</td><td>".$mainObj["max_memory"]."</td><td>".$mainObj['average_memory']."</td></tr>";
        ?>
    </table>
    </div>
    <br><br>
    <div class = 'tableContainer'>
    <table border="1" width="90%" cellpadding="5" id="lastSubmission">
        <tr>
            <th>Тест #</th>
            <th>Вердикт</th>
            <th>Время, с</th>
            <th>Память, Кб</th>
        </tr>
        <?php
            foreach ($mainObj['tests'] as $key => $value){
                $tmpArr = explode(";",$value);
                echo "<tr";
                echo ($PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1")?" ondblclick = 'getTestResult(".$submissionId.", ".$key.")' ":"";
                echo "><td>".($key+1)."</td><td>".getNormalStatus($tmpArr[0])['status']."</td><td>".$tmpArr[1]."</td><td>".$tmpArr[2]."</td></tr>";
            }
        ?>
    </table>
    </div>
    <br>
    <br>

    <div class="line-numbers codeView">
        <?php
        if (file_exists(getSubmissionPath($submissionId))) {
            $codePath = getSubmissionPath($submissionId)."$submissionId".$LANG_CONF[$language]["extension"];
            $code = file_get_contents($codePath);
            echo "<pre><code id='userCode' class='language-$language'><xmp>".$code."</xmp></code></pre>";

        }
        ?>
    </div>

    <br>
    <br>
    <input type="button" class="btn purple small" value="Назад" onclick="history.back(); return false;">
    <br>
</center>
<div id="winTestResult" style="display:none;">
    <div class="overlay" onclick="getElementById('winTestResult').style.display='none';"></div>
    <div class="visible">
        <span class='winTestViewHeader'></span>
        <div class="content">
            <br><br>
           <table style="table-layout: fixed;">
               <tr>
                   <th>Входные данные</th>
                   <th>Правильный вывод</th>
                   <th>Вывод программы</th>
               </tr>
               <tr>

               </tr>
           </table>
            <br><center>
                <form method="post" action="views/testFullView.php" enctype="application/x-www-form-urlencoded">
                    <input type="submit" class="btn cyan small" value="Подробнее">
                    <input type="hidden" id="form_sub_id" name="submission_id" value="">
                    <input type="hidden" id="form_test_num" name="test_number" value="">
                </form>
            </center><br>
        </div>
        <input type="button" class="btn purple mini" value="Закрыть" style="margin:5px 5px 8px 0px; float:right;" onClick="getElementById('winTestResult').style.display='none';">
    </div>
</div>
<script src="js/prism/prism.js"></script>
<script src="js/btnCopyScript.js"></script>
<script type="text/javascript">
    function codeViewBtn(){
        var tb = document.getElementsByClassName("toolbar")[0];
        tb.style.opacity=1;
    }
    setTimeout(codeViewBtn,100);
</script>
<?php
if ($PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1"){
    echo "<script src='js/getTestResultFunction.js'></script>";
}
?>
</body>

</html>
