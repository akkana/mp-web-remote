<?php

if (! `pidof mpv`) {
    header('Location: index.php');
    exit();
}

include 'commands.php';
include 'configfile.php';

$message = '&nbsp;';

# Get paused state, since this affects lots of other things,
# like which commands might unwantedly un-pause.
$paused = send_mpv_cmd('{ "command": ["get_property", "pause"] }\n');
error_log("paused status: " . print_r($paused, true), 0);
if ($paused)
    error_log('Paused', 0);
else
    error_log('NOT Paused', 0);

$curvol = send_mpv_cmd('{ "command": ["get_property", "volume"] }\n');
error_log('Volume ' . $curvol, 0);

$curpos = send_mpv_cmd('{ "command": ["get_property", "percent-pos"] }\n');
error_log('Percent position ' . $curpos, 0);

if (isset($_GET['action'])) {
    error_log("action: " . $_GET['action'], 0);
    switch ($_GET['action']) {
        case 'pause':
            send_mpv_cmd('{ "command": ["set_property", "pause", true] }');
            $paused = 1;
            break;

        case 'play':
            send_mpv_cmd('{ "command": ["set_property", "pause", false] }');
            $paused = 0;
            break;

        case 'back':
            send_mpv_cmd('{ "command": [ "seek", "-10" ] }');
            break;

        case 'forward':
            send_mpv_cmd('{ "command": [ "seek", "+10" ] }');
            break;

        case 'mute':
            send_mpv_cmd('{ "command": ["set_property", "mute", true] }');
            break;

        case 'unmute':
            send_mpv_cmd('{ "command": ["set_property", "mute", false] }');
            break;

        case 'volumeup':
            $curvol += 5;
            if ($curvol > 100)
                $curvol = 100;
            send_mpv_cmd('{ "command": ["set_property", "volume", '
                       . $curvol . '] }');
            break;

        case 'volumedown':
            $curvol -= 5;
            if ($curvol < 0)
                $curvol = 0;
            send_mpv_cmd('{ "command": ["set_property", "volume", '
                       . $curvol . '] }');
            break;

        case 'aspect':
            // change_rectangle <val1> <val2>
            $message .= "Sorry, don't know how to change aspect ratio yet";
            break;

        case 'status':
            $message = shell_exec('sh ./mpvstatus.sh');

        case 'reallydelete':
            // sadly, pausing_keep_force doesn't work with get_property filename
            // or path: it doesn't print anything
            $filepath = send_mpv_cmd('{ "command": ["get_property", "path"] }');
            error_log("filepath: " . $filepath);

            send_mpv_cmd('{ "command": ["set_property", "pause", true] }');
            $paused = 1;
            send_mpv_cmd('{"command": [ "show-text", "Deleted: '
                       . basename($filepath) . '", 5000]  }');

            unlink($filepath);
            $message .= 'Deleted ' . $filepath;
            $encoded = urlencode(dirname("$filepath"));

            // If dir is empty, rmdir it
            $dir = dirname($filepath);
            if (count(scandir($dir)) <= 2) {
                error_log("Removing now-empty directory " . $dir, 0);
                rmdir($dir);
                sleep(1);
                error_log("going to" . dirname($dir), 0);
                header('Location: browse.php?dir=' . urlencode(dirname($dir)));
                return;
            }

            sleep(1);
            header('Location: browse.php?dir=' . urlencode($dir));
            break;

        case 'poweroff':
            error_log("controls poweroff", 0);

            // Get and save the current position
            $filepath = send_mpv_cmd('{ "command": ["get_property", "path"] }');
            $curpos = send_mpv_cmd('{ "command": ["get_property", "time-pos/full"] }');

            error_log("Trying to save current position before exiting", 0);
            $config = read_config();
            if (array_key_exists('mediadir', $config)) {
                if (! empty($filepath))
                    $config['filepath'] = $filepath;
                if (! empty($curpos))
                    $config['position'] = $curpos;
                write_config($config);

                send_mpv_cmd('{"command": [ "show-text", "saved: '
                           . print_r($config, true) . '", 5000]  }');
            } else {
                send_mpv_cmd('{"command": [ "show-text", "Not saving config, couldn\'t get mediadir", 5000] }');
                $message .= "Not saving config, couldn't get mediadir";
                error_log("Not saving config, couldn't get mediadir", 0);
            }

            // Quit mpv, to make sure it saves the current position
            // XXX This fails if mpv isn't running
            send_mpv_cmd('{ "command": [ "quit" ] }');
            sleep(2);

            // shell_exec('sh -c "sleep 3; sudo poweroff" &');

            // Redirect to a page with few images.
            // For some reason, on Android DDG,
            // images disappear after the host shuts down
            // but the rest of the page still displays fine.
            // However, this doesn't work; it gets a 404
            // even though the shutdown shouldn't happen until
            // well after the page is loaded.
            header("Location: index.php");
            break;
    }
}

include "header.php";

?>

<center>

<dialog id="delete-dialog" class="dialog">
  <p>Really delete?

  <br /><br />
  <a href="?action=reallydelete">
    <button commandfor="delete-dialog" id="reallydeletebtn"
            command="close">Yes</button></a>

  &nbsp; &nbsp; &nbsp; &nbsp;
  <button id="dontdeletebtn"
          commandfor="delete-dialog" command="close">No</button>
</dialog>

<table class="controls">
<tr>
<td><a href="?action=back">
    <img src="images/skip-backward.svg"
         width="64" height=64"" alt="Back"></a></td>
<td>
<?php if ($paused): ?>
    <a href="?action=play">
    <img src="images/start.svg" width="64" height="64" alt="Play"></a>
<?php else: ?>
    <a href="?action=pause">
    <img src="images/pause.svg" width="64" height="64" alt="Pause"></a>
<?php endif; ?>
</td>
<td><a href="?action=forward">
    <img src="images/skip-forward.svg" width="64" height="64" alt="Forward"></a>
</tr>

<tr class="slider positionSlider">
<td colspan=3">
    <span class="sliderlabel">Percent played:</span>
    <input type="range" id="positionSlider" name="positionSlider"
           min="0" max="100" value="<?php echo $curpos ?>"
           disabled style="width: 75%" />

<tr class="spacer"><td colspan=3">&nbsp;

<tr>
<td><a href="?action=volumedown">
    <img src="images/volume-down.svg"
         width="64" height="64" alt="Volume down"></a></td>

<td><button id="deleteButton" command="show-modal" commandfor="delete-dialog">
    <img src="images/trash.svg" width="64" height="64" alt="Delete">

<td><a href="?action=volumeup">
    <img src="images/volume-up.svg"
         width="64" height="64" alt="Volume down"></a></td>
</button>

<tr class="slider">
<td colspan="3">
    <img src="images/volume-down.svg"
         width="25" height="25" alt="Volume down">
    <input type="range" id="volumeSlider" name="volumeSlider" min="0" max="100"
           value="<?php echo $curvol; ?>" disabled style="width: 75%" />
    <img src="images/volume-up.svg"
         width="25" height="25" alt="Volume down">

</tr>

<!--
<td><a href="?action=aspect">Aspect</a>
<td><a href="?action=status">Get status</a>
 -->

</table>

<div id="status">
<?php echo $message; ?>
</div>

</center>

<script language="JavaScript">

 /* Set up the delete dialog in case of an older browser
  * that doesn't understand commandfor
  */
 var btn = document.getElementById("deleteButton");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     var dialog = document.getElementById("delete-dialog");
     dialog.showModal();
 };
 btn = document.getElementById("reallydeletebtn");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     window.location.href = "controls.php?action=reallydelete";
 };
 btn = document.getElementById("dontdeletebtn");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     var dialog = document.getElementById("delete-dialog");
     dialog.close();
 };

  var volumeSlider = document.getElementById("volumeSlider");
  volumeSlider.onchange = function() {
      // Writing to a file is hard from JS (maybe impossible?)
      // because of security concerns. But it can load a PHP URL
      // that can do things like write a command to the mpv player.
      // (e || window.event).preventDefault();

      var statdiv = document.getElementById("status");
      statdiv.innerHTML = "Setting volume to " + this.value;

      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(e) {
          if (xhr.readyState == 4 && xhr.status == 200) {
              statdiv.innerHTML = xhr.responseText;
          }
      };
      xhr.open("GET", "simplecommands.php?property=volume&val="
                    + this.value, true);
      xhr.send();
  }

  var positionSlider = document.getElementById("positionSlider");
  positionSlider.onchange = function() {
      // Writing to a file is hard from JS (maybe impossible?)
      // because of security concerns. But it can load a PHP URL
      // that can do things like write a command to the mpv player.
      // (e || window.event).preventDefault();

      var statdiv = document.getElementById("status");
      statdiv.innerHTML = "Setting position to " + this.value;

      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(e) {
          if (xhr.readyState == 4 && xhr.status == 200) {
              //positionSlider.VALUE = xhr.responseText;
          }
      };
      xhr.open("GET", "simplecommands.php?property=percent-pos&val="
                    + this.value, true);
      xhr.send();
  }

  function updatePositionSlider() {
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function(e) {
          if (xhr.readyState == 4 && xhr.status == 200) {
              var pos = parseInt(xhr.responseText);
              positionSlider.value = pos;
          }
      };
      xhr.open("GET", "simplecommands.php?property=percent-pos", true);
      xhr.send();
  }

  // Enable the two sliders through JS, since they don't work in
  // non-JS browsers.
  volumeSlider.disabled = false;
  positionSlider.disabled = false;
  // Set the containing tr, the slider's grandparent, to visible
  positionSlider.parentElement.parentElement.style.display = 'table-row';
  //alert("set positionSlider.parentElement.parentElement.display to table-row:"
  //      + positionSlider.parentElement.parentElement.display);

  // Update the position slider regularly, so it keeps track as
  // the video plays. Not so important for the volume slider since
  // it will be updated if the user clicks the volume up/down buttons.
  setInterval(updatePositionSlider, 9000);

</script>

<?php require 'footer.php'; ?>

</body>
</html>

