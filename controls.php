<?php

include 'mpvcommands.php';
include 'configfile.php';

$message = '&nbsp;';

function save_pos_to_file() {
    global $posfile;    // comes from configfile.php
    try {
        $filepath = get_prop("path");
        if (! empty($filepath)
            && is_video($filepath) && file_exists($filepath))
        {
            $fp = fopen($posfile, 'w');

            fwrite($fp, "filepath = $filepath\n");
            $curpos = get_prop("time-pos/full");
            if (! empty($curpos))
                fwrite($fp, "position = $curpos\n");
            $volume = get_prop("volume");
            if (! empty($volume))
                fwrite($fp, "volume = $volume\n");
            error_log("saved to pos file: filepath $filepath, curpos $curpos", 0);
            run_command("show-text", "saved current position", 5000);
            fclose($fp);
            return;
        }

        error_log("No filepath, not saving position");

    } catch (Exception $e) {
        error_log("Error saving position: $e", 0);
    }
    send_mpv_cmd('{"command": [ "show-text", "current position not saved", 5000 ] }');
}

if (isset($_GET['action']))
    error_log("Action: " . $_GET['action'], 0);

// The one action this file can do without mpv running is power off
if (isset($_GET['action']) && $_GET['action'] == 'poweroff') {
    error_log("controls.php poweroff", 0);
    run_command('poweroff');

    // Redirect to a page with few images.
    // For some reason, on Android DDG,
    // images disappear after the host shuts down
    // but the rest of the page still displays fine.
    // However, this doesn't work; it gets a 404
    // even though the shutdown shouldn't happen until
    // well after the page is loaded.
    header("Location: .");
    return;
}

if (! player_is_running()) {
    header('Location: .');
    return;
}

# Get paused state, since this affects lots of other things,
# like which commands might unwantedly un-pause.
$paused = get_prop("pause");
error_log("paused status: " . print_r($paused, true), 0);
if ($paused)
    error_log('Paused', 0);
else
    error_log('NOT Paused', 0);

$curvol = get_prop("volume");
error_log('Volume ' . $curvol, 0);

$curpos = get_prop("percent-pos");
$duration = get_prop("duration");
error_log('Percent position ' . $curpos, 0);

$filepath = get_prop("path");
$filename = basename($filepath);

if (isset($_GET['action'])) {
    error_log("action: " . $_GET['action'], 0);
    switch ($_GET['action']) {
        case 'pause':
            error_log('pausing', 0);
            set_prop("pause", "true");
            $paused = 1;
            break;

        case 'play':
            set_prop("pause", "false");
            $paused = 0;
            break;

        case 'back':
            run_command("seek", "-10");
            break;

        case 'forward':
            run_command("seek", "+10");
            break;

        case 'mute':
            set_prop("mute", true);
            break;

        case 'unmute':
            set_prop("mute", false);
            break;

        case 'volumeup':
            $curvol += 5;
            if ($curvol > 100)
                $curvol = 100;
            set_prop("volume", $curvol);
            break;

        case 'volumedown':
            $curvol -= 5;
            if ($curvol < 0)
                $curvol = 0;
            set_prop("volume", $curvol);
            break;

        case 'aspect':
            // change_rectangle <val1> <val2>
            $message .= "Sorry, don't know how to change aspect ratio yet";
            break;

        case 'reallydelete':
            // XXX It would be nice to show some indication here that something
            // is happening, but I'm not sure how. The previous page,
            // where the user tapped on the Yes button for Really Delete,
            // is still showing, and giving some JS action there, like an
            // alert, doesn't do anything visible.
            // But this page isn't visible yet either. Sigh.
            delete_current_file();
            $dir = dirname($filepath);
            if (! file_exists($dir))
                $dir = dirname($dir);
            if ($dir && $dir != '/')
                header('Location: browse.php?dir=' . urlencode($dir));
            else
                header('.');
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
  <td><a href="?action=back" class="button">
      <img src="images/skip-backward.svg"
           width="64" height="64" alt="Back"></a></td>
  <td>
  <?php if ($paused): ?>
      <a href="?action=play" class="button">
      <img src="images/start.svg" width="64" height="64" alt="Play"></a>
  <?php else: ?>
      <a href="?action=pause" class="button">
      <img src="images/pause.svg" width="64" height="64" alt="Pause"></a>
  <?php endif; ?>
  </td>
  <td><a href="?action=forward" class="button">
    <img src="images/skip-forward.svg" width="64" height="64" alt="Forward"></a>
</tr>

<tr class="sliderRow" id="positionSliderRow" style="display: none">
  <td colspan="3">
    <div class="sliderFlex">
      <span class="sliderlabel" id="posSliderLabel">Played:</span>
      <input type="range" id="positionSlider" class="slider"
             name="positionSlider" min="0" max="100"
             value="<?php echo $curpos ?>" />
      <span id="totTime" class="sliderlabel"><?php echo hms($duration); ?></span>
    </div>
  </td>
</tr>

<tr class="spacer"><td colspan=3">&nbsp;

<tr>
<td><a href="?action=volumedown" class="button">
    <img src="images/volume-down.svg"
         width="64" height="64" alt="Volume down"></a></td>

<td><button id="deleteButton" command="show-modal" commandfor="delete-dialog">
    <img src="images/trash.svg" width="64" height="64" alt="Delete" class="button">

<td><a href="?action=volumeup" class="button">
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

<?php if ($filename)
echo "<p>";
echo "Playing: $filename";
?>

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
     // Would be nice to show some indication that the user has confirmed,
     // but putting an alert here makes no visible difference
     // (see earlier comment under action="reallydelete").
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
  var timePos = document.getElementById("posSliderLabel");
  // XXX how to pass mm:ss to JS to update the position slider label?
  //var posSliderLabel = document.getElementById("posSliderLabel");
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
              var poses = xhr.responseText.split(',');
              positionSlider.value = parseInt(poses[0]);
              var hms = new Date(parseFloat(poses[1]) * 1000)
                  .toISOString().substring(11, 19);
              if (hms.startsWith('00:'))
                  hms = hms.substring(3);
              timePos.innerHTML = hms;
          }
      };
      xhr.open("GET", "simplecommands.php?property=percent-pos,time-pos", true);
      xhr.send();
  }

  // Enable the two sliders through JS, since they don't work in
  // non-JS browsers.
  volumeSlider.disabled = false;
  positionSlider.disabled = false;
  // Set the containing tr, the slider's grandparent, to visible
  var positionSliderRow = document.getElementById("positionSliderRow");
  positionSliderRow.style.display = 'table-row';

  // Update the position slider regularly, so it keeps track as
  // the video plays. Not so important for the volume slider since
  // it will be updated if the user clicks the volume up/down buttons.
  setInterval(updatePositionSlider, 9000);

</script>

<?php require 'footer.php'; ?>

</body>
</html>

