<?php
include_once "include/global.php";

// TODO:
// Заменить if на switch/case

function getNormalStatus($str){
if ($str == "RE") {
$ans['status'] = "Ошибка выполнения программы";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "CE") {
$ans['status'] = "Ошибка во время компиляции";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "ML") {
$ans['status'] = "Превышение лимита по памяти";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "TL") {
$ans['status'] = "Превышение лимита по времени";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "WA") {
$ans['status'] = "Неправильный ответ";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "OK") {
$ans['status'] = "OK";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "IGN") {
$ans['status'] = "Игнорируется";
$ans["isEnd"] = false;
return $ans;
} else if ($str == "PS"){
$ans["status"] = "Частичное решение";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "LOOSER"){
$ans["status"] = "Неудачник";
$ans["isEnd"] = true;
return $ans;
} else if ($str == "IQ"){
    $ans["status"] = "В очереди";
    $ans["isEnd"] = false;
    return $ans;
} else if ($str == "NLSP"){
    $ans['status'] = "Попытка взлома системы";
    $ans["isEnd"] = false;
    return $ans;
} else {
$ans['status'] = "Проверка: тест " . substr($str, 4, (strlen($str) - 4));
$ans["isEnd"] = false;
return $ans;
}
}

function connect_to_db() {
    global $CONF;
    $new_connection = mysqli_connect($CONF["mysql"]["host"],
                                $CONF["mysql"]["user"],
                                $CONF["mysql"]["password"],
                                $CONF["mysql"]["dbname"]);
    return $new_connection;
}

function getProblemPath($problem_id) {
    global $PROBLEMS;
    return "$PROBLEMS/$problem_id";
}

function getSubmissionPath($submission_id) {
    global $SUBMISSIONS;
    return "$SUBMISSIONS/$submission_id";
}
?>
