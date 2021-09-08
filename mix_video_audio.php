<?php

$fps = 30;
$shortopts = "";
$longopts = array(
    "video:",
    "audio:",
    "audio_volume:",
    "speech:",
    "speech_volume:",
    "duration:",
    "output:",
);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$audio = isset($options['audio']) ? $options['audio'] : false;
$speech = isset($options['speech']) ? $options['speech'] : false;
$duration = isset($options['duration']) ? $options['duration'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$audio_volume = isset($options['audio_volume']) ? $options['audio_volume'] : 0.3;
$speech_volume = isset($options['speech_volume']) ? $options['speech_volume'] : 1;

if (empty($video)) {
    help("Do not set option --video");
    exit(1);
}

if (empty($audio) && empty($speech)) {
    help("Do not set option --audio or --speech");
    exit(1);
}

if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

$processing = new Processing();
if (empty($duration)) {
    $duration = $processing->getMediaDuration($video);
    $processing->writeToLog("Warning: Do not set option '--duration' . Will use duration of video file");
}

if (empty($audio) || empty($speech)) { // for one audio source
    if (empty($audio)) {
        $audio = $speech;
        $audio_volume = $speech_volume;
    }

    $cmd = join(" ", array(
        "ffmpeg -y  -probesize 100M -analyzeduration 50M",
        "-stream_loop -1 -i \"$video\" -ss 0 -t $duration",
        "-i \"$audio\"  -ss 0 -t $duration",
        "-filter_complex \"",
        "[1:a] volume=${audio_volume}, afade=t=out:d=0.25:st=".($duration-0.25)." [a];",
        "[0:v] null [v]\"",
        "-map \"[a]\" -c:a aac -b:a 128k -ac 2 ",
        "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
        "-r $fps",
        "\"$output\"",

    ));
}

if ($audio && $speech ) { // for one audio source
  $cmd = join(" ", array(
      "ffmpeg -y  -probesize 100M -analyzeduration 50M",
      "-stream_loop -1 -i \"$video\" -ss 0 -t $duration",
      "-i \"$audio\"  -ss 0 -t $duration",
      "-i \"$speech\"  -ss 0 -t $duration",
      "-filter_complex \"",
      "[1:a] volume=${audio_volume} [fg_audio];",
      "[2:a] volume=${speech_volume} [bg_audio];",
      "[fg_audio][bg_audio] amix=inputs=2:duration=first, afade=t=out:d=0.25:st=".($duration-0.25)." [a];",
      "[0:v] null [v]\"",
      "-map \"[a]\" -c:a aac -b:a 128k -ac 2 ",
      "-map \"[v]\" -c:v h264 -preset veryfast -crf 18",
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
  Script mix one or two audio streams ( options --audio --speech ) and concat with video. If option --duration is longest than video, then video will be palying in the loop.
	Usage: php $script --video /path/video.mp4   --output /path/output.mp4 {[--audio /path/audio.mp3] | [--speech /path/speech.mp3]} [--audio_volume 0.3][--speech_volume 1][--duration 10]
	where:
    --output  path to output file
    --video  path ( or url ) of input video file. Required
    --audio  path ( or url ) of input audio file. Must be set one : audio or speech 
    --speech  path ( or url ) of input speech audio file. Must be set one : audio or speech 
    --audio_volume  volume of audio ( for background music good value will be 0.2-05 ). Optional. Default: 0.3
    --speech_volume  volume of speech ( for speech good value will be 1 ). Optional. Default: 1
    --duration  duration of output video. Option. If ommited, then used duration of video file


	Example: php $script --video /path/video.mp4 --audio /path/audio.mp3 --speech /path/speech.mp3 --audio_volume 0.3 --speech_volume 1 --duration 10 --output /path/output.mp4\n";
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

}
