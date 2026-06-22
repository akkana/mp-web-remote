<?php

$viddir = '/' . trim($_GET['dir'], '/');
if (! $viddir)
    header('Location: .');

include 'mpvcommands.php';
include 'configfile.php';

include "header.php";

$config = read_config();

// Don't allow browsing above the mediadir
if (! str_contains($viddir, $config['mediadir'])) {
    echo "<p>Can't browse above media dir";
    echo "<p><a href=\".\">Home</a></p>";
    require 'footer.php';
    return;
}

$message = '';

// Find out what's currently playing, if anything
try {
    $filepath = get_prop("path");
    $filename = basename($filepath);
    error_log("Currently playing $filename");
} catch (Exception $e) {
    $filepath = null;
    $filename = null;
}

$title = basename($viddir) . ' (MPV Remote)';

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

if (player_is_running() && $filename && is_video($filename)) {
    // error_log("filename is: " . $filename, 0);
    echo "<p><a href='controls.php'>Continue playing $filename</a>";
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

// Link to the parent directory
// XXX compare against $config['mediadir'] and don't show "Up One Level"
// XXX if it would go above the mediadir.
$parent = dirname($viddir);
$parentenc = urlencode($parent);
error_log("s is $parent, mediadir is " . $config['mediadir'], 0);

if ($parent != $config['mediadir']) {
  if (str_contains($parent, $config['mediadir']))
    echo "<li class='cmd'><a href=\"browse.php?dir={$parentenc}\">Up One Level</a>";

}
else
    echo "<li class='cmd'><a href=\".\">Home</a>";

foreach ($dirs as $d) {
    $bn = basename($d);
    if ($bn == 'lost+found')
        continue;
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
