<?php
include_once "include/standart.php";
$elemOnPage = 12;
$pageNumber = 1;
if (isset($_GET['p'])){
    $pageNumber  = max(1, (int)($_GET['p']));
}
$query = "select tag_id from problem_tag group by tag_id limit " . (($pageNumber - 1) * $elemOnPage) . ", $elemOnPage";
$result = mysqli_query($link,$query);
$tagIdArr = mysqli_fetch_all($result, MYSQLI_ASSOC);
mysqli_free_result($result);
$query = "select tag_id from problem_tag group by tag_id limit " . (($pageNumber - 1+1) * $elemOnPage) . ", $elemOnPage";
$tmpRow = mysqli_fetch_assoc(mysqli_query($link,$query));
$nextPage = $tmpRow?true:false;
?>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <title>Judge</title>
    <style>
    </style>
</head>

<body background="img/124.png">
<?php include_once "views/navbar.php";?>
<center>
    <h1>Теги</h1>
    <div class="cardList">




        <?php
        foreach ($tagIdArr as $val){
            $tmpJsonText = file_get_contents("../Tags/".$val["tag_id"]."/info.json");
            $tmpObj = json_decode($tmpJsonText, true);
            $query = "select problem_id from problem_tag where tag_id = ".$val['tag_id']." order by problem_id";
            $result = mysqli_query($link,$query);
            $taskIdArr = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
            $cntSolvedTask = 0;
            foreach ($taskIdArr as $elem){
                $query = "select solved from user_result where problem_id = ".$elem['problem_id']." and user_id = $userId";
                $result = mysqli_query($link,$query);
                $row = mysqli_fetch_assoc($result);
                if ($row){
                    if ($row["solved"]) {
                        ++$cntSolvedTask;
                    }
                }
            }

            echo "<div class='card' onclick='location = `archive.php?tag_id=".$val['tag_id']."`'><div class='cardHeader'><span class='cardName'>".$tmpObj['name']."</span></div><p class='tagDescription'>".$tmpObj['description']."</p><div class='cardStatus'>Задачи: $cntSolvedTask/".count($taskIdArr)."</div></div>";
        }



        ?>

    </div>
    <div class="pagingBar">
        <input type="button" class="btn mini blue" <?php if ($pageNumber==1) echo "disabled"?> value="<" onclick="changePage(<?php echo ($pageNumber-1); ?>);"><?php echo " <g>".$pageNumber."</g> ";?><input type="button" class="btn mini blue" value=">" <?php if (!$nextPage) echo "disabled"?> onclick="changePage(<?php echo ($pageNumber+1); ?>);">
    </div>
</center>
<script type="text/javascript">
function changePage(np) {
    location = "tags.php?p="+np;
}
</script>
</body>

</html>
