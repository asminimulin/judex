<?php

// TODO:
// Create js script to doing such things

include_once "../include/global.php";


$link = mysqli_connect($CONF["database"]["host"],
                        $CONF["database"]["user"],
                        $CONF["database"]["password"],
                        $CONF["database"]["dbname"]);
if (!$link) {
    echo mysqli_error($link);
    exit(1);
}

function upload($submission_id, $language) {
    global $CONF;
    $submission_dir = $CONF["global"]["submissions"] . '/' . "$submission_id";
    mkdir($submission_dir, 0770, TRUE);	

    $output_dir = "$submission_dir/output";
    mkdir($output_dir, 0770, TRUE);

    $result_path = "$submission_dir/result.json";
    $submission_result = array();
    $submission_result["status"] = "IQ";
    $fp = fopen($result_path, 'w');
    fwrite($fp, "{\"status\": \"IQ\"}");
    fclose($fp);
    chmod($path_to_dir, 0777);
    chmod($path_to_file, 0777);
	
    $lang_conf = parse_ini_file("/etc/judex/language.ini", true);
    $filename = "$submission_dir/$submission_id".$lang_conf[$language]["extension"];
    $uploaded = move_uploaded_file($_FILES['uploading_file']['tmp_name'], $filename);

    chmod($filename, 0777);
    return $uploaded;
}

function getCurrentUserId($link, $token){
    $query = "select user_id from auth where token = '$token'";
    $result = mysqli_query($link, $query);
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    return $row['user_id'];
}


function submit() {
    global $link;
    if(!isset($_POST)) {
        die("Nothing in POST query");
    }

    $user_id = getCurrentUserId($link, $_COOKIE["token"]);

    $problem_id = $_POST["problem_id"];
    $language = $_POST["language"];

    $sql = "INSERT INTO submissions (problem_id, user_id, time, language) VALUES ($problem_id, $user_id, now(), '$language');";
    $inserted = mysqli_query($link, $sql);
    if(!$inserted) {
        echo "sql = $sql";
    	die("Error while inserting into submissions");
    }

    $sql = "SELECT LAST_INSERT_ID()";
    $submission_id = mysqli_fetch_array(mysqli_query($link, $sql))[0];
    if(!$submission_id) {
    	die("Error while getting submission_id");
    }

    if(!upload($submission_id, $language)) {
        die("Error while uploading file");
    }

    $sql = "INSERT INTO testing_queue (id, problem_id, language) VALUES ($submission_id, $problem_id, '$language')";
    $inserted = mysqli_query($link, $sql);
    if(!$inserted) {
        die(mysqli_error($link));
    }

    setcookie("submission_id", $submission_id, time() + 100);
    header("Location: /task.php?id=$problem_id");
}

submit();

?>
