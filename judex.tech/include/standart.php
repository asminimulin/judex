<?php
include_once "include/global.php";
include_once "include/functions.php";
include_once "scripts/loadPermissions.php";
$authBool = false;

$link = connect_to_db();
if (!$link) {
    echo "<p>".mysqli_connect_error()."</p>";
    echo "<p>Stadart.php caught DB Error</p>";
    exit(1);
}

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
            setcookie("token", "" , time()-5);
            header("Location: /login.php");
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
        setcookie("logoutFrom", "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        setcookie("token", "", time()-5);
        header("Location: login.php");
    }
} else {
    setcookie("logoutFrom", "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    header("Location: login.php");
}




?>
