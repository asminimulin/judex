<?php
$link = mysqli_connect( "judex.tech", "judge", "123456", "judge" );
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