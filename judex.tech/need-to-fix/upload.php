<H1>NEED TO FIX</H1>
<?php
    include "../standart.php";
?>
<?php
function printt($str) {
    echo "<p>$str</p>";
}
if (isset($_POST['submit'])) {
    printt("judgeroot = $PATH_TO_JUDGE_ROOT");
	$address = $PATH_TO_JUDGE_ROOT.'/standart.judge.com/need-to-fix/newTask';//'/tmp/new_task'
    printt("address = $address");
    $name = $_POST['name'];
    printt("name = $name");
	$timel = $_POST['timel'];
    printt("timel = $timel");
	$memory = $_POST['memory'];
    printt("memory = $memory");
	$condition = $_POST['condition'];
    printt("condition = $condition");
	$sample_in = 1;//$_POST['in'];
    printt("sample_in = $sample_in");
	$sample_out = 2;//$_POST['out'];
    printt("sample_out = $sample_out");
	mkdir("$address", 0777, TRUE);
    mkdir("$address/input", 0777, TRUE);
	file_put_contents("$adress/task_conf.json", json_encode(array("input" => "stdin","output" => "stdout", "memory" => $memory, "time" => $timel, "groups_amount"=> 1, "groups" => array("required_groups" => "", "tests_amount" => 10, "cost" => 100, "need_full" => 1)), JSON_UNESCAPED_UNICODE));
	// добавить кол-во тестов, группы тестов
	file_put_contents("$adress/statement.json", json_encode(array("name" => $name,"conditions" => $condition, "comments" =>"", "examples" =>array("in" => $sample_in,"out" => $sample_out)), JSON_UNESCAPED_UNICODE));
	// добавить комментарии, поддержку не одного семпла
    /*$name = 0;
            $tmp = $_FILES["input_sample"]["tmp_name"][$file];
            move_uploaded_file($tmp, "$adress/input/$name.txt");
            $name+=1;
        }
    }
    move_uploaded_file($_FILES["program"]["tmp_name"], "$adress/checker.cpp");//NOT COMPLETE*/
}
?>
<html>
<head>
	<link rel="stylesheet" type="text/css" href="../style.css">
    <title>Upload task</title>
    <script type="text/javascript">
    </script>
</head>
<body background = "../img/124.png">
	<?php include "../views/navbar.php";?>
	<center>
		<form class="authForm" name="taskUploadForm" action="upload.php" method="POST">
			<label class="authLabel" style="font-size: 10px">Название задачи</label><br>
			<input class="taskName" type="text" name="name" required title="" placeholder="A + B" <?php "value='"."name"."'";?> ><br>
			<label class="authLabel" style="font-size: 10px">Ограничение по времени, мс.</label><br>
			<input class="timeLimit" type="number" name="timel" required title="" min="0" placeholder="1000" pattern="0-9"<?php echo "value='timel'";?>><br>
			<label class="authLabel" style="font-size: 10px">Ограничение по памяти, Мб</label><br>
			<input class="memoryLimit" type="number" name="memory" min="0" required title="" placeholder="512" <?php echo "value='memory'";?>><br>
			<label class="authLabel" style="font-size: 10px">Условие задачи</label><br>
			<textarea style="min-height: 1000px; width: 60%; resize: none;" class="taskCondition" name="condition" placeholder="Условие задачи" required><?php echo "condition";?></textarea><br>
			<label class="authLabel" style="font-size: 10px">Претесты</label><br>
			<input required type="file" name="input_samples">
			<input required type="file" name="output_sample"><br>
			<label class="authLabel" style="font-size: 10px">Основные тесты</label><br>
			<input required type="file" name="input_tests" multiple> <br>
			<label class="authLabel" style="font-size: 10px">Чекер</label><br>
			<select required name="language">
				<option value="C++">C++</option>
				<option value="Python3">Python3</option>
                <option value="PascalABC">PascalABC</option>
                <option value="Java">Java</option>
            </select>
            <input required type="file" name="program"><br>
			<input class="authButton" type="submit"  value="Добавить задачу"  name="submit" ><br>
		</form>
		<!-- <form class="authForm" name="testsUploadForm" action="upload.php" method="POST" enctype="multipart/form-data"> -->
		<!-- </form> -->
	</center>
</body>
</html>
