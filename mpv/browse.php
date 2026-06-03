<?php

$viddir = '/' . trim($_GET['dir'], '/');
// print("viddir =" . $viddir);

require 'commands.php';

// Find out what's currently playing, if anything
try {
    $filename = send_mpv_cmd('{ "command": ["get_property", "path"] }\n');
    $filename = basename($filename);
} catch (Exception $e) {
    $filename = null;
}

$title = basename($viddir) . ' (MPV Remote)';

include "header.php";

if (! $viddir) {
    echo "Nothing found";
    return;
}

$files = array();
$dirs = array();

// glob() gives full pathnames
foreach (glob($viddir . '/*') as $f) {
    if (is_file($f)) {
        array_push($files, $f);
    } else {
        array_push($dirs, $f);
    }
}

asort($files);
asort($dirs);

echo '<ul class="browselist">';

$p = explode('/', $viddir);
array_pop($p);
$s = urlencode(implode('/', $p));
echo "<li><a href=\"browse.php?dir={$s}\">Up One Level</a><br />";

echo "<li><a href=\"index.php\">Main Menu</a>";

foreach ($dirs as $d) {
    $bn = basename($d);
    $encoded = urlencode(trim("$d"));
    echo "<li><a href=\"browse.php?dir={$encoded}\">{$bn}</a>";
}

foreach ($files as $f) {
    $bn = basename($f);
    $encoded = urlencode(trim("$f"));
    echo "<li><a href=\"play.php?file={$encoded}\">{$bn}</a>";
    if ($bn == $filename)
        echo " &nbsp; &nbsp; &larr; NOW PLAYING";
}

?>

</ul>

<?php require 'footer.php'; ?>

</body>
</html>
