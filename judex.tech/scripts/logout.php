<?php
// TODO:
// refactor to make includable

include_once "global.php";
$link = mysqli_connect($CONF["mysql"]["host"],
                        $CONF["mysql"]["user"],
                        $CONF["mysql"]["password"],
                        $CONF["mysql"]["dbname"]);

$cookieFromClient = $_COOKIE["token"];
if ($cookieFromClient){
    $query = "delete from auth where token='$cookieFromClient'";
    mysqli_query($link,$query);
    setcookie("token","",time()-5);
    header("Location:  /login.php");
} else {
    setcookie("token","", time()-5);
    header("Location:  /login.php");
}

?>
