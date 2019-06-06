<?php
$elemOnPage = 30;
$pageNumber = 1;
if (isset($_GET['p'])){
    $pageNumber  = max(1, (int)($_GET['p']));
}
?>

<center>
    <?php
        if(!$link) {
            include "/standart.php";
        }
        if(!$link) {
            echo "<H1>DATABASE ERROR. RECONNECT.</H1>";
            $link = mysqli_connect("judex.tech", "judge", "123456", "judge");
            echo mysqli_error($link);
        }
        echo "<script src=\"/js/autocomplete.js\"></script>";
    ?>

    <?php
        $selected_problem_id = $_COOKIE["selected_problem_id"];
        if($selected_problem_id.length == 0 || $selected_problem_id == "0") {
            $selected_problem_id = null;
        }
        $selected_user_id = $_COOKIE["selected_user_id"];
        if($selected_user_id.length == 0 || $selected_user_id == "0") {
            $selected_user_id = null;
        }

        $sql = "SELECT id, name FROM problems ORDER BY id DESC";
        $response = mysqli_query($link, $sql);
        $problems = [];
        while($row = mysqli_fetch_assoc($response)) {
            $problems[$row["id"]] = $row["name"];
        }
       // $problems["0"] = "Все";
        $sql = "SELECT id, login FROM users ORDER BY id DESC";
        $response = mysqli_query($link, $sql);
        $users = [];
        while($row = mysqli_fetch_assoc($response)) {
            $users[$row["id"]] = $row["login"];
        }
       // $users["0"] = "Все";
    ?>

    <link rel="stylesheet" type="text/css" href="/style.css">
    <div class="submissions_table">
        <font color="red"><H1>Посылки</H1></font>
        <div class="form_wrapper">
            <?php   
                if(!$selected_problem_id) {
                  //  $problem_name = "Все";
                } else {
                    $problem_name = $problems[$selected_problem_id];
                }
                echo "
                    <form autocomplete=\"off\" class=\"inp_problem\">
                        <div style=\"width:300px;\">
                            <input id=\"inp_problem_name\" type=\"text\" name=\"problem_id\" placeholder=\"Problem\" value=\"$problem_name\">
                            <input id=\"inp_problem_id\" type=\"hidden\" value=\"$selected_problem_id\">
                        </div>
                    </form>
                ";
               // $problems[0] = "Все";
                $str = json_encode($problems);
                echo "
                    <script>
                        var arr = $str;
                        autocomplete(document.getElementById(\"inp_problem_name\"), document.getElementById(\"inp_problem_id\"), arr);
                    </script>
                ";
            ?>
            <?php 
                $admin = 1;
                if($admin) {
                    if($selected_user_id) {
                        $user_login = $users[$selected_user_id];
                    } else {
                      //
                        //$user_login = "Все";
                    }
                    echo "
                        <form autocomplete=\"off\" class=\"inp_login\">
                            <div style=\"width:300px;\">
                                <input id=\"inp_user_login\" type=\"text\" name=\"login_id\" placeholder=\"Login\" value=\"$user_login\">
                                <input id=\"inp_user_id\" type=\"hidden\" value=\"$selected_user_id\">
                            </div>
                        </form>
                    ";
                
                    $str = json_encode($users);
                    echo "
                        <script>
                            var arr = $str;
                            autocomplete(document.getElementById(\"inp_user_login\"), document.getElementById(\"inp_user_id\"), arr);
                        </script>
                    ";
                }
            ?>
            <input type="submit" class="btn cyan mini" id="submit" value="Submit">
            <script type = "text/javascript">
                //document.cookie = "submission_login= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
                //document.cookie = "submission_problem= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
                document.getElementById("submit").onclick = function () {
                    document.cookie = "selected_user_id= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
                    document.cookie = "selected_problem_id= ; expires = Thu, 01 Jan 1970 00:00:00 GMT";
                    var mdate = new Date();
                    mdate.setTime(mdate.getTime() + 1000 * 5);
                    var login = document.getElementById("inp_user_id").value;
                    var problem = document.getElementById("inp_problem_id").value;
                    document.cookie = "selected_user_id=" + login + " ; expires=" + mdate.toGMTString();
                    document.cookie = "selected_problem_id=" + problem + " ; expires=" + mdate.toGMTString();
                    //console.log("cookie setted");
                    document.location.reload(true);
                };
            </script>
        </div>
        <style>
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
        <table>
            <?php
                $sql = "SELECT * FROM submissions";
                if($selected_user_id && $selected_problem_id) {
                    $sql = $sql . " WHERE user_id=$selected_user_id AND problem_id=$selected_problem_id";
                } else if($selected_user_id) {
                    $sql = $sql . " WHERE user_id=$selected_user_id";
                } else if($selected_problem_id) {
                    $sql = $sql . " WHERE problem_id=$selected_problem_id";
                }
                $sql = $sql." order by id desc limit ". (($pageNumber - 1) * $elemOnPage) . ", $elemOnPage";
                $response = mysqli_query($link, $sql);
            ?> 

            <tr>
                 <th>ID</th>
                 <th>Дата</th>
                 <th>Название</th>
                 <th>Логин</th>
                 <th>Язык</th>
                 <th>Статус</th>
                 <th>Пройдено тестов</th>
                 <th>Баллы</th>
                 <th>Подробнее</th>
             </tr>
                    
            <?php
                while($submission = mysqli_fetch_assoc($response)) {
                    $submission_result = json_decode(file_get_contents($path_to_judge_root . '/' . "Submissions" . '/' . "$submission[id]" . '/' . "result.json"), true);
                    $normal_status = getNormalStatus($submission_result["status"])["status"];
                    $problem_name = $problems["$submission[problem_id]"];
                    $user_login = $users["$submission[user_id]"];
                    echo "<tr>
                        <td>$submission[id]</td>
                        <td>$submission[time]</td>
                        <td><a href='task.php?id=".$submission['problem_id']."'>$problem_name</a></td>
                        <td><a href='user.php?id=".$submission['user_id']."'>$user_login</a></td>
                        <td>$submission[language]</td>
                        <td>$normal_status</td>
                        <td>$submission_result[tests_passed]</td>
                        <td>$submission_result[sum]</td>
                        <td><input type='button' value='Подробнее' class='btn cyan mini' onclick='location=`protocol.php?submission_id=".$submission['id']."`'></td>
                              </tr>";
                }
                mysqli_free_result($result);
            $sql = "SELECT * FROM submissions";
            if($selected_user_id && $selected_problem_id) {
                $sql = $sql . " WHERE user_id=$selected_user_id AND problem_id=$selected_problem_id";
            } else if($selected_user_id) {
                $sql = $sql . " WHERE user_id=$selected_user_id";
            } else if($selected_problem_id) {
                $sql = $sql . " WHERE problem_id=$selected_problem_id";
            }
            $sql = $sql." order by id desc limit ". (($pageNumber - 1+1) * $elemOnPage) . ", $elemOnPage";
            $tmpRow = mysqli_fetch_assoc(mysqli_query($link, $sql));
            $nextPage = $tmpRow?true:false;
            ?>
        </table>
    </div>
    <div class="pagingBar">
        <input type="button" class="btn mini blue" <?php if ($pageNumber==1) echo "disabled"?> value="<" onclick="changePage(<?php echo ($pageNumber-1); ?>);"><?php echo " <g>".$pageNumber."</g> ";?><input type="button" class="btn mini blue" value=">" <?php if (!$nextPage) echo "disabled"?> onclick="changePage(<?php echo ($pageNumber+1); ?>);">
    </div>
</center>
<script type="text/javascript">
    function changePage(np) {
        location = "admin.php?p="+np;
    }
</script>