<?php
    $JUDEX_HOME = getenv("JUDEX_HOME");
    $CONF = parse_ini_file("$JUDEX_HOME/conf.d/judex.conf", true);
    $SUBMISSIONS = $CONF["testing"]["submissions_dir"];
    $PROBLEMS = $CONF["archive"]["problems_dir"];
    $LANG_CONF = parse_ini_file("$JUDEX_HOME/conf.d/language.conf");
?>
