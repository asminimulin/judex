<?php
include "standart.php";
if (isset($_POST['submission_id']) && isset($_POST['test_number']) && $PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1"){
    $sub_id = $_POST['submission_id'];
    $test_num = $_POST['test_number'];
    $query = "select problem_id from submissions where id = $sub_id";
    $result = mysqli_query($link, $query);
    $taskId= mysqli_fetch_assoc($result)['problem_id'];
    mysqli_free_result($result);
    $ansObj = [];
    if (file_exists("../Archive/$taskId/tests/$test_num")) {
        $ansObj['in'] = cutUp(file_get_contents("../Archive/$taskId/tests/$test_num"));
    }
    if (file_exists("../Archive/$taskId/answers/$test_num")) {
        $ansObj['right'] = cutUp(file_get_contents("../Archive/$taskId/answers/$test_num"));
    }
    if (file_exists("../Submissions/$sub_id/output/$test_num")) {
        $ansObj['real'] = cutUp(file_get_contents("../Submissions/$sub_id/output/$test_num"));
    }
    echo json_encode($ansObj,JSON_PRETTY_PRINT);
}

function cutUp($txt){
    if (strlen($txt) > 50){
        $txt = substr($txt,0,50)."...";
    }
    return $txt;
}


?>