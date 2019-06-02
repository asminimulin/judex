<?php
    include getenv("JUDGE_ROOT")."/standart.judge.com/standart.php";
    if(!$link) {
        header("Location: /404.php");
    }
?>

<?php
    function saveTag($fullName, $shortName, $description, $id = 1000) {
        global $PATH_TO_JUDGE_ROOT;
        $path = "$PATH_TO_JUDGE_ROOT/Tags/$id";
        if(file_exists($path)) {
            echo "<script>alert(\"Tag with such id already exists\")</script>";
            return;
        }
        mkdir($path, 0777, TRUE);
        file_put_contents("$path/info.json", json_encode(array(
                                            "shortname" => $shortName,
                                            "name" => $fullName,
                                            "description" => $description), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        chmod("$path/info.json", 0777);
    }

    function checkIfTagExists($shortname) {
        global $link;
        $sql = "SELECT * FROM tags WHERE shortname=\"$shortname\"";
        $response = mysqli_query($link, $sql);
        if($response) {
            if($response->num_rows >= 1) {
                mysqli_free_result($response);
                return true;
            }
            mysqli_free_result($response);
            return false;
        } else {
            header("Location: /404.php");
        }
    }

    function getNewTagId($shortname) {
        global $link;
        $sql = "INSERT INTO tags(shortname) values(\"$shortname\")";
        mysqli_query($link, $sql);
        $sql = "SELECT LAST_INSERT_ID()";
        $response = mysqli_fetch_array(mysqli_query($link, $sql))[0];
        return $response;
    }

    if(isset($_POST["saveTag"])) {
        if(!checkIfTagExists($_POST["shortName"])) {
            $id = getNewTagId($_POST["shortName"]);
            saveTag($_POST["fullName"], $_POST["shortName"], $_POST["description"], $id);
        } else {
            echo "<script>alert(\"Tag with such shortame already exists\");</script>";
        }
    }
?>

<html>
    <head>
        <title>Редактировать</title>
    </head>
    <link rel="stylesheet" type="text/css" href="/style.css">
    <body background="/img/124.png">
        <?php include "$PATH_TO_JUDGE_ROOT/standart.judge.com/views/navbar.php"; ?>
        <center> 
            <H1>Создать ТЭГ</H1>
            <form id="form_create_tag" action="/need-to-fix/create.php" method="post"> 
                <input type="text" placeholder="Название тэга" name="fullName">
                <input type="text" placeholder="Краткое название" name="shortName">
                <input type="text" placeholder="Описание" name="description">
                <input name="saveTag" class="btn green small" type="submit" value="Сохранить">
            </form> 
        </center> 
    </body> 
</html>

<style>
    #form_create_tag input:not(.btn) {
        font-size: 1.5em;
    }
</style>
