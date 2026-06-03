<?php
$filepath = $_GET['file'];
error_log('will try to play ' . $filepath, 0);

include 'commands.php';

$SOCKETNAME = '/tmp/mpvsocket';

if (! `pidof mpv` or !file_exists($SOCKETNAME)) {
    // mpv isn't running yet, so start it.
    //shell_exec('gnome-screensaver-command -p');
    //shell_exec('rm -f ' . $SOCKETNAME);

    error_log('Starting a new mpv ...', 0);
    shell_exec('mpv --save-position-on-quit --fs --input-ipc-server='
             . $SOCKETNAME . ' --volume=50 ' . $filepath
             . ' </dev/null >/dev/null 2>&1 &');
}

else {
    // mpv is already running; tell it to load the new file.
    send_mpv_cmd('loadfile "' . $filepath . '"');
    send_mpv_cmd('{ "command": ["set_property", "pause", false] }');
}

sleep(2);
error_log("Showing controls", 0);
//header('HTTP/1.0 302 Temp');
header('Location: controls.php');

// header() doesn't seem to work consistently, so in case it doesn't,
// here's a fallback:
echo '<script type="text/javascript">';
echo 'window.location = "controls.php";';
echo '</script>';

?>
