mp-web-remote
==================

A PHP tool for browsing files and playing videos using MPV (or mplayer).

Run on your server, and you can browse the files and control the
player with your mobile phone.

Original proof of concept by Josh Heidenreich (TheJosh) for mplayer,
heavily modified and switched to use mpv by Akkana Peck
because mpv has more consistent commands and is better at remembering
its position in files. Josh's version didn't quite work for me, so
I got it working, then stashed the working code in the subdirectory
called mplayer when I changed gears to work on the mpv version.
Currently, the mplayer version still has Josh's user interface,
though in theory, you could write a file mplayercommands.php with
the same commands in it as mpvcommands, swap it in and use mplayer
with the current UI (or the same for VLC or any other video player
that allows remote commands).

Requires packages: mpv php socat.
(On Debian, php pulls in apache2, which seems kind of annoying and
is not needed for this package.)

To run:

Create a file named `~/.config/mp-remote.ini` containing one line:

```
mediadir = /path/to/video/files
```

Then, as the user that's logged in to X and has permission for audio,
run the remote control as:

```
    php -S localhost:8000
```
to test locally (change the port to whatever you prefer), or
```
    php -S 0.0.0.0:8000
```
if you want it accessible to other machines, like if you want to use
your phone as a remote control.

I don't suggest you run this on a public server, for (hopefully) obvious reasons.

When shutting down, it tries to remember the file, position in the
file, and volume setting you were last using, so you can continue from
there when you start your next viewing session.
