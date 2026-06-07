<?php

include "header.php";

include "configfile.php";

try {
    $config = read_config();

    $mediadir = $config['mediadir'];

    if (array_key_exists('filepath', $config)) {
        $encoded = urlencode(trim($config['filepath']));
        if (array_key_exists('position', $config)) {
            $hms = gmdate("H:i:s", $config['position']);
            echo '<p><a href="play.php?file=' . $encoded . '&pos='
               . $hms . '">Resume '
               . basename($config['filepath'])
               . ' (' . $hms . ")</a>\n";
        }
        else {
            echo '<p><a href="play.php?file='
               . $encoded . '">Resume ' . basename($config['filepath']). "</a>\n";
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

