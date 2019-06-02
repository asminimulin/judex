<?php
include "functions.php";
if (isset($_POST['submission_id'])){
    $submissionId = $_POST['submission_id'];
    if (file_exists("../Submissions/$submissionId")){
        $jsonText = file_get_contents("../Submissions/$submissionId/result.json");
        $mainObj = json_decode($jsonText, true);
        $newObj["tests_passed"] = $mainObj['tests_passed'];
        $tmpObj = getNormalStatus($mainObj['status']);
        $newObj["status"] = $tmpObj['status'];
        $newObj['isEnd'] = $tmpObj["isEnd"];
        $newObj["sum"] = $mainObj['sum'];
        $jsonResult = json_encode($newObj, JSON_UNESCAPED_UNICODE);
        echo $jsonResult;
    }
}
?>