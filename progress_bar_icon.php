<?php

$marginV=0.02; // relative value for vertical bar box position ( depend of video height ). biggest value - move box above. 0 - mean bottom
$barWidthPart=0.85; // relative value for horizontal bar size ( depend of video weight ).
$barHeightPart=0.03; // relative value for vertical bar size ( depend of video height ).
$smooth=3; // smooth for output video, good values 1-6, increase processing time!


$barBgHeightPart=0.1; // relative value for vertical bar box size ( depend of video height ).
$barBgWidthPart=0.9; //  relative value for horizontal bar box size ( depend of video weight ).


$shortopts = "";
$longopts = array(
    "video:",
    "bgcolor:",
    "barcolor:",
    "output:",
    "icon:",
    "iconwidth:",
    "marginV:",
    "barWidthPart:",
    "barHeightPart:",
    "barBgWidthPart:",
    "barBgHeightPart:",       
    "smooth:",
        
);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$bgcolor = isset($options['bgcolor']) ? $options['bgcolor'] : "#FFFFFF@0.7";
$barcolor = isset($options['barcolor']) ? $options['barcolor'] : "#000000";
$output = isset($options['output']) ? $options['output'] : false;
$icon = isset($options['icon']) ? $options['icon'] : false;
$iconwidth = isset($options['iconwidth']) ? $options['iconwidth'] : false;

$marginV = isset($options['marginV']) ? $options['marginV'] : $marginV;
$barWidthPart = isset($options['barWidthPart']) ? $options['barWidthPart'] : $barWidthPart;
$barHeightPart = isset($options['barHeightPart']) ? $options['barHeightPart'] : $barHeightPart;
$smooth = isset($options['smooth']) ? intval( $options['smooth']) : $smooth;


$barBgWidthPart = isset($options['barBgWidthPart']) ? $options['barBgWidthPart'] : $barBgWidthPart;
$barBgHeightPart = isset($options['barBgHeightPart']) ? $options['barBgHeightPart'] : $barBgHeightPart;


if (empty($video) ) {
    help("Do not set option --video");
    exit(1);
}


if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}


if (empty($icon)) {
    help("Do not set option --icon");
    exit(1);
}
if( $smooth >8 or $smooth<1 ) {
    help("Incorrect option value --smooth. Must be 1-8");
    exit(1);    
}
if( $marginV >0.99 or $marginV<0 ) {
    help("Incorrect option value --marginV. Must be 0-0.99");
    exit(1);    
}
if( $barWidthPart >1 or $barWidthPart<0 ) {
    help("Incorrect option value --barWidthPart. Must be 0-0.99");
    exit(1);    
}
if( $barHeightPart >1 or $barHeightPart<0 ) {
    help("Incorrect option value --barHeightPart. Must be 0-0.99");
    exit(1);    
}




$processing = new Processing();
$duration = $processing->getMediaDuration($video);
$videoInfo=$processing->getVideoInfo($video);
$fps = $videoInfo['streams'][0]['r_frame_rate'];
$barWidth = round( $videoInfo['streams'][0]['width']*$barWidthPart *$smooth) ;
$barHeight = round( $videoInfo['streams'][0]['height']*$barHeightPart*$smooth ) ;
$marginV=$smooth*$marginV;

$bgWidth = round( $videoInfo['streams'][0]['width']*$barBgWidthPart *$smooth) ;
$bgHeight = round( $videoInfo['streams'][0]['height']*$barBgHeightPart*$smooth ) ;

//$barBgWidthPart = isset($options['barBgWidthPart']) ? $options['barBgWidthPart'] : $barBgWidthPart;
//$barBgHeightPart = isset($options['barBgHeightPart']) ? $options['barBgHeightPart'] : $barBgHeightPart;



$fps = $videoInfo['streams'][0]['r_frame_rate'];
//$fps = eval( "return round($fps,3);") ;
$fps = $fps ? eval( "return round($fps,3);") : 30 ;


if( !$iconwidth ) {
    $iconwidth=round( $videoInfo['streams'][0]['width']*0.1 );
} 
$iconwidth=round( $iconwidth*$smooth );

    $cmd = join(" ", array(
        "ffmpeg4.4 -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-i \"$icon\" -ss 0 -t $duration",
        "-filter_complex \"",
        "[1:v] fps=fps=$fps, scale=h=-2:w=$iconwidth [icon];",
        "[0:v] fps=fps=$fps, scale=w=iw*$smooth:h=-2 [video];",
        "color=c=$bgcolor:s=${bgWidth}x${bgHeight}:duration=${duration}:r=$fps [bg];",        
        "color=c=$barcolor:s=10x10:duration=${duration}:r=$fps, scale=eval=frame:h=$barHeight:w=$iconwidth+($barWidth-$iconwidth)*t/$duration [bar];",         
        "[bg][bar] overlay=x=(W-$barWidth)/2:y=(H-h)/2 [video_bg];",
        "[video][video_bg] overlay=x=(W-$bgWidth)/2:y=H-h-H*$marginV+( h-$barHeight)/2 [bg_box_progress];",
        "[bg_box_progress][icon] overlay=x=(W-$barWidth)/2+($barWidth-$iconwidth)*t/$duration:y=H-h-H*$marginV-$barHeight, scale=iw/$smooth:h=-2\"",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-crf 18 -preset veryfast -c:v h264 -g " . round(2 * $fps) . " -keyint_min " . round(2 * $fps) ,
        "-r $fps -pix_fmt yuv420p",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    //exit;
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        exit(1);
    }

$processing->writeToLog("Info: output video file : $output");
$processing->writeToLog("Info: Script finished");
exit(0);

function help($msg)
{
    $script = basename(__FILE__);
    $date = date("Y-m-d H:i:s");
    $message =
        "$msg
	Usage: php $script --video /path/video.mp4  --icon /path/icon.png --output /path/output.mp4  [--barcolor HTML_COLOR] [--iconwidth ICON_WIDTH] [--smooth 3] [--marginV 0.02] [--barWidthPart 0.85] [--barHeightPart 0.03] [--barBgWidthPart 0.9] [--barBgHeightPart 0.1][--bgcolor HTML_COLOR]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --icon path ( or url ) of icon png file
    --iconwidth ICON_WIDTH . Optional. Resize icon to this width ( height will be adjust automaticaly). Default - 10% of video width. 
    --bgcolor HTML_COLOR. Default #FFFFFF@0.3 Optional. Please note that here can be used alpha in color ( value followed by @ ) See color description https://ffmpeg.org/      
    --barcolor HTML_COLOR . Optional. Default #000000 . Please note that here can be used alpha in color ( value followed by @ ). See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --smooth smooth for output video. Optional. Default 3. good values 1-8, increase processing time!
    --marginV relative value for vertical bar box position ( depend of video height ). Optional. Default 0.02. biggest value - move box above. 0 - mean bottom
    --barWidthPart relative value for horizontal bar size ( depend of video weight ). Optional, Default 0.85
    --barHeightPart relative value for vertical bar size ( depend of video height ). Optional, Default 0.03
    --barBgWidthPart  relative value for horizontal bar box size ( depend of video weight ). Optional, Default 0.9
    --barBgHeightPart relative value for vertical bar box size ( depend of video height ). Optional, Default 0.1
    
	Example: php $script --video 3321.mp4  --output output.mp4  --barcolor 'red@0.5' --bgcolor 'A12345@0.5' --icon icon_2.png --smooth 3 --marginV 0.0 --barWidthPart 1 --barHeightPart 0.07 --barBgWidthPart 0.85 --barBgHeightPart 0.05 ";
    $stderr = fopen('php://stderr', 'w');
    fwrite($stderr, "$date   $message" . PHP_EOL);
    fclose($stderr);
    exit(-1);
}

class Processing
{
    public function __construct($log = false, $debug = false, $ffmpeg = 'ffmpeg', $ffprobe = 'ffprobe', $ffmpegLogLevel = 'info', $tmpDir = 'tmp', $fps = 25)
    {
        $this->ffmpeg = $ffmpeg;
        $this->ffprobe = $ffprobe;
        $this->ffmpegLogLevel = $ffmpegLogLevel;
        $this->error = '';
        $this->debug = $debug;
        $this->log = $log; // absolute path to log file
        $this->tmpDir = $tmpDir;
        $this->tmpFiles = array();
        $this->fps = $fps;
        //var_dump($this, $fps);
    }

    public function getMediaInfo($input)
    {
        $ffprobe = $this->ffprobe;
        $cmd = "$ffprobe -v quiet -hide_banner -show_streams -show_format -of json \"$input\"";
        //echo $cmd;
        $json = shell_exec($cmd);
        $out = json_decode($json, true);
        return ($out);
    }

    public function getMediaDuration($input)
    {
        $info = $this->getMediaInfo($input);
        $duration = 0;

        if (isset($info['streams'][0]['tags']['DURATION'])) {
            $duration = $this->time2float($info['streams'][0]['tags']['DURATION']);
        }
        if (isset($info['format']['duration'])) {
            $duration = floatval($info['format']['duration']);
        }
        if (isset($info['streams'][0]['duration'])) {
            $duration = floatval($info['streams'][0]['duration']);
        }
        return ($duration);
    }

    public function getVideoInfo($input)
    {
        $ffprobe = $this->ffprobe;
        $cmd = "$ffprobe -v quiet -hide_banner -show_streams -select_streams v:0 -of json \"$input\"";
        //echo $cmd;
        $json = shell_exec($cmd);
        $out = json_decode($json, true);
        return ($out);
    }

    public function writeToLog($message)
    {
        #echo "$message\n";
        $timeZone = date_default_timezone_get();
        date_default_timezone_set("UTC");
        $date = date("Y-m-d H:i:s");
        date_default_timezone_set($timeZone);
        $stderr = fopen('php://stderr', 'w');
        fwrite($stderr, "$date   $message" . PHP_EOL);
        fclose($stderr);

        if (!empty($this->log)) {
            file_put_contents($this->log, "$date   $message" . PHP_EOL, FILE_APPEND | LOCK_EX);
        }
    }    
    /**
     * doExec
     * @param    string    $Command
     * @return integer 0-error, 1-success
     */
    public function doExec($Command)
    {
        if ($this->debug) {
            print $Command . PHP_EOL;
            //return 1;
        }
        system($Command, $execResult);        
        if ($execResult) {
            return 0;
        }
        return 1;
    } 

    /**
     * time2float
     * this function translate time in format 00:00:00.00 to seconds
     *
     * @param    string $t
     * @return    float
     */
    public function time2float($t)
    {
        $matches = preg_split("/:/", $t, 3);
        if (array_key_exists(2, $matches)) {
            list($h, $m, $s) = $matches;
            return ($s + 60 * $m + 3600 * $h);
        }
        $h = 0;
        list($m, $s) = $matches;
        return ($s + 60 * $m);
    }
    
}