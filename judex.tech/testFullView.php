<?php

include "standart.php";
if (isset($_POST['submission_id']) && isset($_POST['test_number']) && $PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1"){
    $sub_id = $_POST['submission_id'];
    $test_num = $_POST['test_number'];
    $query = "select problem_id from submissions where id = $sub_id";
    $result = mysqli_query($link, $query);
    $taskId= mysqli_fetch_assoc($result)['problem_id'];
    mysqli_free_result($result);

    if (file_exists("../Archive/$taskId/tests/$test_num")) {
        echo "<h2>Входные данные</h2>";
        echo "<xmp>".file_get_contents("../Archive/$taskId/tests/$test_num")."</xmp>";
    }
    if (file_exists("../Archive/$taskId/answers/$test_num")) {
        echo "<h2>Правильный вывод</h2>";
        echo "<xmp>".file_get_contents("../Archive/$taskId/answers/$test_num")."</xmp>";
    }
    if (file_exists("../Submissions/$sub_id/output/$test_num")) {
        echo "<h2>Вывод программы</h2>";
        echo "<xmp>".file_get_contents("../Submissions/$sub_id/output/$test_num")."</xmp>";
    }
}

?>