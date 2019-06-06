<?php
    include_once "include/standart.php";
    $elemOnPage = 10;
    $pageNumber = 1;
    $nextPage = false;
    $tagId = 1;
    $tagName ="";
    $certainTag = false;
if (isset($_GET['p'])){
    $pageNumber  = max(1, (int)($_GET['p']));
}

if (isset($_GET['tag_id'])){
    $tagId = $_GET['tag_id'];
    $certainTag = true;
    $query = "select problem_id from problem_tag where tag_id = '$tagId' order by problem_id limit ". (($pageNumber - 1) * $elemOnPage) . ", $elemOnPage";
    $result = mysqli_query($link,$query);
    $problemsArr = mysqli_fetch_all($result,MYSQLI_ASSOC);
    mysqli_free_result($result);
    if (!$problemsArr){
        header("Location: archive.php");
    }
    $query = "select problem_id from problem_tag where tag_id= '$tagId' order by problem_id limit ". (($pageNumber - 1+1) * $elemOnPage) . ", $elemOnPage";
    $result = mysqli_query($link,$query);
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    if ($row){
        $nextPage = true;
    }
    $jsonText = file_get_contents("../Tags/$tagId/info.json");
    $tagObj = json_decode($jsonText, true);
    $tagName = $tagObj['name'];

}
?>

<html>
<head>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
    </style>
</head>
<body background="img/124.png">
<?php
include_once "views/navbar.php";
?>
<center>
	<h1><?php if($certainTag) {
	   echo $tagName;
	} else {
	    echo "Архив задач";
        }?></h1>

	<!-- Php get it from mysql -->
    <div class="cardList">
        <?php
        if($certainTag) {
        foreach($problemsArr as $val){
            $tmp_problem_id = $val['problem_id'];
            $tmpJsonText = file_get_contents("../Archive/$tmp_problem_id/statement.json");
            $tmpMainObj = json_decode($tmpJsonText, true);
            $query = "select name from problems where id = $tmp_problem_id";
            $result = mysqli_query($link,$query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $query = "select solved from user_result where user_id = $userId and problem_id = $tmp_problem_id";
            $result = mysqli_query($link,$query);
            $row2 = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            $tmp_solved = 0;
            if ($row2){
              if ($row2["solved"]) {
                  $tmp_solved = 1;
              }
            }
            echo "<div class='card' ><div onclick='location = `task.php?id=".$val['problem_id']."`' class='cardHeader'><span class='cardName'>".$row['name']."</span></div><span class='cardTaskId'>ID: ".$tmp_problem_id."</span><div class='cardLimits'>T: ".$tmpMainObj['timeLimit']." с<br>M: ".$tmpMainObj['memoryLimit']." Мб</div><p class='tagList'>";
            $query = "select tag_id from problem_tag where problem_id = $tmp_problem_id";
            $result = mysqli_query($link, $query);
            while ($row3 = mysqli_fetch_assoc($result)){
                $tmp2JsonText = file_get_contents("../Tags/".$row3['tag_id']."/info.json");
                $tmp2Obj = json_decode($tmp2JsonText, true);
                echo "<span class='tagInList'>".$tmp2Obj['shortname']."</span>";
            }
            mysqli_free_result($result);
            echo "</p><div class='cardStatus' style='color:forestgreen'>".($tmp_solved?"OK":"")."</div></div>";
        }
            unset($val);
        } else {
            $query = "select id, name from problems order by id limit " . (($pageNumber - 1) * $elemOnPage) . ", $elemOnPage";
            $result = mysqli_query($link,$query);
            $taskList  = mysqli_fetch_all($result, MYSQLI_ASSOC);
            mysqli_free_result($result);
            $query = "select * from problems order by id limit " . (($pageNumber - 1 + 1) * $elemOnPage) . ",$elemOnPage";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row) {
                $nextPage = true;
            }
            foreach ($taskList as $val){
                $tmpJsonText = file_get_contents("../Archive/".$val['id']."/statement.json");
                $tmpMainObj = json_decode($tmpJsonText, true);
                $cond = $tmpMainObj["conditions"];
                $query = "select solved from user_result where user_id = $userId and problem_id = ".$val['id'];
                $result = mysqli_query($link,$query);
                $row2 = mysqli_fetch_assoc($result);
                mysqli_free_result($result);
                $tmp_solved = 0;
                if ($row2){
                    if ($row2["solved"]) {
                        $tmp_solved = 1;
                    }
                }
                echo "<div class='card'><div onclick='location=`task.php?id=".$val['id']."`' class='cardHeader'><span class='cardName'>".$val['name']."</a></div><span class='cardTaskId'>ID: ".$val['id']."</span><div class='cardLimits'>T: ".$tmpMainObj['timeLimit']." с<br>M: ".$tmpMainObj['memoryLimit']." Мб</div><p class='tagList'>";
                $query = "select tag_id from problem_tag where problem_id = ".$val['id'];
                $result = mysqli_query($link, $query);
                while ($row3 = mysqli_fetch_assoc($result)){
                    $tmp2JsonText = file_get_contents("../Tags/".$row3['tag_id']."/info.json");
                    $tmp2Obj = json_decode($tmp2JsonText, true);
                    echo "<span class='tagInList'>".$tmp2Obj['shortname']."</span>";
                }
                echo "</p><div class='cardStatus' style='color:forestgreen'>".($tmp_solved?"OK":"")."</div></div>";
            }
        }
        ?>
    </div>
    <div class="pagingBar">
        <input type="button" class="btn mini blue" <?php if ($pageNumber==1) echo "disabled"?> value="<" onclick="changePage(<?php echo ($pageNumber-1); ?>);"><?php echo " <g>".$pageNumber."</g> ";?><input type="button" class="btn mini blue" value=">" <?php if (!$nextPage) echo "disabled"?> onclick="changePage(<?php echo ($pageNumber+1); ?>);">
    </div>
</center>
<script type="text/javascript" src="js/dev.js"></script>
<script type="text/javascript">
    function changePage(np) {
        if(<?php echo ($certainTag)?1:0;?>){
            location = "/archive.php?tag=<?php echo $tagName?>&p="+np;
        } else {
            location = "/archive.php?p="+np;
        }
    }
</script>
</body>
</html>
