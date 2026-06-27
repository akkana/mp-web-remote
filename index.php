<?php

include 'mpvcommands.php';

include "header.php";

include "configfile.php";

try {
    $config = read_config();

    $wasplaying = read_position();
    /*
    if (! empty($wasplaying))
        error_log("Read position: " . print_r($wasplaying, true), 0);
    else
        error_log("No remembered position", 0);
    */

    $mediadir = $config['mediadir'];

} catch (Exception $e) {
    $mediadir = [];
    echo "<p>Eek, no mediadir!</p>";
    include 'footer.php';
    return;
}

// Make sure the cache dir is there.
// If it already exists, mkdir should return false but not raise an error.
mkdir(getenv('HOME') . '/.cache/mp-remote');

if (player_is_running()) {
    $filepath = get_prop("path");
    $filename = basename($filepath);
    // error_log("filename is: " . $filename, 0);
    if (is_video($filename))
        echo "<p><a href='controls.php'>Continue playing $filename</a>";
}

if (array_key_exists('filepath', $wasplaying)
    && is_video($wasplaying['filepath'])) {
    if (file_exists($wasplaying['filepath'])) {
        $encoded = urlencode(trim($wasplaying['filepath']));
        if (array_key_exists('position', $wasplaying)) {
            $hms = hms($wasplaying['position']);
            if (array_key_exists('volume', $wasplaying))
                echo '<p><a href="play.php?file=' . $encoded . '&pos='
                   . $hms . '&volume=' . $wasplaying['volume'] . '">Resume '
                   . basename($wasplaying['filepath'])
                   . ' (' . $hms . ")</a>\n";
            else
                echo '<p><a href="play.php?file='
                   . $encoded . '">Resume '
                   . basename($wasplaying['filepath']). "</a>\n";
        }
    } else {
        $s = 'Was playing ' . $wasplaying['filepath']
           . 'but it no longer exists';
        echo "<p>" . $s;
        error_log($s, 0);
    }
}

if ($mediadir) {
    foreach (glob($config['mediadir'] . '/*') as $f) {
        $base = basename($f);
        if ($base == 'lost+found')
            continue;
        echo '<p><a href="browse.php?dir=' . $f . '">' . $base
           . '</a></p>' . PHP_EOL;
    }
} else {
    echo '<p>You must specify mediadir = &lt;some path&gt; in mp-remote.ini</p>';
}

include 'footer.php';
?>

