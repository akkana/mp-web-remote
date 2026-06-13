<?php

include "header.php";

include "configfile.php";

try {
    $config = read_config();

    $nowplaying = read_position();
    if (! empty($nowplaying))
        error_log("Read position: " . print_r($nowplaying, true), 0);

    $mediadir = $config['mediadir'];

    // Make sure the cache dir is there.
    // If it already exists, mkdir should return false but not raise an error.
    mkdir(getenv('HOME') . '/.cache/mp-remote');

    if (array_key_exists('filepath', $nowplaying)) {
        error_log("filepath exists in nowplaying", 0);
        $encoded = urlencode(trim($nowplaying['filepath']));
        if (array_key_exists('position', $nowplaying)) {
            $hms = gmdate("H:i:s", $nowplaying['position']);
            echo '<p><a href="play.php?file=' . $encoded . '&pos='
               . $hms . '">Resume '
               . basename($nowplaying['filepath'])
               . ' (' . $hms . ")</a>\n";
        }
        else {
            echo '<p><a href="play.php?file='
               . $encoded . '">Resume ' . basename($nowplaying['filepath']). "</a>\n";
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

