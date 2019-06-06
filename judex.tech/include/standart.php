<?php
include_once "functions.php";
include_once "scripts/loadPermissions.php";
$authBool = false;

//******************************************************************
// GLOBAL CONSTANTS

$PERMISSION_ID = array(
    "isAdmin" => 0,
);
$PERMISSIONS = null;

$path_to_judge_root = getenv("JUDEX_HOME");
$PATH_TO_JUDGE_ROOT = $path_to_judge_root;

$link = connect_to_db();
if (mysqli_connect_errno()){
    $strErr = "[".date("Y-m-d H:i:s")."] ERROR : Can't connect to DB on page ".$_SERVER['REQUEST_URI']."\n";
    $file = fopen("../logs/main.log","a");
    fwrite($file,$strErr);
    fclose($file);
    header("Location: /login.php");
}

//*****************************************************************
if ($_COOKIE["token"]){
    $tokenFromClient = $_COOKIE["token"];
    $query = "select user_id, date from auth where token ='".$tokenFromClient."'";
    $result = mysqli_query($link,$query);
    $row = mysqli_fetch_row($result);
    mysqli_free_result($result);
    if ($row){
        $lastDate = strtotime($row[1]);
        $nowDate = strtotime(date("Y-m-d H:i:s"));
        $timeDif= ($nowDate-$lastDate)/60;
        if ($timeDif > 120) {
            setcookie("logoutFrom", "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
          header("Location: logout.php");
        } else {
            $query = "update auth set date='".date("Y-m-d H:i:s")."' where user_id=".$row[0];
            mysqli_query($link,$query);
            $authBool = true;
            $query = "select id,login from users where id=$row[0]";
            $result = mysqli_query($link,$query);
            $row = mysqli_fetch_row($result);
            mysqli_free_result($result);
            $userId = $row[0];
            $userLogin = $row[1];
            loadPermissions();
        }
    } else {
        setcookie("token", "", time()-5);
        header("Location: login.php");
    }
} else {
    header("Location: login.php");
}




?>
