<?php
$filepath = $_GET['file'];
if (isset($_GET['pos']))
    $pos = $_GET['pos'];
else
    $pos = 0;
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
    // When running loadfile, the position parameter it needs
    // is start, not time-pos, and start accepts mm:ss.
    // However, there's an undocumented quirk: if you want to use the
    // 4th argument to set any runtime arguments like start,
    // you have to set the third argument to -1.
    run_command('loadfile', [$filepath, 'replace', -1, 'start=' . $pos]);
    sleep(3);
    set_prop('pause', "false");
}

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
