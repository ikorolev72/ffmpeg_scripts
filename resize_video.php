<?php

$shortopts = "";
$longopts = array(
    "video:",
    "output:",
    "height:",
    "orientation:",
    "fix:",

);
$options = getopt($shortopts, $longopts);

$video = isset($options['video']) ? $options['video'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$orientation = isset($options['orientation']) ? $options['orientation'] : 'horizontal'; // vertical, square, horizontal
$height = isset($options['height']) ? $options['height'] : 720;
$video = isset($options['video']) ? $options['video'] : false;
$fix = isset($options['fix']) ? $options['fix'] : "pad"; // pad, crop, direct, blur

if (empty($video)) {
    help("Do not set option --video");
    exit(1);
}

if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

$processing = new Processing();
$duration = $processing->getMediaDuration($video);

$height = $processing->roundToEven($height);
switch ($orientation) {
    case "vertical":
        $width = $processing->roundToEven($height * 9 / 16);
        break;
    case "square":
        $width = $height;
        break;
    default:
        $width = $processing->roundToEven($height * 16 / 9);
        break;
}

$videoInfo = $processing->getVideoInfo($video);
$fps = empty($videoInfo['streams'][0]['r_frame_rate']) ? 30 : $videoInfo['streams'][0]['r_frame_rate'];
if (eval('return ' . $fps . ';') > 60) {
    $fps = 60;
}

switch ($fix) {
    case "blur":
        $cmd = join(" ", array(
            "ffmpeg -y  -probesize 100M -analyzeduration 50M",
            "-i \"$video\" -ss 0 -t $duration",
            "-i \"$video\" -ss 0 -t $duration",
            "-filter_complex \"",
            "[0:v] null [video0-1];",
            "[1:v] null [video0-2];",
            "[video0-1] scale=w=${width}:h=${height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
            "[video0-2] scale='trunc(ih*dar/2)*2:trunc(ih/2)*2', scale=w=min(iw*${height}/ih\,${width}):h=min(${height}\,ih*${width}/iw), setsar=1 [video0-2-scaled];",
            "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2 [v]\"",
            "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
            "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
            "-r $fps",
            "\"$output\"",
        ));
        break;
    case "direct":
        $cmd = join(" ", array(
            "ffmpeg -y  -probesize 100M -analyzeduration 50M",
            "-i \"$video\" -ss 0 -t $duration",
            "-filter_complex \"",
            "[0:v] null [video0-1];",
            "[video0-1] scale='trunc(ih*dar/2)*2:trunc(ih/2)*2', scale=w=${width}:h=${height}, setsar=1 [v]\"",
            "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
            "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
            "-r $fps",
            "\"$output\"",
        ));
        break;
    case "crop":
        $cmd = join(" ", array(
            "ffmpeg -y  -probesize 100M -analyzeduration 50M",
            "-i \"$video\" -ss 0 -t $duration",
            "-filter_complex \"",
            "[0:v] null [video0-1];",
            "[video0-1] scale='trunc(ih*dar/2)*2:trunc(ih/2)*2', scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [v]\"",
            "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
            "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
            "-r $fps",
            "\"$output\"",
        ));
        break;
    default: // pad
        $cmd = join(" ", array(
            "ffmpeg -y  -probesize 100M -analyzeduration 50M",
            "-i \"$video\" -ss 0 -t $duration",
            "-filter_complex \"",
            "[0:v] null [video0-1];",
            "[video0-1] scale=w=min(iw*${height}/ih\,${width}):h=min(${height}\,ih*${width}/iw), pad=w=$width:h=$height:x=($width-iw)/2:y=($height-ih)/2,setsar=1 [v]\"",
            "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
            "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
            "-r $fps",
            "\"$output\"",
        ));

        break;
}

echo $cmd . PHP_EOL;
system($cmd, $retCode);
exit($retCode);

function help($msg)
{
    $script = basename(__FILE__);
    $date = date("Y-m-d H:i:s");
    $message =
        "$msg
	Usage: php $script --video /path/video.mp4   --output /path/output.mp4 [--orientation vertical|square|horizontal] [--fix blur|crop|direct|pad] [--height HEIGTH]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --orientation   output video orientation : vertical (9/16), square(1/1) or horizontal(16/9). Optional. Default: horizontal
    --fix   How to fix output aspect ratio : blur, crop, direct, pad . Direct mean 'not respecting the aspect ratio'.  Optional. Default: pad
    --height   output height. Width will be calculated automatically. Optional. Default: 720


	Example: php $script --video VIDEO.mp4 --height 360 --orientation horizontal --output 360p_1.mp4 --fix crop
	Example: php $script --video VIDEO.mp4 --height 360 --orientation vertical --output 360p_2.mp4 --fix blur
	Example: php $script --video VIDEO.mp4 --height 360 --orientation square --output 360p_3.mp4 --fix pad \n";
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
