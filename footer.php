
<dialog id="poweroff-dialog" class="dialog">
  <p>Really power off?

  <br /><br />
  <a href="controls.php?action=poweroff">
     <button id="reallypoweroffbtn" commandfor="poweroff-dialog"
             command="close">Yes</button></a>

  &nbsp; &nbsp; &nbsp; &nbsp;
  <button id="cancelpoweroffbtn"
          commandfor="poweroff-dialog" command="close">No</button>
</dialog>

<hr>
<a href="index.php"><img src="images/browse.svg"
         width="48" height="48" alt="Browse"></a>

<button id="offButton" command="show-modal" commandfor="poweroff-dialog"
        class="farright">
    <img src="images/power.svg" width="48" height="48" alt="Power button">
</button>

<script language="JavaScript">
 /* Set up the poweroff dialog in case of an older browser
  * that doesn't understand commandfor
  */
 var btn = document.getElementById("offButton");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     var dialog = document.getElementById("poweroff-dialog");
     dialog.showModal();
 };
 btn = document.getElementById("reallypoweroffbtn");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     window.location.href = "controls.php?action=poweroff";
 };
 btn = document.getElementById("cancelpoweroffbtn");
 btn.command = null;
 btn.commandfor = null;
 btn.onclick = function(e) {
     var dialog = document.getElementById("poweroff-dialog");
     dialog.close();
 };
</script>

</body>
</html>
