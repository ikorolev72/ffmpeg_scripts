# Ffmpeg php scripts 

## Several script for adding top, botom ( or both ) image, encode several images into video or gif. As video/image sources can be used local files or URLs

Scripts :

  + [image2video.php](#image2video) - convert images to video
  + [img2gif.php](#img2gif) - convert images to video 
  + [header_footer_horizontal.php](#header_footer_horizontal) - convert video to horizontal and add top, bottom or both images to video. If ommited header and footer images, then video converted to horizontal video.
  + [header_footer_square.php](#header_footer_square) - convert video to square and add top, bottom or both images to video. If ommited header and footer images, then video converted to square video.
  + [header_footer_vertical.php](#header_footer_vertical) - convert video to vertical and add top, bottom or both images to video. If ommited header and footer images, then video converted to vertical video.
  + [header_footer_horizontal_image2gif.php](#header_footer_horizontal_image2gif) - convert images to video ( with header and footer)
  + [progress_bar.php](#progress_bar) - add progress bar to video
  + [progress_bar_icon.php](#progress_bar_icon) - add progress bar with icon to video
  + [progress_bar_waveform.php](#progress_bar_icon) - add progress bar with waveform to video
  + [word_by_work.php](#word_by_work) - burn word-by-word srt subtitles to video
  + [mix_video_audio.php](#mix_video_audio) - mix audio stream (or two streams) with video 
  + [resize_video.php](#resize_video) - resize video to vertical (9/16), square(1/1) or horizontal(16/9)


## Installation
 Require:
  + php 7
  + ffmpeg 4

```
sudo apt-get install -y ffmpeg php
```  

## Usage

### image2video
```
        Script create video from banch of images.
        Usage: php image2video.php --dir /path/images --output /path/output.mp4 [--mask img%3d.png] [--loop 2] [--fps 10]
        where:
    --output  path to output file
    --dir  directory ( or url ) with images
    --mask  mask of images. Optional. Default : '%2d.jpg'
    --loop  play video in the loop. Optionla. Default : 1
    --fps  input FPS ( frames per second ). Optional. Default : 5
        Example: php image2video.php --dir ./img --mask %2d.jpg --loop 3 --fps 12 --output 2.mp4

        Example: php image2video.php --dir http://domain/path  --mask %2d.jpg --loop 3 --fps 12 --output 2.mp4
```

### img2gif
```
        Script create gif  from banch of images.
        Usage: php img2gif.php --dir /path/images --output /path/output.gif [--mask img%3d.png] [--loop 2] [--fps 10]
        where:
    --output  path to output file
    --dir  directory ( or url ) with images
    --mask  mask of images. Optional. Default : '%2d.jpg'
    --loop  play video in the loop. Optionla. Default : 1
    --fps  input FPS ( frames per second ). Optional. Default : 5
        Example: php img2gif.php --dir ./img --mask %2d.jpg --loop 3 --fps 12 --output output.gif

        Example: php img2gif.php --dir http://domain/path --mask %2d.jpg --loop 3 --fps 5 --output output.gif
```


### header_footer_horizontal
```
        Usage: php header_footer_horizontal.php --video /path/video.mp4   --output /path/output.mp4 [--header /path header.png] [--footer /path/footer.png]
        where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional

        Example: php header_footer_horizontal.php --video /path/video.mp4 --header /path/header.png --output /path/output.mp4
        Example: php header_footer_horizontal.php --video http://domain/video.mp4 --header http://domain/header.png --output /path/output.mp4
```



### header_footer_vertical
```
        Usage: php header_footer_vertical.php --video /path/video.mp4   --output /path/output.mp4 [--header /path/header.png] [--footer /path/footer.png]
        where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional

        Example: php header_footer_vertical.php --video /path/video.mp4 --header /path/header.png --output /path/output.mp4
        Example: php header_footer_vertical.php --video http://domain/video.mp4 --header http://domain/header.png --output /path/output.mp4
```



### header_footer_square
```
        Usage: php header_footer_square.php --video /path/video.mp4   --output /path/output.mp4 [--header /path/header.png] [--footer /path/footer.png]
        where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional

        Example: php header_footer_square.php --video /path/video.mp4 --header /path/header.png --output /path/output.mp4
        Example: php header_footer_square.php --video http://domain/video.mp4 --header http://domain/header.png --output /path/output.mp4
```

### header_footer_horizontal_image2gif
```
        Script create gif  from banch of images  with header and footer.
        Usage: php header_footer_horizontal_image2gif.php --dir /path/images --output /path/output.gif [--mask img%3d.png] [--loop 2] [--fps 10] [--hea
der /path/header.png] [--footer /path/footer.png]
        where:
    --output  path to output file
    --dir  directory ( or url ) with images
    --mask  mask of images. Optional. Default : '%2d.jpg'
    --loop  play video in the loop. Optionla. Default : 1
    --fps  input FPS ( frames per second ). Optional. Default : 5
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional
        Example: php header_footer_horizontal_image2gif.php --dir ./img --mask %2d.jpg --loop 3 --fps 12 --output output.gif

        Example: php header_footer_horizontal_image2gif.php --header http://domain/header.png  --dir http://domain/path --mask %2d.jpg --loop 3 --fps 5
 --output output.gif

```

### progress_bar
```
        Usage: php progress_bar.php --video /path/video.mp4   --output /path/output.mp4 [--bgcolor HTML_COLOR] [--barcolor HTML_COLOR]
        where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --bgcolor HTML_COLOR. Default #FFFFFF@0.3 Optional. Please note that here can be used alpha in color ( value followed by @ ) See color descriptio
n https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --barcolor HTML_COLOR. Default #000000 Optional. Please note that here can be used alpha in color ( value followed by @ ). See color description
https://ffmpeg.org/ffmpeg-utils.html#color-syntax

        Example: php progress_bar.php --video /path/video.mp4 --output /path/output.mp4  --bgcolor '#FFFFFF@0.9' --barcolor 'red@0.5'
```

### progress_bar_icon
```	
	Usage: php progress_bar_icon --video /path/video.mp4  --icon /path/icon.png --output /path/output.mp4  [--barcolor HTML_COLOR] [--iconwidth ICON_WIDTH] [--smooth 3] [--marginV 0.02] [--barWidthPart 0.85] [--barHeightPart 0.03] 
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --icon path ( or url ) of icon png file
    --iconwidth ICON_WIDTH . Optional. Resize icon to this width ( height will be adjust automaticaly). Default - 10% of video width. 
    --barcolor HTML_COLOR . Optional. Default #000000 . Please note that here can be used alpha in color ( value followed by @ ). See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --smooth smooth for output video. Optional. Default 3. good values 1-8, increase processing time!
    --marginV relative value for vertical bar box position ( depend of video height ). Optional. Default 0.02. biggest value - move box above. 0 - mean bottom
    --barWidthPart relative value for horizontal bar size ( depend of video weight ). Optional, Default 0.85
    --barHeightPart relative value for vertical bar size ( depend of video height ). Optional, Default 0.03

    
	Example: php progress_bar_icon.php --video 3321.mp4  --output output.mp4  --barcolor 'red@0.5' --icon icon_2.png --smooth 3 --marginV 0.0 --barWidthPart 1 --barHeightPart 0.07 
```

### progress_bar_waveform
```
	Usage: php progress_bar_waveform.php --video /path/video.mp4   --output /path/output.mp4 [--bgcolor HTML_COLOR] [--barcolor HTML_COLOR] [--wavecolor HTML_COLOR][--marginV 0.02] [--barWidthPart 0.85] [--barHeightPart 0.03][--barBgWidthPart 0.9] [--barHeightPart 0.1]  
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --bgcolor HTML_COLOR. Default #FFFFFF@0.3 Optional. Please note that here can be used alpha in color ( value followed by @ ) See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --barcolor HTML_COLOR. Default #000000 Optional. Please note that here can be used alpha in color ( value followed by @ ). See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --wavecolor HTML_COLOR. Default #000000 Optional. Please note that here can be used alpha in color ( value followed by @ ). See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --marginV relative value for vertical bar box position ( depend of video height ). Optional. Default 0.02. biggest value - move box above. 0 - mean bottom
    --barWidthPart relative value for horizontal bar size ( depend of video weight ). Optional, Default 0.85
    --barHeightPart relative value for vertical bar size ( depend of video height ). Optional, Default 0.03
    --barBgWidthPart  relative value for horizontal bar box size ( depend of video weight ). Optional, Default 0.9
    --barHeightPart relative value for vertical bar box size ( depend of video height ). Optional, Default 0.1

	Example: php progress_bar_waveform.php --video /path/video.mp4 --output /path/output.mp4  --bgcolor '#FFFFFF@0.9' --barcolor 'red@0.5' --wavecolor '#000000'
```

### word_by_work
```
        Script burn the SRT subtitles 'youtube style word by word' to video.
	Usage: php word_by_work.php --video /path/video.mp4 --output /path/output.mp4 --srt /path/subtitles.srt
    [--oneline ]
    [--Fontname "Arial" ]
    [--Fontsize 24 ]
    [--PrimaryColour "&Hffffff" ]
    [--SecondaryColour "&Hffffff" ]
    [--OutlineColour "&H000000" ]
    [--BackColour "&H000000" ]
    [--Bold ]
    [--Italic ]
    [--Underline ]
    [--StrikeOut ]
    [--ScaleX 100 ]
    [--ScaleY 100 ]
    [--Spacing 0 ]
    [--Angle 0 ]
    [--BorderStyle 4 ]
    [--Outline 0 ]
    [--Shadow 0 ]
    [--Alignment 1 ]
    [--MarginL 10 ]
    [--MarginR 10 ]
    [--MarginV 10 ]


    where:
    --output  path to output file
    --video  source video file
    --srt source srt subtitles
    --oneline show subtitles in one line


    --Fontname "Arial"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Fontsize 24  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --PrimaryColour "&Hffffff"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --SecondaryColour "&Hffffff"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --OutlineColour "&H000000"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --BackColour "&H000000"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Bold  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Italic  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Underline  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --StrikeOut  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --ScaleX 100  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --ScaleY 100  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Spacing 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Angle 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --BorderStyle 4  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Outline 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Shadow 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Alignment 1  - ASS optiion . 1 - left, 2- center, 3 - right
    --MarginL 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --MarginR 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --MarginV 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'

	Example: php word_by_work.php --video /path/video.mp4 --output /path/output.mp4 --srt /path/subtitles.srt
```

### mix_video_audio
```
Script mix one or two audio streams ( options --audio --speech ) and concat with video. If option --duration is longest than video, then video will be palying in the loop.
	Usage: php mix_video_audio.php --video /path/video.mp4   --output /path/output.mp4 {[--audio /path/audio.mp3] | [--speech /path/speech.mp3]} [--audio_volume 0.3][--speech_volume 1][--duration 10]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file. Required
    --audio  path ( or url ) of input audio file. Must be set one : audio or speech 
    --speech  path ( or url ) of input speech audio file. Must be set one : audio or speech 
    --audio_volume  volume of audio ( for background music good value will be 0.2-05 ). Optional. Default: 0.3
    --speech_volume  volume of speech ( for speech good value will be 1 ). Optional. Default: 1
    --duration  duration of output video. Option. If ommited, then used duration of video file


	Example: php mix_video_audio.php --video /path/video.mp4 --audio /path/audio.mp3 --speech /path/speech.mp3 --audio_volume 0.3 --speech_volume 1 --duration 10 --output /path/output.mp4
```

### resize_video
```
	Usage: php resize_video.php --video /path/video.mp4   --output /path/output.mp4 [--orientation vertical|square|horizontal] [--fix blur|crop|direct|pad] [--height HEIGTH]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --orientation   output video orientation : vertical (9/16), square(1/1) or horizontal(16/9). Optional. Default: horizontal
    --fix   How to fix output aspect ratio : blur, crop, direct, pad . Direct mean 'not respecting the aspect ratio'.  Optional. Default: pad
    --height   output height. Width will be calculated automatically. Optional. Default: 720


	Example: php resize_video.php --video VIDEO.mp4 --height 360 --orientation horizontal --output 360p_1.mp4 --fix crop
	Example: php resize_video.php --video VIDEO.mp4 --height 360 --orientation vertical --output 360p_2.mp4 --fix blur
	Example: php resize_video.php --video VIDEO.mp4 --height 360 --orientation square --output 360p_3.mp4 --fix pad 
```



##  Bugs
##  ------------

##  Licensing
  ---------
	GNU

  Contacts
  --------

     o korolev-ia [at] yandex.ru
     o http://www.unixpin.com



