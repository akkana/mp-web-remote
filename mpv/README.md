# Tips for mpv

## Config File

You can create a config file in ~/.config/mpv/mpv.conf

I recommend the following options:

```
save-position-on-quit
watch-later-options=vid,aid,sid,volume,start,speed,sub-visibility
write-filename-in-watch-later-config

osd-font-size=125
```

The first three lines try to make it more likely that mpv will save its
position in each video file it plays.
mp-web-remote will try to save position anyway, via a command-line argument
to mpv, but this isn't always reliable and the mpv.conf lines work better.

The last line controls the font size for the on-screen-display,
e.g. when you delete a file. I find 125 works pretty well on a 1080p TV.
