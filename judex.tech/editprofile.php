<?php
include "standart.php";
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css">
		<title>Редактирование</title>
        <style>
            .infoblock{
                width: 600px;
                height: auto;
                background-color: white;
            }

            .infoheader{
                top:0;
                width: 100%;
                height: 60px;
                background-color: #e6ecff;
            }


            .infocontent{

            }

            span.infoheadertext{
                font-size: 2px;
                font-family: "Trebuchet MS";
                color: #5e88be;
                float: left;
                margin: 15px;
            }

        </style>
	</head>

	<body background="img/124.png">
    <?php include "views/navbar.php";?>
    <center>
	<h1>Изменение личной информации</h1>
        <div>
    <div class="infoblock">
        <div class="infoheader">
            <span class="infoheadertext">Пароль</span>
        </div>
        <div class="infocontent">
            <div class="infoleftcolumn"></div>
            <div class="inforightcolumn"></div>
        </div>
    </div>
        </div>
    <p></p>
    </center>
	</body>

</html>