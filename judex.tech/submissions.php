<?php
include_once "include/standart.php";
include_once "include/functions.php";
$elemOnPage = 20;
$certainTask = false;
if (isset($_GET['task_id'])){
    $taskId = $_GET['task_id'];
    $taskName;

$query = "select name from problems where id = ".$taskId;
$result = mysqli_query($link, $query);
$row = mysqli_fetch_row($result);
mysqli_free_result($result);
if (!$row){
    header("Location: 404.php");
} else {
    $certainTask = true;
    $taskName = $row[0];
    $query = "select id from submissions where user_id = $userId and problem_id=$taskId";
    $result = mysqli_query($link,$query);
    $row2 = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    if (!$row2){
        header("Location: submissions.php");
    }
}
}
$pageNumber = 1;
if (isset($_GET['p'])){
    $pageNumber  = max(1, (int)($_GET['p']));
}
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <title>Submissions</title>
</head>

<body background="img/124.png">
<?php include_once "views/navbar.php";?>
<center>
    <h1>
        Все посылки
    </h1>
    <h2>Задача</h2>
    <select id = "selectTask" onchange="changeTask();">
        <option <?php if (!$certainTask) {
            echo "selected";
        } ?> value="0">Любая</option>
        <?php
            $query = "select problem_id from submissions where user_id = $userId group by problem_id order by problem_id";
            $result = mysqli_query($link,$query);
            while ($row = mysqli_fetch_assoc($result)){
                $jsonText = file_get_contents(getProblemPath($row['problem_id'])."/statement.json");
                $tmpObj = json_decode($jsonText, true);
                $selectedWord="";
                if ($certainTask && $taskId == $row['problem_id']){
                    $selectedWord = 'selected';
                }
                echo "<option $selectedWord value='".$row['problem_id']."'>".$tmpObj['name']."</option>";
            }
        ?>
    </select>
    <br><br>


    <div class="tableContainer">
    <table border="1" width="90%" cellpadding="5">
        <tr>
            <th>ID</th>
            <?php if (!$certainTask){
                echo "<th>Задача</th>";
            } ?>
            <th>Дата</th>
            <th>Язык</th>
            <th>Статус</th>
            <th>Пройдено тестов</th>
            <th>Баллы</th>
            <th>Подробнее</th>
        </tr>
        <?php
	$nextPage = false;
        if ($certainTask){
            $query = "select id, time, language from submissions where user_id=$userId and problem_id=$taskId order by time desc limit ".(($pageNumber-1)*$elemOnPage).",$elemOnPage";
            $result = mysqli_query($link, $query);
            while ($row = mysqli_fetch_assoc($result)){
                $jsonText = file_get_contents("../Submissions/".$row["id"]."/result.json");
                $mainObj = json_decode($jsonText, true);

                echo "<tr><td>".$row['id']."</td><td>".$row["time"]."</td><td>".$row['language']."</td><td>".getNormalStatus($mainObj["status"])['status']."</td><td>".$mainObj["tests_passed"]."</td><td>".$mainObj['sum']."</td><td><input type='button' value='Подробнее' class='btn cyan mini' onclick='location=`protocol.php?submission_id=".$row['id']."`'></td></tr>";
            }
            mysqli_free_result($result);
            $query = "select id submissions where user_id=$userId and problem_id=$taskId order by time desc limit ".(($pageNumber-1+1)*$elemOnPage).",$elemOnPage";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row){
                $nextPage = true;
            }
        } else {
            $query = "select id, problem_id, time, language from submissions where user_id=$userId order by id desc limit ".(($pageNumber-1)*$elemOnPage).",$elemOnPage";
            $result = mysqli_query($link, $query);
            while ($row = mysqli_fetch_assoc($result)){
                $jsonText = file_get_contents("../Submissions/".$row["id"]."/result.json");
                $mainObj = json_decode($jsonText, true);
                $jsonText = file_get_contents("../Archive/".$row['problem_id']."/statement.json");
                $tmpObj = json_decode($jsonText, true);
                echo "<tr><td>".$row['id']."</td><td><a style='display: block;' href='/task.php?id=".$row['problem_id']."'>".$tmpObj['name']."</a></td><td>".$row["time"]."</td><td>".$row['language']."</td><td>".getNormalStatus($mainObj["status"])['status']."</td><td>".$mainObj["tests_passed"]."</td><td>".$mainObj['sum']."</td><td><input type='button' value='Подробнее' class='btn cyan mini' onclick='location=`protocol.php?submission_id=".$row['id']."`'></td></tr>";

            }
		 mysqli_free_result($result);
            $query = "select id from submissions where user_id=$userId order by id desc limit ".(($pageNumber-1+1)*$elemOnPage).",$elemOnPage";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row){
                $nextPage = true;
            }
        }
        

        ?>
    </table>
    </div>
    <br><br>
    <div class="pagingBar">
        <input type="button" class="btn mini blue" <?php if ($pageNumber==1) echo "disabled"?> value="<" onclick="changePage(<?php echo ($pageNumber-1); ?>);"><?php echo " <g>".$pageNumber."</g> ";?><input type="button" class="btn mini blue" value=">" <?php if (!$nextPage) echo "disabled"?> onclick="changePage(<?php echo ($pageNumber+1); ?>);">
    </div>
    <br>
    <br>
    <input type="button" class="btn purple small" value="Назад" onclick="history.back(); return false;">
    <br>
</center>
<script type="text/javascript" >
    function changeTask(){
        var taskId = document.getElementById("selectTask").value;
        if (taskId == 0){
            location = "/submissions.php";
        } else {
            location = "/submissions.php?task_id="+taskId;
        }
    }
    function changePage(np) {
        if(<?php echo ($certainTask)?1:0;?>){
            location = "/submissions.php?task_id=<?php echo $taskId?>&p="+np;
        } else {
            location = "/submissions.php?p="+np;
        }
    }
</script>
</body>

</html>
