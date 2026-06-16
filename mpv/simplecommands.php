<?php

// A shim that JS can call via AJAX
// simplecommands.php?cmd=foo
// simplecommands.php?property=foo&val=bar

include 'commands.php';

if (isset($_GET['cmd'])) {
    if ($_GET['cmd'] == 'poweroff') {
        error_log("simplecommands: poweroff. First sending quit command ...", 0);
        echo "Power off";
        send_mpv_cmd('{ "command": ["quit" ] }');
        sleep(2);
        error_log("simplecommands: trying to power off", 0);
        shell_exec('sudo poweroff"');
    }
    else
        send_mpv_cmd('{ "command": ["' . $_GET['cmd'] . '" ] }');
}
else if (isset($_GET['property'])) {
    // property can have multiple values, e.g. ?property=time-pos,percent-pos

    if (isset($_GET['val'])) {
        error_log('cmd = ' . $_GET['property'] . ' and val = ' . $_GET['val'], 0);
        send_mpv_cmd('{ "command": ["set_property", "' . $_GET['property']
                   . '", '
                   . $_GET['val'] . '] }');
        echo 'Set ' . $_GET['property'] . ' &rarr; ' . $_GET['val'];
    } else {
        error_log('cmd = ' . $_GET['property'], 0);
        $props = explode(',', $_GET['property']);
        $retval = '';
        foreach ($props as $prop) {
            $val = send_mpv_cmd('{ "command": ["get_property", "'
                              . $prop . '" ] }');
            if (! empty($val)) {
                if (! empty($retval))
                    $retval .= ',';
                $retval .= $val;
            }
        }
        echo $retval;
    }
}
?>
