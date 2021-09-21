<?php

$shortopts = "";
$longopts = array(
    "video:",
    "output:",
    "image:",
    "imageV:",
    "alignment:",
    "marginV:",
    "marginH:",

);
$options = getopt($shortopts, $longopts);

$video = isset($options['video']) ? $options['video'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$image = isset($options['image']) ? $options['image'] : false;
$alignment = isset($options['alignment']) ? $options['alignment'] : 'top_left';
$marginV = isset($options['marginV']) ? $options['marginV'] : 0.04; // relative value for vertical image alignment  ( depend of video height ).
$marginH = isset($options['marginH']) ? $options['marginH'] : 0.04; // relative value for horizontal image alignment  ( depend of video width ).
$imageV = isset($options['imageV']) ? $options['imageV'] : 0.1; // relative value for resize image . eg 0.1 mean - resize to 0.1*video_height, for 1080p - overlay image size will be 108.

if (empty($video)) {
    help("Do not set option --video");
    exit(1);
}
if (empty($image)) {
    help("Do not set option --image");
    exit(1);
}
if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

$processing = new Processing();
$duration = $processing->getMediaDuration($video);
if (empty($duration)) {
    help("Error: cannot get the video duration for file '$video'");
    exit(1);
}

switch ($alignment) {
    case "bottom_right":
        $x = "(W-w-($marginH*W))";
        $y = "(H-h-($marginV*H))";
        break;
    case "bottom_left":
        $x = "($marginH*W)";
        $y = "(H-h-($marginV*H))";
        break;
    case "top_right":
        $x = "(W-w-($marginH*W))";
        $y = "($marginV*H)";
        break;
    default: // top_left
        $x = "($marginH*W)";
        $y = "($marginV*H)";
        break;
}
/*
var_dump( $options['alignment'],$alignment, $x, $y);
exit;
 */
$videoInfo = $processing->getVideoInfo($video);
$fps = empty($videoInfo['streams'][0]['r_frame_rate']) ? 30 : $videoInfo['streams'][0]['r_frame_rate'];
if (eval('return ' . $fps . ';') > 60) {
    $fps = 60;
}

$height = $videoInfo['streams'][0]['height'];
$imageHeight = round($height * $imageV);

$cmd = join(" ", array(
    "ffmpeg -y  -probesize 100M -analyzeduration 50M",
    "-i \"$video\" -ss 0 -t $duration",
    "-r $fps -i \"$image\" -ss 0 -t $duration",
    "-filter_complex \"",
    "[0:v] null [video];",
    "[1:v] scale=w=-2:h=${imageHeight}, setsar=1 [image];",
    "[video][image] overlay=x=$x:y=$y [v]\"",
    "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
    "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
    "-r $fps",
    "\"$output\"",
));

echo $cmd . PHP_EOL;
system($cmd, $retCode);
exit($retCode);

function help($msg)
{
    $script = basename(__FILE__);
    $date = date("Y-m-d H:i:s");
    $message =
        "$msg
	Usage: php $script --video /path/video.mp4  --image /path/image.png --output /path/output.mp4 [--alignment  top_left|top_right|bottom_left|bottom_right] [--imageV 0.1] [--marginV 0.04] [--marginH 0.04]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --image  path ( or url ) of overlay image
    --alignment    image alignment  : top_left, top_right, bottom_left, bottom_right. Optional. Default: top_left
    --imageV   relative value for resize image . eg 0.1 mean - resize to 0.1*video_height, for 1080p - overlay image height will be 108.  Optional. Default: 0.1
    --marginV relative value for vertical image margin  ( depend of video height ). Optional. Default: 0.04
    --marginH relative value for horizontal image margin  ( depend of video width ). Optional. Default: 0.04

	Example: 
    php $script --video VIDEO.mp4 --output overlay.mp4 --image logo.png
    php $script --video green1.MOV --output 1.mp4 --image logo.png --alignment bottom_left --imageV 0.2 --marginH 0.01    
    ";
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

    public function roundToEven($number)
    {
        $number = round($number);
        if ($number % 2 == 0) {
            return ($number);
        }
        return ($number + 1);
    }

}
