<?php

$width = 1080; // scale video to this width
$height = 608; // scale video to this height

$output_width = 1080; // output size
$output_height = 1920; // output size

$fps=30;
$shortopts = "";
$longopts = array(
    "video:",
    "header:",
    "footer:",
    "output:",
);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$header = isset($options['header']) ? $options['header'] : false;
$footer = isset($options['footer']) ? $options['footer'] : false;
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

if ($header and !$footer) {

    $image_height = $output_height - $height;
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-i \"$header\"  -ss 0 -t $duration",
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [v0];",
        //"[1:v] scale=w=$width:h=${image_height} [v1];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${width}):h=max(${image_height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${image_height}, setsar=1 [v1];",
        "[bg][v0] overlay=x=0:y=${image_height} [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=0 [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-r $fps",
        "\"$output\"",

    ));
}

if (!$header and $footer) {
    $image_height = $output_height - $height;
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-i \"$footer\"  -ss 0 -t $duration",
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [v0];",
        //"[1:v] scale=w=$width:h=${image_height} [v1];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${width}):h=max(${image_height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${image_height}, setsar=1 [v1];",
        "[bg][v0] overlay=x=0:y=0 [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=${height} [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-r $fps",
        "\"$output\"",

    ));
}

if ($header and $footer) {
    $image_height = round(($output_height - $height) / 2);
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-i \"$header\"  -ss 0 -t $duration",
        "-i \"$footer\"  -ss 0 -t $duration",
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [v0];",
        //"[1:v] scale=w=$width:h=${image_height} [v1];",
        //"[2:v] scale=w=$width:h=${image_height} [v2];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${width}):h=max(${image_height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${image_height}, setsar=1 [v1];",
        "[2:v] scale=w=max(iw*${image_height}/ih\,${width}):h=max(${image_height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${image_height}, setsar=1 [v2];",
        "[bg][v0] overlay=x=0:y=${image_height} [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=0 [bg_v1];",
        "[bg_v1][v2] overlay=x=0:y=H-h [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-r $fps",
        "\"$output\"",

    ));
}

if (!$header and !$footer) {
    $height=$output_height;
    $width=$output_width;
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\" -ss 0 -t $duration",
        "-filter_complex \"",
        "[0:v] split=2 [video0-1][video0-2];", 
        "[video0-1] scale=w=${output_width}:h=${output_height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
        //"[video0-2] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [video0-2-scaled];",
        "[video0-2]  scale=w=min(iw*${height}/ih\,${width}):h=min(${height}\,ih*${width}/iw), setsar=1, crop=w='min(${width},iw)':h='min(${height},ih)', setsar=1 [video0-2-scaled];",
        "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2  [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
        "-r $fps",
        "\"$output\"",

    ));
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
	Usage: php $script --video /path/video.mp4   --output /path/output.mp4 [--header /path/header.png] [--footer /path/footer.png]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional

	Example: php $script --video /path/video.mp4 --header /path/header.png --output /path/output.mp4
	Example: php $script --video http://domain/video.mp4 --header http://domain/header.png --output /path/output.mp4    \n";
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
