# Ffmpeg php scripts 

## Several script for adding top, botom ( or both ) image, encode several images into video or gif. As video/image sources can be used local files or URLs

Scripts :
  + image2video.php - convert images to video
  + img2gif.php - convert images to video 
  + header_footer_horizontal.php - convert video to horizontal and add top, bottom or both images to video. If ommited header and footer images, then video converted to horizontal video.
  + header_footer_square.php - convert video to square and add top, bottom or both images to video. If ommited header and footer images, then video converted to square video.
  + header_footer_vertical.php - convert video to vertical and add top, bottom or both images to video. If ommited header and footer images, then video converted to vertical video.
  + header_footer_horizontal_image2gif.php - convert images to video ( with header and footer)
  + progress_bar.php - add progress bar to video
  + word_by_work.php - burn word-by-word srt subtitles to video 

## Installation
 Require:
  + php 7
  + ffmpeg 4

```
sudo apt-get install -y ffmpeg php
```  

## Usage

### image2video.php
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

### img2gif.php
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


### header_footer_horizontal.php
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



### header_footer_vertical.php
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



### header_footer_square.php
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

# header_footer_horizontal_image2gif.php
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

# progress_bar.php
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

# word_by_work.php
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



##  Bugs
##  ------------

##  Licensing
  ---------
	GNU

  Contacts
  --------

     o korolev-ia [at] yandex.ru
     o http://www.unixpin.com



