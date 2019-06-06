<?php
include_once "standart.php";

$errorMessage = "";
if(isset($_POST["action"])){
    if ($_POST["action"] == "login"){
        $postLogin = $_POST["login"];
        if ($postLogin == $userLogin){
            $errorMessage = "OK";
            goto flagEnd;
        } else if (!preg_match("/[a-zA-Z0-9_.]{4,50}/", $postLogin)){
            $errorMessage = "Некорректный логин";
            goto flagEnd;
        } else {

            $query = "select id from users where login = '$postLogin'";
            $result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($result);
            mysqli_free_result($result);
            if ($row){
                $errorMessage = "Данный логин уже занят";
                goto flagEnd;
            } else {
                $query = "update users set login = '$postLogin' where id = $userId";
                mysqli_query($link, $query);
                $errorMessage = "OK";
            }
        }
    }
    flagEnd:
   // echo $errorMessage;
}


$query = "select login, first_name, last_name, email from users where id = $userId";
$result = mysqli_query($link, $query);
$userObj = mysqli_fetch_assoc($result);
mysqli_free_result($result);

?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
		<title>Редактирование</title>
        <style>

						/* Базовый контейнер табов */
.tabs {
	min-width: 320px;
	max-width: 800px;
	padding: 0px;
	margin: 0 auto;
}
/* Стили секций с содержанием */
.tabs>section {
	display: none;
	padding: 15px;
	background: #fff;
	border: 1px solid #ddd;
}
.tabs>section>p {
	margin: 0 0 5px;
	line-height: 1.5;
	color: #383838;
	/* прикрутим анимацию */
	-webkit-animation-duration: 1s;
	animation-duration: 1s;
	-webkit-animation-fill-mode: both;
	animation-fill-mode: both;
	-webkit-animation-name: fadeIn;
	animation-name: fadeIn;
}
/* Описываем анимацию свойства opacity */

@-webkit-keyframes fadeIn {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}
@keyframes fadeIn {
	from {
		opacity: 0;
	}
	to {
		opacity: 1;
	}
}
/* Прячем чекбоксы */
.tabs>input {
	display: none;
	position: absolute;
}
/* Стили переключателей вкладок (табов) */
.tabs>label {
	display: inline-block;
	margin: 0 0 -1px;
	padding: 15px 25px;
	font-weight: 600;
	text-align: center;
	color: #aaa;
	border: 1px solid #ddd;
	background: #f1f1f1;
	border-radius: 3px 3px 0 0;
}
/* Шрифт-иконки от Font Awesome в формате Unicode */
.tabs>label:before {
	font-family: fontawesome;
	font-weight: normal;
	margin-right: 10px;
}
/*.tabs>label[for*="1"]:before {
	content: "\1F510";
}
.tabs>label[for*="2"]:before {
	content: "\1F464";
}
.tabs>label[for*="3"]:before {
	content: "\f13b";
}
.tabs>label[for*="4"]:before {
	content: "\f13c";
}
 Изменения стиля переключателей вкладок при наведении */

.tabs>label:hover {
	color: #888;
	cursor: pointer;
}
/* Стили для активной вкладки */
.tabs>input:checked+label {
	color: #555;
	border-top: 3px solid #1bd4da;
	border-bottom: 1px solid #fff;
	background: #fff;
}
/* Активация секций с помощью псевдокласса :checked */
#tab1:checked~#content-tab1, #tab2:checked~#content-tab2, #tab3:checked~#content-tab3, #tab4:checked~#content-tab4 {
	display: block;
}
/* Убираем текст с переключателей
* и оставляем иконки на малых экранах
*/

@media screen and (max-width: 680px) {
	.tabs>label {
		font-size: 0;
	}
	.tabs>label:before {
		margin: 0;
		font-size: 18px;
	}
}
/* Изменяем внутренние отступы
*  переключателей для малых экранов
*/
@media screen and (max-width: 400px) {
	.tabs>label {
		padding: 15px;
	}
}
        </style>
	</head>

	<body background="img/124.png">
    <?php include "views/navbar.php";?>
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
	        <p>
			Здесь размещаете любое содержание....
	        </p>
	    </section>
	    <section id="content-tab2">
	        <p>
            <form action="/editprofile.php#login" method="post" enctype="application/x-www-form-urlencoded">
                <input type="text" class="authInput" name="login" value="<?php echo $userObj['login'];?>" />
                <input type="submit" class="btn green small" value="Сохранить" />
                <input type="hidden" name="action" value="login" />
            </form>
	        </p>
	    </section>
	    <section id="content-tab3">
	        <p>
	          Здесь размещаете любое содержание....
			</p>
	    </section>
	    <section id="content-tab4">
	        <p>
	          Здесь размещаете любое содержание....
	        </p>
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
