<?php

$shortopts = "";
$longopts = array(
    "video:",
    "output:",
    "intro:",
    "outro:",
    "fadeDuration:",

);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$intro = isset($options['intro']) ? $options['intro'] : false;
$outro = isset($options['outro']) ? $options['outro'] : false;
$fadeDuration = isset($options['fadeDuration']) ? $options['fadeDuration'] : 0.5;


if (empty($video)) {
    help("Do not set option --video ");
    exit(1);
}
if (empty($output)) {
    help("Do not set option --output ");
    exit(1);
}
if (empty($intro) and empty($outro)) {
    help("Do not set one or both options --intro / --outro");
    exit(1);
}


//$fadeDuration = 0.5;
$processing = new Processing();
$processing->writeToLog("Info: Script started");

$videoInfo = $processing->getVideoInfo($video);
$audioInfo = $processing->getAudioInfo($video);
$width = $videoInfo['streams'][0]['width'];
$height = $videoInfo['streams'][0]['height'];
$duration = $processing->getMediaDuration($video);
$fps = empty($videoInfo['streams'][0]['r_frame_rate']) ? 60 : $videoInfo['streams'][0]['r_frame_rate'];
$fps = eval("return round($fps,3);");
if ($fps > 60) {
    $fps = 60;
}

// if file haven't audio stream
$audioFilter = "[0:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($duration - $fadeDuration) . ":duration=$fadeDuration [a_video];";
if (empty($audioInfo['streams'][0]['codec_name'])) {
    $audioFilter = "anullsrc=r=48000:cl=stereo:d=$duration [a_video];";
}

if ($intro and !$outro) {

    if ($intro) {
        $introDuration = $processing->getMediaDuration($intro);
        $introAudioInfo = $processing->getAudioInfo($intro);
        $audioIntroFilter = "[1:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($introDuration - $fadeDuration) . ":duration=$fadeDuration [a_intro];";
        if (empty($introAudioInfo['streams'][0]['codec_name'])) {
            $audioIntroFilter = "anullsrc=r=48000:cl=stereo:d=$introDuration [a_intro];";
        }
    }

    if ($outro) {
        $outroDuration = $processing->getMediaDuration($outro);
        $outroAudioInfo = $processing->getAudioInfo($outro);
        $audioOutroFilter = "[1:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($outroDuration - $fadeDuration) . ":duration=$fadeDuration [a_outro];";
        if (empty($outroAudioInfo['streams'][0]['codec_name'])) {
            $audioOutroFilter = "anullsrc=r=48000:cl=stereo:d=$outroDuration [a_outro];";
        }
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\"",
        "-i \"$intro\"",
        "-filter_complex \"",
        "[0:v] fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($duration - $fadeDuration) . ":duration=$fadeDuration [video];",
        "[1:v] scale=w=$width:h=$height, setsar=1, fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($introDuration - $fadeDuration) . ":duration=$fadeDuration [intro];",
        $audioFilter,
        $audioIntroFilter,

        "[intro][a_intro][video][a_video]concat=n=2:v=1:a=1",
        "\"",
        "-c:v h264 -preset veryfast -crf 18 -pix_fmt yuv420p",
        "-c:a aac -b:a 128k -ac 2 -ar 48000",
        "-r $fps",
        "-g 180 -keyint_min 180",
        "-movflags faststart",
        "\"$output\"",

    ));
}

if ($intro and $outro) {

    if ($intro) {
        $introDuration = $processing->getMediaDuration($intro);
        $introAudioInfo = $processing->getAudioInfo($intro);
        $audioIntroFilter = "[1:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($introDuration - $fadeDuration) . ":duration=$fadeDuration [a_intro];";
        if (empty($introAudioInfo['streams'][0]['codec_name'])) {
            $audioIntroFilter = "anullsrc=r=48000:cl=stereo:d=$introDuration [a_intro];";
        }
    }

    if ($outro) {
        $outroDuration = $processing->getMediaDuration($outro);
        $outroAudioInfo = $processing->getAudioInfo($outro);
        $audioOutroFilter = "[2:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($outroDuration - $fadeDuration) . ":duration=$fadeDuration [a_outro];";
        if (empty($outroAudioInfo['streams'][0]['codec_name'])) {
          $audioOutroFilter = "anullsrc=r=48000:cl=stereo:d=$outroDuration [a_outro];";
        }
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\"",
        "-i \"$intro\"",
        "-i \"$outro\"",
        "-filter_complex \"",
        "[0:v] fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($duration - $fadeDuration) . ":duration=$fadeDuration [video];",
        "[1:v] scale=w=$width:h=$height, setsar=1, fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($introDuration - $fadeDuration) . ":duration=$fadeDuration [intro];",
        "[2:v] scale=w=$width:h=$height, setsar=1, fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($outroDuration - $fadeDuration) . ":duration=$fadeDuration [outro];",
        $audioFilter,
        $audioIntroFilter,
        $audioOutroFilter,

        "[intro][a_intro][video][a_video][outro][a_outro]concat=n=3:v=1:a=1",
        "\"",
        "-c:v h264 -preset veryfast -crf 18 -pix_fmt yuv420p",
        "-c:a aac -b:a 128k -ac 2 -ar 48000",
        "-r $fps",
        "-g 180 -keyint_min 180",
        "-movflags faststart",
        "\"$output\"",

    ));
}

if (!$intro and $outro) {

    if ($intro) {
        $introDuration = $processing->getMediaDuration($intro);
        $introAudioInfo = $processing->getAudioInfo($intro);
        $audioIntroFilter = "[1:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($introDuration - $fadeDuration) . ":duration=$fadeDuration [a_intro];";
        if (empty($introAudioInfo['streams'][0]['codec_name'])) {
            $audioIntroFilter = "anullsrc=r=48000:cl=stereo:d=$introDuration [a_intro];";
        }
    }

    if ($outro) {
        $outroDuration = $processing->getMediaDuration($outro);
        $outroAudioInfo = $processing->getAudioInfo($outro);
        $audioOutroFilter = "[1:a] afade=in:st=0:duration=$fadeDuration, afade=out:st=" . ($outroDuration - $fadeDuration) . ":duration=$fadeDuration [a_outro];";
        if (empty($outroAudioInfo['streams'][0]['codec_name'])) {
            $audioOutroFilter = "anullsrc=r=48000:cl=stereo:d=$outroDuration [a_outro];";
        }
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-i \"$video\"",
        "-i \"$outro\"",
        "-filter_complex \"",
        "[0:v] fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($duration - $fadeDuration) . ":duration=$fadeDuration [video];",
        "[1:v] scale=w=$width:h=$height, setsar=1, fade=in:st=0:duration=$fadeDuration, fade=out:st=" . ($outroDuration - $fadeDuration) . ":duration=$fadeDuration [outro];",
        $audioFilter,
        $audioOutroFilter,

        "[video][a_video][outro][a_outro]concat=n=2:v=1:a=1",
        "\"",
        "-c:v h264 -preset veryfast -crf 18 -pix_fmt yuv420p",
        "-c:a aac -b:a 128k -ac 2 -ar 48000",
        "-r $fps",
        "-g 180 -keyint_min 180",
        "-movflags faststart",
        "\"$output\"",

    ));
}

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

    $message =
        "$msg
        Script stitch main video with intro and outro files
	Usage: php $script --video /path/video.mp4 --output /path/output.mp4  [--intro /path/intro.mp4] [--outro /path/outro.mp4] [--fadeDuration 0.5]
	where:
  --video  path to main video file
  --output  path to output file
  --intro  path to intro video file, Please define one of intro or outro or both
  --outro  path to outro video file, Please define one of intro or outro or both
  --fadeDuration  duration of fade in the beginning and int the of intro/video/outro files. Optional. Default 0.5
  
  Example: php $script -video /path/video.mp4 --output /path/output.mp4  --intro /path/intro.mp4 --outro /path/outro.mp4 --fadeDuration 0.5\n
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

    public function getAudioInfo($input)
    {
        $ffprobe = $this->ffprobe;
        $cmd = "$ffprobe -v quiet -hide_banner -show_streams -select_streams a:0 -of json \"$input\"";
        //echo $cmd;
        $json = shell_exec($cmd);
        $out = json_decode($json, true);
        return ($out);
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
