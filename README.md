# Ffmpeg php scripts 

## Several script for adding top, botom ( or both ) image, encode several images into video or gif. As video/image sources can be used local files or URLs

Scripts :
  + image2video.php - convert images to video
  + img2gif.php - convert images to video 
  + header_footer_horizontal.php - convert video to horizontal and add top, bottom or both images to video. If ommited header and footer images, then video converted to horizontal video.
  + header_footer_square.php - convert video to square and add top, bottom or both images to video. If ommited header and footer images, then video converted to square video.
  + header_footer_vertical.php - convert video to vertical and add top, bottom or both images to video. If ommited header and footer images, then video converted to vertical video.


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


##  Bugs
##  ------------

##  Licensing
  ---------
	GNU

  Contacts
  --------

     o korolev-ia [at] yandex.ru
     o http://www.unixpin.com



