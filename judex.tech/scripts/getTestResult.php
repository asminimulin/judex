<?php
include_once "include/standart.php";
if (isset($_POST['submission_id']) && isset($_POST['test_number']) && $PERMISSIONS[$PERMISSION_ID["isAdmin"]] == "1"){
    $submissionId = $_POST['submission_id'];
    $test_num = $_POST['test_number'];
    $query = "select problem_id from submissions where id = $sub_id";
    $result = mysqli_query($link, $query);
    $taskId= mysqli_fetch_assoc($result)['problem_id'];
    mysqli_free_result($result);
    $ansObj = [];
    problemPath = getProblemPath($taskId);
    submissionPath = getSubmissionPath($submissionId);
    if (file_exists("$problemPath/tests/$test_num")) {
        $ansObj['in'] = cutUp(file_get_contents("$problemPath/tests/$test_num"));
    }
    if (file_exists("$problem_path/answers/$test_num")) {
        $ansObj['right'] = cutUp(file_get_contents("$problemPath/answers/$test_num"));
    }
    if (file_exists("$submissionPath/output/$test_num")) {
        $ansObj['real'] = cutUp(file_get_contents("$submissionPath/output/$test_num"));
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
