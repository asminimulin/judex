<?php
include_once "include/functions.php";
$link = connect_to_db();
$cookieFromClient = $_COOKIE["token"];
if ($cookieFromClient){
    $query = "delete from auth where token='$cookieFromClient'";
    mysqli_query($link,$query);
    setcookie("token","",time()-5);
    header("Location:  login.php");
} else {
    setcookie("token","", time()-5);
    header("Location:  /login.php");
}

?>