<?php
$filepath = $_GET['file'];
if (isset($_GET['pos']))
    $pos = $_GET['pos'];
error_log('will try to play ' . $filepath . ' from ' . $pos, 0);

include 'commands.php';

$SOCKETNAME = '/tmp/mpvsocket';

if (! `pidof mpv` or !file_exists($SOCKETNAME)) {
    // mpv isn't running yet, so start it.
    //shell_exec('gnome-screensaver-command -p');
    //shell_exec('rm -f ' . $SOCKETNAME);

    error_log('Starting a new mpv ...', 0);
    if (isset($pos) && !empty($pos))
        $startarg = ' --start=' . $pos;
    else {
        $startarg = '';
        error_log("No start argument", 0);
    }
    $cmd = 'mpv --fs --input-ipc-server='
         . $SOCKETNAME . $startarg . ' --volume=50 ' . $filepath
         . ' </dev/null >/dev/null 2>~/.cache/mp-remote/mpv-err.txt &';
    error_log('Trying to run: ' . $cmd, 0);
    shell_exec($cmd);
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
