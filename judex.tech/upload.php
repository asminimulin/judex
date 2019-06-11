<?php
include "include/standart.php";

function removeDir($dir) {
    if ($objs = glob($dir."/*")) {
       foreach($objs as $obj) {
         is_dir($obj) ? removeDirectory($obj) : unlink($obj);
       }
    }
    rmdir($dir);
}

$name = "";
$condition = "";
$groups = array();
$comment = "";

if (isset($_POST['submit'])) {
    $name = $_POST['name'];
    $timeLim = $_POST['timeLim'];
    $memoryLim = $_POST['memoryLim'];
    $condition = $_POST['condition'];
    $comment = $_POST['comments'];
    $input = $_POST['input'];
    $output = $_POST['output'];
    $groupsAmount = $_POST['numOfGroups'];
    if (!is_int($groupsAmount) || $groups_amount <= 0) {
        echo '<script>alert("Введенное число погрупп неккоректно.")</script>';
        exit();
    }
    $tmpAdress = "/tmp/".rand();
    while (!mkdir($tmpAdress, 0777, TRUE)) {
        $tmpAdress = "/tmp/".rand();
    }
    move_uploaded_file($_FILES['checker']['tmp_name'], $tmpAdress."checker.cpp");
    system('g++ '.$tmpAdress.'checker.cpp -o '.$tmpAdress.'checker');
    if (!file_exists($tmpAdress.'checker.cpp')) {
        echo '<script>alert("Чекер неверен.")</script>';
        removeDir($tmpAdress);
        exit();
    }
    move_uploaded_file($_FILES['program']['tmp_name'], $tmpAdress."program.cpp");
    system('g++ '.$tmpAdress.'program.cpp -o '.$tmpAdress.'program');
    if (!file_exists($tmpAdress.'program')) {
        echo '<script>alert("Авторское решение неверно.")</script>';
        removeDir($tmpAdress);
        exit();
    }
    mkdir($tmpAdress."tests", 0777, TRUE);
    mkdir($tmpAdress."answers", 0777, TRUE);

    $inp_names = 0;
    $arr = array();

    $pipes = array();
    $descriptors = array();
    $inputAdress = $tmpAdress."input.txt";
    $outputAdress = $tmpAdress."output.txt";
            
    if ($input == "stdin") {
        if ($output == "stdout") {
            $descriptors = array(
                0 => array("file", $tmpAdress."input.txt", "r"),
                1 => array("file", $tmpAdress."output.txt","w"));
        }
        else {
            $descriptors = array(
                0 => array("file", $tmpAdress."input.txt", "r"),
                1 => array("pipe","w"));
            $outputAdress = $output;
        }
    }
    else {
        if ($output == "stdout") {
            $descriptors = array(
                0 => array("pipe", "r"),
                1 => array("file", $tmpAdress."output.txt","w"));
            $inputAdress = $input;
        }
        else {
            $descriptors = array(
                0 => array("pipe", "r"),
                1 => array("pipe", "w"));
            $inputAdress = $input;
            $outputAdress = $output;
        }
    }
    foreach ($_FILES['inputSamples']['tmp_name'] as $tmp) {
        move_uploaded_file($tmp, $tmpAdress+$inputAdress);
        $process = proc_open('.'.$tmpAdress.'program', $descriptors, $pipes);
        usleep($timeLim*1000);
        $stat = proc_get_status(process);
        if ($stat['running'] == TRUE) {
            proc_terminate($process);
            echo '<script>alert("Проблема с претестами или с авторским решением.")</script>';
            removeDir($tmpAdress);
            exit();
        }
        proc_close($process);
        if ($stat['exitcode'] != 0) {
            echo '<script>alert("Проблема с претестами или с авторским решением.")</script>';
            removeDir($tmpAdress);
            exit();
        }
        $str1 = file_get_contents($tmpAdress+$inputAdress);
        $str2 = file_get_contents($tmpAdress+$outputAdress);
        unlink($tmpAdress+$inputAdress);
        unlink($tmpAdress+$outputAdress);
        $arr[$inp_names] = array('in' => $str1, 'out' => $str2);
        $inp_names+=1;
    }
    $requirments =array();
    $inp_names = 1;

    mkdir($tmpAdress."tests");
    mkdir($tmpAdress."tests");
    
    for ($i = 1; $i <= $groupsAmount; $i++) {
        if (count($_FILES['group'.$i.'Tests']) != $_POST['group'.$i.'TestsNum']) {
            echo '<script>alert("Количество загруженных тестов в группе '.$i.' не соответствует указанному.")</script>';
            removeDir($tmpAdress);
            exit();
        }
        $requirments[$inp_names-1] = array("required" => $_POST['group'.$i.'Select'],
                                           "tests_count" => $_POST['group'.$i.'TestsNum'],
                                           "cost" => $_POST['group'.$i.'Cost'],
                                           "assesment" => $_POST['assesment'.$i]);
        foreach ($_FILES['group'+$i+'Tests']['tmp_name'] as $test) {
            move_uploaded_file($test, $tmpAdress+$inputAdress);
            $process = proc_open('.'.$tmpAdress.'program', $descriptors, $pipes);
            usleep($timeLim*1000);
            $stat = proc_get_status(process);
            if ($stat['running'] == TRUE) {
                proc_terminate($process);
                echo '<script>alert("Проблема с тестами в подгруппе '.$i.' или с авторским решением.")</script>';
                removeDir($tmpAdress);
                exit();
            }
            proc_close($process);
            if ($stat['exitcode'] != 0) {
                echo '<script>alert("Проблема с тестами в подгруппе '.$i.' или с авторским решением.")</script>';
                removeDir($tmpAdress);
                exit();
            }
            rename($tmpAdress+$inputAdress, $tmpAdress."tests/".$inp_names.".txt");
            rename($tmpAdress+$outputAdress, $tmpAdress."answers/".$inp_names.".txt");
            $inp_names+=1;
        }
    }
    file_put_contents($tmpAdress."problem_conf.json", json_encode(array("input" => $input,
                                                                        "output" => $output, 
                                                                        "memory" => $memoryLim, 
                                                                        "time" => $timeLim, 
                                                                        "groups" => $requirments), JSON_UNESCAPED_UNICODE));
    file_put_contents($tmpAdress."statement.json", json_encode(array("name" => $name,
                                                                     "conditions" => $condition, 
                                                                     "comments" =>"$comments", 
                                                                     "examples" =>$arr), JSON_UNESCAPED_UNICODE));
   $query = "INSERT INTO archive (name) VALUES ('$name');";
   $insertion = mysqli_query($link, $query);
    if (!$insertion) {
       echo '<script>alert("Во время добавления задачи в базу данных произошла ошибка.")</script>';
        removeDir($tmpAdress);
        exit();
    }
    $query = "SELECT MAX(id) FROM archive;";
    $result = mysqli_query($link, $query);
    if (!$result) {
        echo '<script>alert("Во время добавления задачи в базу данных произошла ошибка.")</script>';
        removeDir($tmpAdress);
        exit();
    }
    $resarr = mysqli_fetch_assoc($result);
    $id_from_db = $resarr['MAX(id)'];
    if (!mkdir("$adress/$id_from_db", 0777, TRUE)) {
        echo '<script>alert("Во время добавления задачи в архив произошла ошибка.")</script>';
        removeDir($tmpAdress);
        exit();
    }
    rename($tmpAdress, dirname(__DIR__)."/".$id_from_db);
    header("Location: /");
    echo '<script>alert("Задача успешно добавлена.")</script>';
    }
?>


<html>
<head>
    <link rel="stylesheet" type="text/css" href="styles/style.css">
    <title>Upload task</title>
    <script type="text/javascript">
        function GenGroups() {
            var x = document.getElementById('NumOfGroups').value;
            if (x == "" || x <= 0) {
                alert("Количество подгрупп должно быть положительным числом");
            }
            else {
                var node = document.getElementById('Groups');
                while (node.firstChild) {
                    node.removeChild(node.firstChild);
                }
                var genblock = '';
                for (var i = 1; i <= x; i++) {
                    genblock+=  '<p>\
                                    <label>Подгруппа номер '+i+'.</label><br>\
                                    <label>Количество тестов:</label>\
                                    <input class="" type="number" name="group'+i+'TestsNum" required title="" min="1"><br>\
                                    <label>Стоимость подгруппы:</label>\
                                    <input class="" type="number" name="group'+i+'Cost" required title="" min="1"><br>\
                                    <label>Оценивание:</label>\
                                    <select name="assesment'+i+'" required>\
                                         <option value="full" selected="selected">За всю подгруппу</option>\
                                         <option value="by_test">Потестовое</option>\
                                    </select><br>\
                                    <label>Необходимые подгруппы:</label>\
                                    <div style="width:200px; height:30px; overflow:auto; border:solid 1px #C3E4FE;">';
                    for (var j = 1; j < i; j++) {
                        genblock+=      '<input type = "checkbox" name = "group'+i+'Select[]" value="'+j+'">'+j+'<br>';
                    }
                    genblock+=     '</div>\
                                    <label class="authLabel" style="font-size: 10px">Тесты</label>\
                                    <input required type="file" name="group'+i+'Tests[]" multiple><br>\
                                </p>';
                }
                document.getElementById("Groups").innerHTML = genblock;
            }
        }
    </script>
</head>


<body background = "img/124.png">
    <?php include "views/navbar.php";?>
    <center>
        <form class="authForm" name="taskUploadForm" action="upload.php" method="POST" enctype="multipart/form-data">
            <label class="authLabel" style="font-size: 15px"></label><br>
            <label class="authLabel" style="font-size: 10px">Название задачи:</label><br>
            <input class="taskName" type="text" name="name" required title="" placeholder="A + B" <?php  echo "value='".$name."'";?>><br>
            
            <label class="authLabel" style="font-size: 10px">Ограничение по времени, мс.:</label><br>
            <input class="timeLim" type="number" name="timel" required title="" min="0" placeholder="1000" pattern="0-9"<?php echo "value='$timel'";?>><br>
            
            <label class="authLabel" style="font-size: 10px">Ограничение по памяти, Мб:</label><br>
            <input class="memoryLim" type="number" name="memory" min="0" required title="" placeholder="512" <?php echo "value='$memory'";?>><br>
            <p>
                <label class="authLabel" style="font-size: 10px">Условие задачи:</label><br>
                <textarea style="min-height: 1000px; width: 60%; resize: none;" class="taskCondition" name="condition" placeholder="Условие задачи" required><?php echo $condition;?></textarea><br>
            
                <label class="authLabel" style="font-size: 10px">Комментарии к условию и тестам:</label><br>
                <textarea style="min-height: 300px; width: 60%; resize: none;" class="taskComments" name="comments" placeholder="Комментарии к задаче"><?php echo $comments;?></textarea><br> 
            </p>
            <label class="authLabel" style="font-size: 10px" placeholder="stdin">Название входного файла (stdin, если стандартный ввод):</label>
            <input style="width:30%;resize:none;"class="group" type = "text" name="input"><br><br>
            <label class="authLabel" style="font-size: 10px" placeholder="stdin">Название выходного файла (stdout, если стандартный вывод):</label>
            <input style="width:30%;resize:none;"class="group" type = "text" name="output"><br><br>

            <label class="authLabel" style="font-size: 10px">Количество подгрупп тестов:</label>
            <input type="number" name="numOfGroups" id='NumOfGroups' min="0"><br><br>
            <button onclick="GenGroups()">Подтвердить количество подгрупп.</button>
            <p id="Groups"></p>
            <label class="authLabel" style="font-size: 10px">Претесты</label><br>
            <input required type="file" name="inputSamples[]" multiple><br>
            
            <label class="authLabel" style="font-size: 10px">Чекер:</label><br>
    <!-- ?      <select required name="checkerLanguage"> -->
                <!-- <option value="C++">C++</option> -->
                <!-- <option value="Python3">Python3</option> -->
                <!-- <option value="PascalABC">PascalABC</option> -->
                <!-- <option value="Java">Java</option> -->
            <!-- </select> -->
            <input required type="file" name="checker"><br>
            
            <label class="authLabel" style="font-size : 10px">Авторское решение:</label><br>
            <!-- <select required name="checkerLanguage"> -->
                <!-- <option value="C++">C++</option> -->
                <!-- <option value="Python3">Python3</option> -->
                <!-- <option value="PascalABC">PascalABC</option> -->
                <!-- <option value="Java">Java</option> -->
            <!-- </select> -->
            <input required type="file" name="program"><br>
            
            <input class="authButton" type="submit"  value="Добавить задачу"  name="submit" ><br>
        </form>

    </center>
</body>
</html
