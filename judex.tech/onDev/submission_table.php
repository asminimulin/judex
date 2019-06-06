<?php
    if(!$link) {
        //include "../standart.php";
        include "standart.php";
    }
    //echo "<script src=\"autocomplete.js\"></script>";
    echo "<script src=\"onDev/autocomplete.js\"</script>";
?>

<?php
    $selected_problem = $_COOKIE["selected_problem"];
    $selected_login = $_COOKIE["selected_login"];
?>

<link rel="stylesheet" type="text/css" href="../style.css">
<div class="submissions_table">
    <div class="form_wrapper">
        <?php echo "
            <form autocomplete=\"off\" class=\"inp_problem\">
                <div style=\"width:300px;\">
                    <input id=\"inp_problem_id\" type=\"text\" name=\"problem_id\" placeholder=\"Problem\" value=$selected_problem>
                </div>
            </form>
            ";
            $sql = "SELECT name FROM archive";
            $response = mysqli_query($link, $sql); $names = [];
            while($name = mysqli_fetch_array($response)) {
                array_push($names,$name["name"]);
            }
            $str = json_encode($names);
            echo "<script>
                var arr = $str;";
            echo "autocomplete(document.getElementById(\"inp_problem_id\"), arr)</script>";
            
        ?>
        <?php 
            $admin = 1;
            if($admin) {
                echo "
                    <form autocomplete=\"off\" class=\"inp_login\">
                        <div style=\"width:300px;\">
                            <input id=\"inp_login_id\" type=\"text\" name=\"login_id\" placeholder=\"Login\" value=$selected_login>
                        </div>
                    </form>
                ";
            }
            $sql = "SELECT login FROM users";
            $response = mysqli_query($link, $sql);
            $logins = [];
            while($login = mysqli_fetch_array($response)) {
                array_push($logins,$login["login"]);
            }
            $str = json_encode($logins);
            echo "<script>
                var arr = $str;\n";
            echo "autocomplete(document.getElementById(\"inp_login_id\"), arr)</script>";
        ?>
        <button type="submit" class="btn cyan mini" id="submit">Submit</button>
        <script type = "text/javascript">
            //document.cookie = "submission_login= ; expires = Thu, 01 Jan 1970 00:00:00 GMT"
            //document.cookie = "submission_problem= ; expires = Thu, 01 Jan 1970 00:00:00 GMT"
            document.getElementById("submit").onclick = function () {
                document.cookie = "selected_login= ; expires = Thu, 01 Jan 1970 00:00:00 GMT"
                document.cookie = "submission_problem= ; expires = Thu, 01 Jan 1970 00:00:00 GMT"
                var mdate = new Date();
                mdate.setTime(mdate.getTime() + 1000 * 100);
                var login = document.getElementById("inp_login_id").value;
                var problem = document.getElementById("inp_problem_id").value;
                document.cookie = "selected_login=" + login + " ; expires=" + mdate.toGMTString();
                document.cookie = "selected_problem=" + problem + " ; expires=" + mdate.toGMTString();
                //console.log("cookie setted");
                document.location.reload(true);
            };
        </script>
    </div>
    <style>
        .submissions_table {
            font-size: 20px;
        }
        .submissions_table .form_wrapper {
	        display: inline-flex;
	        flex-wrap: wrap;
	        justify-content: space-around;
            margin: 2em;
        }
        .submissions_table form {
            margin: 0 auto;
            display: inline-block;
        }
    </style>
    <?php
        $login = "admin";
        $problem = "A+B";
        if(isset($selected_login)) {
               $login = $selected_login;
        }
        if(isset($selected_problem)) {
               $problem = $selected_problem;
        }
    
        $sql = "SELECT id FROM users WHERE login=\"$login\"";
        $user_id = mysqli_fetch_array(mysqli_query($link, $sql))[0];
 
        $sql = "SELECT id FROM archive WHERE name=\"$problem\"";
        $problem_id = mysqli_fetch_array(mysqli_query($link, $sql))[0];

        $sql = "SELECT * FROM submissions WHERE user_id=\"$user_id\" AND problem_id=\"$problem_id\" ORDER BY id DESC";
        $response = mysqli_query($link, $sql);
    ?> 
        <table>
            <tr>
                 <th>ID</th>
                 <th>Дата</th>
                 <th>Язык</th>
                 <th>Статус</th>
                 <th>Пройдено тестов</th>
                 <th>Баллы</th>
                 <th>Подробнее</th>
             </tr>
        <style>
            table {
                font-family: "Lucida Sans Unicode", "Lucida Grande", Sans-Serif;
                font-size: 0.9em;
                background: white;
                border-collapse: collapse;
                text-align: left;
                margin: 0 auto 0.5em;
                border: 2px solid #69c;
                text-align: center;
            }

            pre{
                font-size: 0.9em;
            }

            table th {
                background-color: #d9e8ff;
                font-weight: normal;
                font-size: 1em;
                color: #039;
                border: 2px solid #69c;
                text-align: center;
            }

            pre{
                font-size: 0.9em;
            }

            table th {
                background-color: #d9e8ff;
                font-weight: normal;
                font-size: 1em;
                color: #039;
                border-bottom: 2px solid #69c;
                padding: 0.6em 0.7em;
            }

            table td {
                color: #669;
                padding: 0.35em 0.7em;
                border: 1px dashed #69c;
                transition: 200ms;
            }

            table tr:not(.exampleDataTr):hover td{
                background:#d9ffd8;
                transition: 200ms;
            }
        </style>
        
    <?php
        while($submission = mysqli_fetch_assoc($response)) {
            $submission_result = json_decode(file_get_contents($path_to_judge_root . "/Submissions" . "/$submission[id]" . "/result.json"), true);
            $normal_status = getNormalStatus($submission_result["status"])["status"];
            echo "<tr>
                <td>$submission[id]</td>
                <td>$submission[time]</td>
                <td>$submission[language]</td>
                <td>$normal_status</td>
                <td>$submission_result[tests_passed]</td>
                <td>$submission_result[sum]</td>
                <td><input type='button' value='Подробнее' class='btn cyan mini' onclick='location=`protocol.php?submission_id=".$row['id']."`'></td
                      </tr>";
        }
        mysqli_free_result($result);
    ?>
    </table>
</div>
