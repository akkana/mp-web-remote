<?php

$SOCKETNAME = '/tmp/mpvsocket';

function quote_arg_if_needed($arg) {
    // Don't quote arg if it's a number, or the string true or false.
    // PHP doesn't seem to be good at handling  false as an
    // object distinct from '' or null, so it's safest to pass
    // it as a string.
    // mpv wants unquoted strings true/false for arguments.
    if (is_null($arg))
        return null;
    if ($arg == '') {
        // error_log("arg is an empty string", 0);
        return '""';
    }
    // MPV requires quoting for +10, but not for -10
    if (str_starts_with($arg, '+'))
        return '"' . $arg . '"';
    if (is_numeric($arg) || $arg == 'true' || $arg == 'false')
        return $arg;
    return '"' . $arg . '"';
}

function send_mpv_cmd($cmd, $arg1=null, $arg2=null)
{
    global $SOCKETNAME;

    error_log('send_mpv_cmd ' . $cmd . ', ' . $arg1 . ', ' . $arg2, 0);
    $arg1 = quote_arg_if_needed($arg1);
    $arg2 = quote_arg_if_needed($arg2);
    if ($arg1 && ! is_null($arg2))
        $mpvcmd = '{ "command": [ "' . $cmd . '", ' . $arg1
                . ', ' . $arg2 . ' ] }';
    else if ($arg1)
        $mpvcmd = '{ "command": [ "' . $cmd . '", ' . $arg1 . ' ] }';
    else
        $mpvcmd = '{ "command": [ "' . $cmd . '" ] }';

    $shellcmd = "echo '$mpvcmd' | socat - $SOCKETNAME";
    error_log("Running shell command: " . $shellcmd, 0);
    $s = shell_exec($shellcmd);

    //error_log("Read: " . $s, 0);
    //error_log("Type: " . gettype($s), 0);
    $j = json_decode($s, $associative=true);
    //error_log("json_decode gives: " . print_r($j, true), 0);
    if ($j != null && array_key_exists('data', $j))
        return $j['data'];
    return null;
}

function get_prop($prop) {
    error_log("get_prop '$prop'", 0);
    return send_mpv_cmd('get_property', $prop);
}

function set_prop($prop, $val) {
    error_log("set_prop '$prop', '$val'", 0);
    return send_mpv_cmd('set_property', $prop, $val);
}

function run_command($cmd, $arg=null) {
    error_log('run_command ' . $cmd . ', ' . $arg, 0);
    switch($cmd) {
        case 'poweroff':
            poweroff();
            return;
    }
    send_mpv_cmd($cmd, $arg);
}

function player_is_running() {
    global $SOCKETNAME;
    return `pidof mpv` && file_exists($SOCKETNAME);
}

function start_player($filepath, $pos) {
    global $SOCKETNAME;

    // mpv isn't running yet, so start it.
    //shell_exec('gnome-screensaver-command -p');
    //shell_exec('rm -f ' . $SOCKETNAME);

    error_log('Starting a new mpv ...', 0);
    if (isset($pos) && $pos)
        $startarg = ' --start=' . $pos;
    else {
        $startarg = '';
        error_log("No start argument", 0);
    }
    $cmd = 'mpv --fs --input-ipc-server='
         . $SOCKETNAME . $startarg . ' --volume=50 ' . $filepath
         . ' </dev/null >~/.cache/mp-remote/mpv-err.txt 2>&1 &';
    error_log('Trying to run: ' . $cmd, 0);
    shell_exec($cmd);
}

function delete_current_file()
{
    $filepath = get_prop("path");
    if (! $filepath) {
        error_log("Eek! Can't delete null file", 0);
        return;
    }
    error_log("delete filepath: " . $filepath);

    // Under some circumstances, changing to another video will
    // start the new video at the position from the previous,
    // now deleted, video.
    // (This happens on Mint but I can't reproduce it on Debian.)
    // Want new videoes to default to playing from 0, so let's
    // see if setting the position back to the beginning helps:
    set_prop("pause", true);
    set_prop("percent-pos", 0);
    // Sometimes it seems to start playing again before being replaced, so
    set_prop("pause", true);

    // loadfile '' will close the player window.
    // So load a jpeg instead.
    //run_command('loadfile', '');
    set_prop('image-display-duration', 'inf');
    run_command('loadfile', getcwd() . '/images/tux-filmstrip.jpg');

    // XXX Sadly, show-text ignores the time (supposed to be milliseconds);
    // the message disappears in a second or less.
    run_command("show-text", "Deleted: " . basename($filepath), 10000);

    unlink($filepath);
    // $message .= 'Deleted ' . $filepath;
    error_log("Deleted: $filepath", 0);

    // If dir is empty, rmdir it
    $dir = dirname($filepath);
    if (count(scandir($dir)) <= 2) {
        error_log("Removing now-empty directory " . $dir, 0);
        rmdir($dir);
        sleep(1);
        $dir = dirname($dir);
        error_log("going to" . $dir, 0);
        header('Location: browse.php?dir=' . urlencode($dir));
        return;
    }
}

function poweroff()
{
    error_log("Powering off...", 0);

    // Get and save the current position
    try {
        $filepath = get_prop("path");
        if ($filepath && !empty($filepath)) {
            error_log("Playing filepath " . $filepath, 0);
            $curpos = get_prop("time-pos/full");
            error_log("Current position: " . $curpos, 0);

            error_log("Trying to save current status before exiting", 0);
            save_pos_to_file();
        } else {
            error_log("Couldn't get file path currently playing", 0);
        }

    } catch (Exception $e) {
        error_log("Whoops, error getting file and position: " . $e, 0);
        $filepath = '';
        $curpos = '';
    }

    // Quit mpv, to make sure it saves the current position
    // This fails if mpv isn't running, so enclose in a try.
    try {
        run_command("quit");
        sleep(2);
    } catch (Exception $e) {
        error_log("quit didn't work, probably mpv isn't running");
    }

    shell_exec('sh -c "sleep 3; sudo poweroff" &');
}

?>
