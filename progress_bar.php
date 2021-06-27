<?php

$marginV=0.02; // relative value for vertical bar box position ( depend of video height ). biggest value - move box above. 0 - mean bottom

$barBgWidthPart=0.9; //  relative value for horizontal bar box size ( depend of video weight ).
$barWidthPart=0.85; // relative value for horizontal bar size ( depend of video weight ).

$barBgHeightPart=0.1; // relative value for vertical bar box size ( depend of video height ).
$barHeightPart=0.03; // relative value for vertical bar size ( depend of video height ).




$shortopts = "";
$longopts = array(
    "video:",
    "bgcolor:",
    "barcolor:",
    "output:",
);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$bgcolor = isset($options['bgcolor']) ? $options['bgcolor'] : "#FFFFFF@0.7";
$barcolor = isset($options['barcolor']) ? $options['barcolor'] : "#000000";
$output = isset($options['output']) ? $options['output'] : false;

if (empty($video) ) {
    help("Do not set option --video");
    exit(1);
}


if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}


$processing = new Processing();
$duration = $processing->getMediaDuration($video);
$videoInfo=$processing->getVideoInfo($video);
$fps = $videoInfo['streams'][0]['r_frame_rate'];
$bgWidth = round( $videoInfo['streams'][0]['width']*$barBgWidthPart ) ;
$bgHeight = round( $videoInfo['streams'][0]['height']*$barBgHeightPart) ;
$barWidth = round( $videoInfo['streams'][0]['width']*$barWidthPart ) ;
$barHeight = round( $videoInfo['streams'][0]['height']*$barHeightPart ) ;
$marginL=(1-$barBgWidthPart)/2 ; //  relative value for horizontal bar box position ( depend of video weight ). biggest value - move box to left

//$barHeight = 50 ;


$fps = $videoInfo['streams'][0]['r_frame_rate'];

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-filter_complex \"",
        "color=c=$bgcolor:s=${bgWidth}x${bgHeight}:duration=${duration} [bg];", 
        "color=c=$barcolor:s=10x10:duration=${duration}, scale=eval=frame:h=$barHeight:w=1+$barWidth*t/$duration, setsar=1 [bar];", 
        "[bg][bar] overlay=x=($bgWidth-$barWidth)/2:y=(H-h)/2 [bg_box];",
        "[0:v][bg_box] overlay=x=W*$marginL:y=H-h-H*$marginV\"",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-r $fps",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
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
	Usage: php $script --video /path/video.mp4   --output /path/output.mp4 [--bgcolor HTML_COLOR] [--barcolor HTML_COLOR]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --bgcolor HTML_COLOR. Default #FFFFFF@0.3 Optional. Please note that here can be used alpha in color ( value followed by @ ) See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax
    --barcolor HTML_COLOR. Default #000000 Optional. Please note that here can be used alpha in color ( value followed by @ ). See color description https://ffmpeg.org/ffmpeg-utils.html#color-syntax

	Example: php $script --video /path/video.mp4 --output /path/output.mp4  --bgcolor '#FFFFFF@0.9' --barcolor 'red@0.5'";
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
    
}
