<?php
    $CONF = parse_ini_file("/etc/judex/judex.conf", true);
    $SUBMISSIONS = $CONF["global"]["submissions"];
    $PROBLEMS = $CONF["global"]["problems"];
    #$LANG_CONF = parse_ini_file("$JUDEX_HOME/conf.d/language.conf");
?>
