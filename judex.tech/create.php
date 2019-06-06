<?php include getenv("JUDGE_ROOT")."/standart.judge.com/standart.php"; ?>

<?php
    function saveTag($fullName, $shortName, $description, $id = 1000) {
        $path = "$PATH_TO_JUDGE_ROOT/Tags/$id";
        mkdir($path, 0777, TRUE);
        file_put_contents("$path/info.json", json_encode(array(
                                            "shortname" => $shortName,
                                            "name" => $fullName,
                                            "description" => $description), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    if(isset($_POST["saveTag"])) {
        saveTag($_POST["fullName"], $_POST["shortName"], $_POST["description"]);
    }
?>

<html>
    <link rel="stylesheet" type="text/css" href="/style.css">
    <body background="/img/124.png">
        <?php include "$PATH_TO_JUDGE_ROOT/standart.judge.com/views/navbar.php"; ?>
        <center> 
            <H1>Create Tag</H1>
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
    #form_create_tag input {
        font-size: 1.5em;
    }
</style>