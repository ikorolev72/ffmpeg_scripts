<?php

$width = 1280; // scale video to this width
$height = 720; // scale video to this height

$output_width = 1920; // output size
$output_height = 1080; // output size

$fps = 30;
$shortopts = "";
$longopts = array(
    "header:",
    "footer:",
    "output:",
    "dir:",
    "mask:",
    "loop:",
    "fps:",
);
$options = getopt($shortopts, $longopts);
$header = isset($options['header']) ? $options['header'] : false;
$footer = isset($options['footer']) ? $options['footer'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$dir = isset($options['dir']) ? $options['dir'] : false;
$mask = isset($options['mask']) ? $options['mask'] : "%2d.jpg";
$loop = isset($options['loop']) ? $options['loop'] : 1;
$fps = isset($options['fps']) ? $options['fps'] : 5;

if (empty($dir)) {
    help("Do not set option --dir ");
    exit(1);
}

if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

if (strtolower(pathinfo($output, PATHINFO_EXTENSION)) != "gif") {
    help("Output file defined with --output must be gif file ( eg output.gif )");
    exit(1);
}

$processing = new Processing();

$tmpFile = $output . time() . ".gif";
$tmpFileMp4 = $output . time() . ".mp4";

if ($header and !$footer) {
    $image_height = round(($output_height - $height) / 2);    
    //$image_height = $output_height - $height;

    $cmd = $processing->prepareImagesToGif($dir, $mask, $fps, $loop, $tmpFile);
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);        
        exit(1);
    }
    $duration = $processing->getMediaDuration($tmpFile);

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-i \"$header\"  -ss 0 -t $duration",
        "-i \"$tmpFile\" -ss 0 -t $duration",        
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] null [video0-1];",
        "[2:v] null [video0-2];",
        "[video0-1] scale=w=${output_width}:h=${height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
        "[video0-2] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [video0-2-scaled];",
        "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2 [v0];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${output_width}):h=max(${image_height}\,ih*${output_width}/iw), setsar=1, crop=w=${output_width}:h=${image_height}, setsar=1 [v1];",
        "[bg][v0] overlay=x=0:y=${image_height} [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=0 [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-an",
        "-r $fps",
        //"-loop -1",
        "\"$tmpFileMp4\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFileMp4\"",
        "-filter_complex \"",
        "[0:v] split=2 [x1] [x2];[x1] palettegen [p];[x2][p] paletteuse [v]\"",
        "-map \"[v]\" ",
        "-threads 1",
        "-an",
        "-r $fps",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }    
}

if (!$header and $footer) {
    $image_height = round(($output_height - $height) / 2);    
    //$image_height = $output_height - $height;
    $cmd = $processing->prepareImagesToGif($dir, $mask, $fps, $loop, $tmpFile);
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);        
        exit(1);
    }
    $duration = $processing->getMediaDuration($tmpFile);

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-i \"$footer\"  -ss 0 -t $duration",
        "-i \"$tmpFile\" -ss 0 -t $duration",        
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] null [video0-1];",
        "[2:v] null [video0-2];",
        "[video0-1] scale=w=${output_width}:h=${height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
        "[video0-2] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1, crop=w=${width}:h=${height}, setsar=1 [video0-2-scaled];",
        "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2 [v0];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${output_width}):h=max(${image_height}\,ih*${output_width}/iw), setsar=1, crop=w=${output_width}:h=${image_height}, setsar=1 [v1];",
        "[bg][v0] overlay=x=0:y=(H-h)/2 [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=H-h [v]\"",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-an",
        "-r $fps",
        //"-loop -1",
        "\"$tmpFileMp4\"",
    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFileMp4\"",
        "-filter_complex \"",
        "[0:v] split=2 [x1] [x2];[x1] palettegen [p];[x2][p] paletteuse [v]\"",
        "-map \"[v]\" ",
        "-threads 1",
        "-an",
        "-r $fps",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }
}

if ($header and $footer) {
    $image_height = round(($output_height - $height) / 2);
    $cmd = $processing->prepareImagesToGif($dir, $mask, $fps, $loop, $tmpFile);
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        exit(1);
    }
    $duration = $processing->getMediaDuration($tmpFile);
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-i \"$header\"  -ss 0 -t $duration",
        "-i \"$footer\"  -ss 0 -t $duration",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-filter_complex \"",
        "color=s=${output_width}x${output_height}:r=$fps:color=black [bg];",
        "[0:v] null [video0-1];",
        "[3:v] null [video0-2];",
        "[video0-1] scale=w=${output_width}:h=${height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
        "[video0-2] scale=w=max(iw*${height}/ih\,${width}):h=max(${height}\,ih*${width}/iw), setsar=1,  crop=w='min(${width},iw)':h='min(${height},ih)', setsar=1 [video0-2-scaled];",
        "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2 [v0];",
        "[1:v] scale=w=max(iw*${image_height}/ih\,${output_width}):h=max(${image_height}\,ih*${output_width}/iw), setsar=1, crop=w=${output_width}:h=${image_height}, setsar=1 [v1];",
        "[2:v] scale=w=max(iw*${image_height}/ih\,${output_width}):h=max(${image_height}\,ih*${output_width}/iw), setsar=1, crop=w=${output_width}:h=${image_height}, setsar=1 [v2];",
        "[bg][v0] overlay=x=0:y=${image_height} [bg_v0];",
        "[bg_v0][v1] overlay=x=0:y=0 [bg_v1];",
        "[bg_v1][v2] overlay=x=0:y=H-h [v]\"",
         "-map \"[v]\" ",
        "-an",
        "-r $fps",
        "\"$tmpFileMp4\"",

    ));
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFileMp4\"",
        "-filter_complex \"",
        "[0:v] split=2 [x1] [x2];[x1] palettegen [p];[x2][p] paletteuse [v]\"",
        "-map \"[v]\" ",
        "-an",
        "-r $fps",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }
}

if (!$header and !$footer) {
    $height = $output_height;
    $width = $output_width;
    $cmd = $processing->prepareImagesToGif($dir, $mask, $fps, $loop, $tmpFile);
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        exit(1);
    }
    $duration = $processing->getMediaDuration($tmpFile);
    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-i \"$tmpFile\" -ss 0 -t $duration",
        "-filter_complex \"",
        "[0:v] null [video0-1];",
        "[1:v] null [video0-2];",
        "[video0-1] scale=w=${output_width}:h=${height},boxblur=luma_radius=min(h\,w)/20:luma_power=1:chroma_radius=min(cw\,ch)/20:chroma_power=1, setsar=1  [bg0];",
        "[video0-2] scale=w=min(iw*${height}/ih\,${width}):h=min(${height}\,ih*${width}/iw), setsar=1,  crop=w='min(${width},iw)':h='min(${height},ih)', setsar=1 [video0-2-scaled];",
        "[bg0][video0-2-scaled] overlay=x=(W-w)/2:y=(H-h)/2 [v]\"",
         "-map \"[v]\" ",
        "-an",
        "-r $fps",
        "\"$tmpFileMp4\"",

    ));
    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$tmpFileMp4\"",
        "-filter_complex \"",
        "[0:v] split=2 [x1] [x2];[x1] palettegen [p];[x2][p] paletteuse [v]\"",
        "-map \"[v]\" ",
        "-an",
        "-r $fps",
        "\"$output\"",

    ));

    $processing->writeToLog("Info: prepared ffmpeg command : $cmd");
    if (!$processing->doExec($cmd)) {
        $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
        @unlink($tmpFile);
        @unlink($tmpFileMp4);
        exit(1);
    }
}

@unlink($tmpFile);
@unlink($tmpFileMp4);
$processing->writeToLog("Info: output video file : $output");
$processing->writeToLog("Info: Script finished");
exit(0);

function help($msg)
{
    $script = basename(__FILE__);
    $date = date("Y-m-d H:i:s");
    $message =

    $message =
        "$msg
        Script create gif from banch of images with header and footer.
	Usage: php $script --dir /path/images --output /path/output.gif [--mask img%3d.png] [--loop 2] [--fps 10] [--header /path/header.png] [--footer /path/footer.png]
	where:
    --output  path to output file
    --dir  directory ( or url ) with images
    --mask  mask of images. Optional. Default : '%2d.jpg'
    --loop  play video in the loop. Optionla. Default : 1
    --fps  input FPS ( frames per second ). Optional. Default : 5
    --header  path ( or url ) of  header image. Optional
    --footer  path ( or url ) of footer image. Optional
	Example: php $script --dir ./img --mask %2d.jpg --loop 3 --fps 12 --output output.gif\n
	Example: php $script --header http://domain/header.png  --dir http://domain/path --mask %2d.jpg --loop 3 --fps 5 --output output.gif\n";

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

    public function prepareImagesToGif($dir, $mask, $fps, $loop, $output)
    {
        if (intval($loop > 1)) {
            $loopOption = "-stream_loop " . intval($loop - 1);
        }
        $cmd = join(" ", array(
            $this->ffmpeg,
            "-y  -probesize 100M -analyzeduration 50M",
            "-loglevel " . $this->ffmpegLogLevel,
            //$loopOption,
            "-r $fps",
            "-i \"$dir/$mask\"",
            "-vf \"scale=w=iw:h=-2,setsar=1,fps=$fps,split[x1][x2];[x1]palettegen[p];[x2][p]paletteuse\"",
            "-an",
            "-loop -1",
            "\"$output\"",

        ));
        return ($cmd);
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
