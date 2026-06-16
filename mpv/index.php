<?php

include "header.php";

include "configfile.php";

try {
    $config = read_config();

    $wasplaying = read_position();
    if (! empty($wasplaying))
        error_log("Read position: " . print_r($wasplaying, true), 0);
    else
        error_log("No remembered position", 0);

    $mediadir = $config['mediadir'];

    // Make sure the cache dir is there.
    // If it already exists, mkdir should return false but not raise an error.
    mkdir(getenv('HOME') . '/.cache/mp-remote');

    if (array_key_exists('filepath', $wasplaying)) {
        if (file_exists($wasplaying['filepath'])) {
            error_log("filepath exists in wasplaying", 0);
            $encoded = urlencode(trim($wasplaying['filepath']));
            if (array_key_exists('position', $wasplaying)) {
                $hms = hms($wasplaying['position']);
                error_log('Replay url: play.php?file=' . $encoded . '&pos='
                        . $hms, 0);
                echo '<p><a href="play.php?file=' . $encoded . '&pos='
                   . $hms . '">Resume '
                   . basename($wasplaying['filepath'])
                   . ' (' . $hms . ")</a>\n";
            } else {
                error_log('Was playing ' . $wasplaying['filepath']
                        . 'but it no longer exists', 0);
            }
        }
        else {
            echo '<p><a href="play.php?file='
               . $encoded . '">Resume ' . basename($wasplaying['filepath']). "</a>\n";
        }
    }

    if ($mediadir) {
        foreach (glob($config['mediadir'] . '/*') as $f) {
            echo '<p><a href="browse.php?dir=' . $f . '">' . basename($f)
               . '</a></p>' . PHP_EOL;
        }
    } else {
        echo '<p>You must specify mediadir = &lt;some path&gt; in mp-remote.ini</p>';
    }

} catch (Exception $e) {
    $mediadir = [];
    echo "<p>Eek, no mediadir!</p>";
}

include 'footer.php';
?>

