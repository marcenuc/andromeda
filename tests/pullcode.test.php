<?php
    require_once('../lib/x_table2.php');
    require_once('../lib/androLib.php');
    require_once('../application/a_pullcode.php');
    require_once('../application/applib.php');

    $app_info = array(
        'vcs_url' => 'https://github.com/dorgan/andromeda.git',
        'vcs_type' => 'git'
    );

    $version = a_pullcode::getLatestVersion($app_info);
    var_dump($version);
?>