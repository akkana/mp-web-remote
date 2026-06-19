<?php

$viddir = '/' . trim($_GET['dir'], '/');

include 'mpvcommands.php';
include 'configfile.php';

$message = '';

// Find out what's currently playing, if anything
try {
    $filename = get_prop("path");
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

function delTree($dir)
{
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delTree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

if (isset($_GET['cmd']) && $_GET['cmd'] == 'deletedir') {
    // Check to make sure it's under the media dir,
    // so this can't be used to delete higher-up directories
    $config = read_config();
    if (str_contains($viddir, $config['mediadir'])) {
        error_log("Deleting " . $viddir);
        delTree($viddir);
        header('Location: browse.php?dir=' . urlencode(dirname($viddir)));
        return;
    } else {
        $message .= '<p><b>Refusing to delete: ' . $viddir
                 . 'is not inside the media dir</b></p>';
        error_log('Refusing to delete: ' . $viddir .
                  ' is not inside the media dir', 0);
        $viddir = $config['mediadir'];
    }
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
echo "<li class='cmd'><a href=\"browse.php?dir={$s}\">Up One Level</a><br />";

echo "<li class='cmd'><a href=\"index.php\">Main Menu</a>";

foreach ($dirs as $d) {
    $bn = basename($d);
    $encoded = urlencode(trim("$d"));
    echo "<li class='dir'><a href=\"browse.php?dir={$encoded}\">{$bn}</a>";
}

foreach ($files as $f) {
    $bn = basename($f);
    $encoded = urlencode(trim("$f"));
    echo "<li class='file'><a href=\"play.php?file={$encoded}\">{$bn}</a>";
    if ($bn == $filename)
        echo " &nbsp; &nbsp; &larr; NOW PLAYING";
}

// Finally, add an option to delete this directory
echo '<li class="cmd"><a href="browse.php?dir=' . urlencode($viddir)
   . '&cmd=deletedir">Delete this directory</a>';

?>

</ul>

<div id="status">
<?php echo $message; ?>
</div>

<?php require 'footer.php'; ?>

</body>
</html>
