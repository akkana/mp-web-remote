<?php

function read_config()
{
    if (file_exists(getcwd() . '/mp-remote.ini'))
        $config = parse_ini_file(getcwd() . '/.config/mp-remote.ini');
    else if (file_exists(getenv('HOME') . '/.config/mp-remote.ini'))
        $config = parse_ini_file(getenv('HOME') . '/.config//mp-remote.ini');
    else
        $config = array();
    //echo "Read from config file:";
    //print_r($config);

    error_log("Read config:");
    error_log(print_r($config, true));
    return $config;
}

function write_config($configvars)
{
    if (empty($configvars)) {
        error_log("Not writing empty config!", 0);
        return;
    }
    error_log("Writing config file", 0);
    error_log(print_r($configvars, true));
    try {
        $config = fopen(getenv('HOME') . '/.config/mp-remote.ini', 'w');
    } catch (Exception $e) {
        echo "<p>Couldn't open ~/.config/mp-remote.ini</p>";
        try {
            $config =  fopen(getcwd() . '/mp-remote.ini', 'w');
        } catch (Exception $e) {
            echo "<p>Couldn't open " . getcwd() . '/mp-remote.ini'
               . " either</p>";
            return;
        }
    }

    fwrite($config, "; mp-remote config file. Comments use semicolons.\n");
    foreach($configvars as $key => $value) {
        error_log("Writing " . $key . " = " . $value, 0);
        fwrite($config, $key . " = " . $value . "\n");
    }
    fclose($config);
}


