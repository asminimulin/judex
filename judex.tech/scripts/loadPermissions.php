<?php
    function loadPermissions() {
        global $link;
        global $userId;
        if(!$link || !$userId) {
            echo "<H1>DATABASE DROPPED OR userId not setted</H1>\n";
            $link = mysqli_connect("judex.tech", "judge", "123456", "judge");
            $userId = 1;
            echo mysqli_error($link);
            //exit(1);
        }
        $sql = "SELECT bin(permissions) FROM users WHERE id=$userId";
        $result = mysqli_query($link, $sql);
        global $PERMISSIONS;
        $PERMISSIONS = mysqli_fetch_array($result)[0];
    }
?>
