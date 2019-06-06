<?php
include "standart.php";
$name = "";
$condition = "";
$groups = array();
$comment
if (isset($_POST['submit'])) {
	$adress = "/home/judge/Archive";
    $name = $_POST['name'];
	$timeLim = $_POST['timeLim'];
	$memoryLim = $_POST['memoryLim'];
	$condition = $_POST['condition'];
    $comment = $_POST['comments'];
    move_uploaded_file($_FILES['checker']['tmp_name'], "/tmp/checker.cpp");
    system('g++ /tmp/checker.cpp -o /tmp/checker_valid');
    if (!file_exists('/tmp/checker_valid')) {
        echo '<script>alert("Чекер неверен.")</script>';
    }
    else {
        exec("rm /tmp/checker_valid");
        move_uploaded_file($_FILES['program']['tmp_name'], "/tmp/auth_program.cpp");
        system('g++ /tmp/auth_program.cpp -o /tmp/auth_program');
        if (!file_exists('/tmp/auth_program')) {
           echo '<script>alert("Авторское решение неверно7.")</script>';
        }
        else {
            exec("rm /tmp/auth_program");

           // $insertion = mysqli_query($link, "INSERT INTO archive (name) VALUES ('$name');");
           // if (!$insertion) {
              //  exit("Error inserting into DB");
           // }
            $result = mysqli_query($link, "SELECT MAX(id) FROM archive;");
            if (!$result) {
               exit("Error getting id from DB");
            }
            $resarr = mysqli_fetch_assoc($result);
            $id_from_db = $resarr['MAX(id)'];
            echo $id_from_db; 
            if (!mkdir("$adress/$id_from_db", 0777, TRUE)) {
                echo "ASS";
            }
            mkdir("$adress/$id_from_db/tests", 0777, TRUE);
            mkdir("$adress/$id_from_db/answers", 0777, TRUE);
            rename("/tmp/checker.cpp", "$adress/$id_from_db/checker.cpp");
            rename("/tmp/auth_program.cpp", "$adress/$id_from_db/program.cpp");
            system('g++ '.$adress.'/'.$id_from_db.'/checker.cpp -o '.$adress.'/'.$id_from_db.'/checker');
            system('g++ '.$adress.'/'.$id_from_db.'/program.cpp -o '.$adress.'/'.$id_from_db.'/program');
            system('cd '.$adress.'/'.$id_from_db.'/');
            $inp_names = 0;
            $arr = array();
            foreach ($_FILES['input_samples']['tmp_name'] as $tmp) {
                echo "KOK<br>";
                move_uploaded_file($tmp, "$adress/$id_from_db/input.txt");
                exec("./program");
                $str1 = file_get_contents("$adress/$id_from_db/input.txt");
                $str2 = file_get_contents("$adress/$id_from_db/output.txt");
                rename("$adress/$id_from_db/input.txt", "$adress/$id_from_db/input/$inp_names");
                rename("$adress/$id_from_db/output.txt", "$adress/$id_from_db/answers/$inp_names");
                $inp_names+=1;
                $arr[] = array('in' => $str1, 'out' => $str2);
            }
            echo "<br>";
            var_dump($arr);
            file_put_contents("$adress/$id_from_db/task_conf.json", json_encode(array("input" => "stdin","output" => "stdout", "memory" => $memory, "time" => $timel, "groups_amount"=> 1, "groups" => array("required_groups" => "", "tests_amount" => count($_FILES['input_samples']['name'])+count($_FILES['input_tests']['name']) , "cost" => 100, "need_full" => 1)), JSON_UNESCAPED_UNICODE));
            file_put_contents("$adress/$id_from_db/statement.json", json_encode(array("name" => $name,"conditions" => $condition, "comments" =>"$comments", "examples" =>$arr), JSON_UNESCAPED_UNICODE));
            foreach ($_FILES['input_tests']['tmp_name'] as $tmp) {
                move_uploaded_file($tmp, "$adress/$id_from_db/input.txt");
                exec("./program");
                rename("$adress/$id_from_db/input.txt", "$adress/$id_from_db/input/$inp_names");
                rename("$adress/$id_from_db/output.txt", "$adress/$id_from_db/answers/$inp_names");
                $inp_names+=1; 
            }
            exec("cd");
        }
    }
}
?>


<html>
<head>
	<link rel="stylesheet" type="text/css" href="style.css">
    <title>Upload task</title>
    <script type="text/javascript">
        function GenGroups() {
            var x = document.getElementById('NumOfGroups').value;
            alert('kek');
            if (x == "" || x <= 0) {
                alert("Количество подгрупп должно быть положительным числом");
            }
            else {
                var node = document.getElementById('Groups');
                while (node.firstChild) {
                    node.removeChild(node.firstChild);
                }
                
                alert("all right");
                
                var genblock = '';
                for (var i = 1; i <= x; i++) {
                    genblock+='<p><label>Подгруппа номер'+i+'.</label><br><label>Количество тестов:</label><input class="" type="number" name="group'+i+'TestsNum" required title="" min="1"><br><label>Оценивание:</label><select name="assesment'+i+'" required><option value="full" selected="selected">За всю подгруппу</option><option value="by_test">Потестовое</option></select><br><label>Необходимые подгруппы:</label><input style="min-height: 3px;width:30%;resize:none;"class="group" type = "text" name="group'+i+'"></textarea></p>';
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
			<input class="timeLimit" type="number" name="timel" required title="" min="0" placeholder="1000" pattern="0-9"<?php echo "value='$timel'";?>><br>
			<label class="authLabel" style="font-size: 10px">Ограничение по памяти, Мб:</label><br>
			<input class="memoryLimit" type="number" name="memory" min="0" required title="" placeholder="512" <?php echo "value='$memory'";?>><br>
			<label class="authLabel" style="font-size: 10px">Условие задачи:</label><br>
			<textarea style="min-height: 1000px; width: 60%; resize: none;" class="taskCondition" name="condition" placeholder="Условие задачи" required><?php echo $condition;?></textarea><br>
            <label class="authLabel" style="font-size: 10px">Комментарии к условию и тестам:</label><br>
			<textarea style="min-height: 300px; width: 60%; resize: none;" class="taskComments" name="comments" placeholder="Комментарии к задаче"><?php echo $comments;?></textarea><br> 
            <label class="authLabel" style="font-size: 10px">Количество подгрупп тестов:</label>
            <input type="number" name="numOfGroups" id='NumOfGroups' min="0">
            <button onclick="GenGroups()">Подтвердить количество подгрупп.</button>
            <p id="Groups"></p>
            <label class="authLabel" style="font-size: 10px">Претесты</label><br>
			<input required type="file" name="input_samples[]" multiple><br>
			<!-- <input required type="file" name="output_samples[]"><br> -->
			<label class="authLabel" style="font-size: 10px">Основные тесты</label><br>
			<input required type="file" name="input_tests[]" multiple><br>
          <!--  <input required type="file" name="output_tests[]" multiple><br> -->
			
            <label class="authLabel" style="font-size: 10px">Чекер</label><br>
		    <select required name="language">
				<option value="C++">C++</option>
				<option value="Python3">Python3</option>
                <option value="PascalABC">PascalABC</option>
                <option value="Java">Java</option>
            </select> -->
            <input required type="file" name="checker"><br>
            
            <label class="authLabel" style="font-size : 10px">Авторское решение</label><br>
            <input required type="file" name="program"><br>
			
            <input class="authButton" type="submit"  value="Добавить задачу"  name="submit" ><br>
		</form>
		<!-- <form class="authForm" name="testsUploadForm" action="upload.php" method="POST" enctype="multipart/form-data"> -->
		<!-- </form> -->
	</center>
</body>
</html
