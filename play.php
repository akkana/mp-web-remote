<?php
$filepath = $_GET['file'];
if (isset($_GET['pos']))
    $pos = $_GET['pos'];
else
    $pos = null;
if (isset($_GET['volume']))
    $volume = $_GET['volume'];
else
    $volume = null;
error_log('will try to play ' . $filepath . ' from ' . $pos
        . ' with volume ' . $volume, 0);

include 'mpvcommands.php';

if (! player_is_running()) {
    start_player($filepath, $pos);
}

else {
    // mpv is already running; tell it to load the new file and unpause.
    run_command('loadfile', $filepath);
    sleep(3);
    set_prop('pause', "false");
}

sleep(2);

if ($volume)
    set_prop('volume', $volume);

error_log("Showing controls", 0);
//header('HTTP/1.0 302 Temp');
header('Location: controls.php');

// header() doesn't seem to work consistently, so in case it doesn't,
// here's a fallback:
echo '<script type="text/javascript">';
echo 'window.location = "controls.php";';
echo '</script>';

?>
