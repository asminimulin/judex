<?php
include_once "include/standart.php";


$query = "select login, first_name, last_name, email from users where id = $userId";
$result = mysqli_query($link, $query);
$userObj = mysqli_fetch_assoc($result);
mysqli_free_result($result);


$errorObj['message'] = "";
if($_SERVER["REQUEST_METHOD"] == "POST"){
    if ($_POST["action"] == "login"){
        $postLogin = $_POST["login"];
        if ($postLogin == $userLogin){
            goto flagDoNothing;
        } else if (!preg_match("/[a-zA-Z0-9_.]{4,50}/", $postLogin)){
            $errorObj["message"] = "Некорректный логин: [a-zA-Z0-9_.]: 4-50 символов";
            $errorObj['action'] = "login";
            goto flagEnd;
        } else {

            $query = "select id from users where login = '$postLogin'";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row){
                $errorObj['message'] = "Данный логин уже занят";
                $errorObj['action'] = "login";
                goto flagEnd;
            } else {
                $query = "update users set login = '$postLogin' where id = $userId";
                mysqli_query($link, $query);
            }
        }
    } else if ($_POST['action'] == "name"){
      $first_name = $_POST['first_name'];
      $last_name = $_POST['last_name'];
      if ($first_name == $userObj['first_name'] && $last_name == $userObj["last_name"]){
        goto flagDoNothing;
      }
      if (!preg_match("/[a-zA-Zа-яА-Я-]+/",$first_name) || strlen($first_name) > 50){
            $errorObj['message'] = "Некорректное имя: [a-zA-Zа-яА-Я-]: 1-50 символов";
            $errorObj['action'] = "name";
            goto flagEnd;
        } else if (!preg_match("/[a-zA-Zа-яА-Я-]+/",$last_name) || strlen($last_name) > 50){
            $errorObj['message'] = "Некорректная фамилия: [a-zA-Zа-яА-Я-]: 1-50 символов";
            $errorObj['action'] = "name";
            goto flagEnd;
        } else {
          $query = "update users set first_name = '$first_name', last_name = '$last_name' where id = $userId";
          mysqli_query($link, $query);
        }

    } else if ($_POST['action'] == "email"){
      $email = $_POST['email'];
      if ($email == $userObj['email']){
        goto flagDoNothing;
      }
      if (!preg_match("/[a-zA-Z0-9\.]+@[a-zA-Z0-0]+.[a-zA-Z]+/", $email) || strlen($email) > 50 ){
        $errorObj['message'] = "Некорректный email";
        $errorObj['action'] = "email";
        goto flagEnd;
      } else {
        $query = "select id from users where email = \"$email\"";
	$result = mysqli_query($link, $query);
	$row = mysqli_fetch_assoc($result);
	mysqli_free_result($result);
	if($row['id']){
		$errorObj["message"] = "Данный email уже зарегистрирован";
		$errorObj["action"] = "email";
	} else {
		$query = "update users set email = '$email' where id = $userId";
        	mysqli_query($link, $query);
	}
      }
    } else if ($_POST['action'] == "password"){
      $oldPassword = $_POST['oldPassword'];
      $newPassword = $_POST['newPassword'];
      $newPasswordRepeat = $_POST['newPasswordRepeat'];
      $query = "select login from users where id = $userId and password = '".md5($oldPassword)."'";
      $result = mysqli_query($link, $query);
      $row = mysqli_fetch_assoc($result);
      mysqli_free_result($result);
      if ($row){
        if (!preg_match("/[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}/", $newPassword)){
          $errorObj['message'] = "Некорректный новый пароль: [a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]: 6-50 символов";
          $errorObj['action'] = "password";
          goto flagEnd;
        } else {
          if ($newPassword == $newPasswordRepeat){
            if ($newPassword == $oldPassword){
              $errorObj['message'] = "Новый пароль должен отличаться от старого";
              $errorObj['action'] = "password";
              goto flagEnd;
            } else {
            $query = "update users set password ='".md5($newPassword)."' where id = $userId";
            mysqli_query($link, $query);
          }
          } else {
            $errorObj['message'] = "Новые пароли не совпадают";
            $errorObj['action'] = "password";
            goto flagEnd;
          }
        }
      } else {
        $errorObj['message'] = "Неверный текущий пароль";
        $errorObj['action'] = "password";
        goto flagEnd;
      }
    } else {
      die("Incorrect action");
    }
    flagEnd:
    $textForAlert = "";
    if (!$errorObj["message"]){
      $textForAlert = "Изменения успешно сохранены";
    }
    flagDoNothing:
    //System doing nothing
}

$query = "select login, first_name, last_name, email from users where id = $userId";
$result = mysqli_query($link, $query);
$userObj = mysqli_fetch_assoc($result);
mysqli_free_result($result);


?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="styles/style.css">
		<title>Редактирование</title>
	</head>

	<body background="img/124.png">
    <?php include_once "views/navbar.php";?>
    <center>
	<h1>Изменение личной информации</h1>

	<div class="tabs">
	    <input id="tab1" type="radio" name="tabs" checked onclick="updateCT(this)">
	    <label for="tab1" title="Пароль">Пароль</label>

	    <input id="tab2" type="radio" name="tabs" onclick="updateCT(this)">
	    <label for="tab2" title="Логин">Логин</label>

	    <input id="tab3" type="radio" name="tabs" onclick="updateCT(this)">
	    <label for="tab3" title="ФИО">ФИО</label>

	    <input id="tab4" type="radio" name="tabs" onclick="updateCT(this)">
	    <label for="tab4" title="Email">Email</label>

	    <section id="content-tab1">
	        <div class="containerWithTitles">
		
            <form action="/editprofile.php#password" method="post" enctype="application/x-www-form-urlencoded">
		<span class ="inputTitle">Текущий пароль</span>
            <input class="authInput" type="password" required name="oldPassword" id="password" title="Пароль"  placeholder="Текущий пароль">
            	<span class ="inputTitle">Новый пароль</span>
		<input class="authInput" type="password" pattern="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}"  required name="newPassword"  title="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]: 6-50 символов"  placeholder="Новый пароль">
<span class ="inputTitle">Повторите новый пароль</span>            
<input class="authInput" type="password" pattern="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]{6,50}"  required name="newPasswordRepeat"   title="[a-zA-Z0-9_\.!?#@%^&*()а-яА-Я]: 6-50 символов"  placeholder="Повторите новый пароль">
            <p><?php if ($errorObj['message'] && $errorObj["action"] == "password") echo $errorObj['message']; ?></p>
            <input type="submit" class="btn green small" value="Сохранить" />
            <input type="hidden" name="action" value="password" />
            </form>
	        </div>
	    </section>
	    <section id="content-tab2">
	        <div class="containerWithTitles">
            <form action="/editprofile.php#login" method="post" enctype="application/x-www-form-urlencoded">
<span class ="inputTitle">Логин</span>
                <input type="text" class="authInput" name="login"  required value="<?php echo $userObj['login'];?>" title="Логин" placeholder="Логин"/>
                <p><?php if ($errorObj['message'] && $errorObj["action"] == "login") echo $errorObj['message']; ?></p>
                <input type="submit" class="btn green small" value="Сохранить" />
                <input type="hidden" name="action" value="login" />
            </form>
	        </div>
	    </section>
	    <section id="content-tab3">
	        <div class="containerWithTitles">
	         <form action="/editprofile.php#name" method="post" enctype="application/x-www-form-urlencoded">
	     <span class ="inputTitle">Имя</span>
             <input class="authInput" type="text"  required name="first_name" id="first_name" title="[a-zA-Zа-яА-Я-]: 1-50 символов"  placeholder="Имя"  value="<?php echo $userObj['first_name'];?>">
<span class ="inputTitle">Фамилия</span>
             <input class="authInput" type="text"  required name="last_name" id="last_name" title="[a-zA-Zа-яА-Я-]: 1-50 символов"  placeholder="Фамилия" value="<?php echo $userObj['last_name'];?>">
             <p><?php if ($errorObj['message'] && $errorObj["action"] == "name") echo $errorObj['message']; ?></p>
             <input type="submit" class="btn green small" value="Сохранить" />
             <input type="hidden" name="action" value="name" />
           </form>
			</div>
	    </section>
	    <section id="content-tab4">
	        <div class="containerWithTitles">
	          <form action="/editprofile.php#email" method="post" enctype="application/x-www-form-urlencoded">
<span class ="inputTitle">Email</span>
              <input class="authInput" type="email"  pattern="[a-zA-Z0-9\.]+@[a-zA-Z0-0]+.[a-zA-Z]+" required name="email" id="email" title="example@judex.tech" placeholder="Email" value="<?php echo $userObj['email'];?>">
              <p><?php if ($errorObj['message'] && $errorObj["action"] == "email") echo $errorObj['message']; ?></p>
              <input type="submit" class="btn green small" value="Сохранить" />
              <input type="hidden" name="action" value="email" />
            </form>
	        </div>
	    </section>
	</div>




				<!-- <div>
    <div class="infoblock">
        <div class="infoheader">
            <span class="infoheadertext">Пароль</span>
        </div>
        <div class="infocontent">
            <div class="infoleftcolumn"></div>
            <div class="inforightcolumn"></div>
        </div>
    </div>
        </div> -->
    <p></p>
    </center>
    <script>
        var currentTab = location.toString().split("#")[1];
        if (currentTab == 'name'){
            document.getElementById("tab3").checked = true;
        } else if (currentTab == "login"){
            document.getElementById("tab2").checked = true;
        } else if (currentTab == "password"){
            document.getElementById("tab1").checked = true;
        } else if (currentTab == "email"){
            document.getElementById("tab4").checked = true;
        }

        setTimeout(afterLoad, 100);

        function afterLoad(){
          if (<?php echo ($textForAlert?1:0)?>){
            alert(<?php echo '"'.$textForAlert.'"';?>);
            if (<?php echo ($errorObj['action']=="login"?1:0);?>){
            document.getElementById("userLoginBlock").innerHTML = "<?php echo $userLogin;?>"
          }
          }

      }



        function updateCT(elem) {
           if(elem.id == "tab1"){
               location.href = location.toString().split("#")[0]+"#password";
           } else if (elem.id == "tab2"){
               location.href = location.toString().split("#")[0]+"#login";
           } else if (elem.id == "tab3"){
               location.href = location.toString().split("#")[0]+"#name";
           } else if (elem.id == "tab4"){
               location.href = location.toString().split("#")[0]+"#email";
           }
        }
    </script>
	</body>

</html>
