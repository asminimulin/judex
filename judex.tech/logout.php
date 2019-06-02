<?php
$link = mysqli_connect( "80.93.182.97", "judge", "123456", "judge" );
$cookieFromClient = $_COOKIE["token"];
if ($cookieFromClient){
    $query = "delete from auth where token='$cookieFromClient'";
    mysqli_query($link,$query);
    setcookie("token","",time()-5);
    header("Location:  http://judex.tech/login.php");
} else {
    setcookie("token","", time()-5);
    header("Location:  http://judex.tech/login.php");
}

?>