<?php


$fps=30;
$shortopts = "";
$longopts = array(
    "dir:",
    "mask:",
    "loop:",
    "fps:",
    "output:",
);
$options = getopt($shortopts, $longopts);
$dir = isset($options['dir']) ? $options['dir'] : false;
$mask = isset($options['mask']) ? $options['mask'] : "%2d.jpg";
$loop = isset($options['loop']) ? $options['loop'] : 1;
$fps = isset($options['fps']) ? $options['fps'] : 5;
$output = isset($options['output']) ? $options['output'] : false;

if (empty($dir) ) {
    help("Do not set option --dir ");
    exit(1);
}


if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

if (strtolower( pathinfo($output, PATHINFO_EXTENSION)) !="gif") {
  help("Output file defined with --output must be gif file ( eg output.gif )");
  exit(1);
}



$processing = new Processing();

$processing->writeToLog( "Info: Script started");
$cmd=$processing->prepareImageToVideo( $dir, $mask, $fps, $loop, $output);

$processing->writeToLog( "Info: prepared ffmpeg command : $cmd");
if( !$processing->doExec($cmd) ) {
  $processing->writeToLog( "Error: cannot execute ffmpeg command : $cmd");
}
$processing->writeToLog( "Info: output video file : $output");
$processing->writeToLog( "Info: Script finished");

exit(0);

function help($msg)
{
    $script = basename(__FILE__);
    $date = date("Y-m-d H:i:s");
    $message =
        "$msg
        Script create gif  from banch of images.
	Usage: php $script --dir /path/images --output /path/output.gif [--mask img%3d.png] [--loop 2] [--fps 10]  
	where:
    --output  path to output file
    --dir  directory ( or url ) with images
    --mask  mask of images. Optional. Default : '%2d.jpg'
    --loop  play video in the loop. Optionla. Default : 1
    --fps  input FPS ( frames per second ). Optional. Default : 5
	Example: php $script --dir ./img --mask %2d.jpg --loop 3 --fps 12 --output output.gif\n
	Example: php $script --dir http://domain/path --mask %2d.jpg --loop 3 --fps 5 --output output.gif\n";
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

    public function prepareImageToVideo( $dir, $mask, $fps, $loop, $output) {
      if( intval( $loop>1 ) ) {
        $loopOption="-stream_loop ".intval( $loop -1 );
      }
      $cmd=join( " ",array(
        $this->ffmpeg,
        "-y  -probesize 100M -analyzeduration 50M",
        "-loglevel ".$this->ffmpegLogLevel,
        //$loopOption,
        "-r $fps",
        "-i \"$dir/$mask\"",
        "-vf \"scale=w=iw:h=-2,setsar=1,fps=$fps,split[x1][x2];[x1]palettegen[p];[x2][p]paletteuse\""    ,
        "-an",        
        "-loop -1",
        "\"$output\"",        

      )) ;
      return($cmd);
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
