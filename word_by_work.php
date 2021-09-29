<?php

$fps = 30;
$shortopts = "";
$longopts = array(
    "srt:",
    "video:",
    "output:",
    "oneline::",    

    "Fontname:",
    "Fontsize:",
    "PrimaryColour:",
    "SecondaryColour:",
    "OutlineColour:",
    "BackColour:",
    "Bold::",
    "Italic::",
    "Underline::",
    "StrikeOut::",
    "ScaleX:",
    "ScaleY:",
    "Spacing:",
    "Angle:",
    "BorderStyle:",
    "Outline:",
    "Shadow:",
    "Alignment:",
    "MarginL:",
    "MarginR:",
    "MarginV:",
);
$options = getopt($shortopts, $longopts);
$video = isset($options['video']) ? $options['video'] : false;
$output = isset($options['output']) ? $options['output'] : false;
$srt = isset($options['srt']) ? $options['srt'] : false;
$oneline = isset($options['oneline']) ? true : false;

$assOption['Fontname'] = isset($options['Fontname']) ? $options['Fontname'] : "Arial";
$assOption['Fontsize'] = isset($options['Fontsize']) ? $options['Fontsize'] : 24;
$assOption['PrimaryColour'] = isset($options['PrimaryColour']) ? $options['PrimaryColour'] : "&Hffffff";
$assOption['SecondaryColour'] = isset($options['SecondaryColour']) ? $options['SecondaryColour'] : "&Hffffff";
$assOption['OutlineColour'] = isset($options['OutlineColour']) ? $options['OutlineColour'] : "&H000000";
$assOption['BackColour'] = isset($options['BackColour']) ? $options['BackColour'] : "&H000000";
$assOption['Bold'] = isset($options['Bold']) ? -1 : 0;
$assOption['Italic'] = isset($options['Italic']) ? -1 : 0;
$assOption['Underline'] = isset($options['Underline']) ? -1 : 0;
$assOption['StrikeOut'] = isset($options['StrikeOut']) ? -1 : 0;
$assOption['ScaleX'] = isset($options['ScaleX']) ? $options['ScaleX'] : 100;
$assOption['ScaleY'] = isset($options['ScaleY']) ? $options['ScaleY'] : 100;
$assOption['Spacing'] = isset($options['Spacing']) ? $options['Spacing'] : 0;
$assOption['Angle'] = isset($options['Angle']) ? $options['Angle'] : 0;
$assOption['BorderStyle'] = isset($options['BorderStyle']) ? $options['BorderStyle'] : 4;
$assOption['Outline'] = isset($options['Outline']) ? $options['Outline'] : 0;
$assOption['Shadow'] = isset($options['Shadow']) ? $options['Shadow'] : 0;
$assOption['Alignment'] = isset($options['Alignment']) ? $options['Alignment'] : 1;
$assOption['MarginL'] = isset($options['MarginL']) ? $options['MarginL'] : 10;
$assOption['MarginR'] = isset($options['MarginR']) ? $options['MarginR'] : 10;
$assOption['MarginV'] = isset($options['MarginV']) ? $options['MarginV'] : 10;

if (empty($video)) {
    help("Do not set option --video");
    exit(1);
}

if (empty($output)) {
    help("Do not set option --output");
    exit(1);
}

if (empty($srt)) {
    help("Do not set option --srt");
    exit(1);
}

$processing = new Processing();

$tmpFile = $output . time() . "_1.ass";
$tmpFile2 = $output . time() . "_2.ass";

$cmd = join(" ", array(
    "ffmpeg -y",
    "-i \"$srt\"",
    "\"$tmpFile\"",
));

$processing->writeToLog("Info: prepared ffmpeg command : $cmd");
if (!$processing->doExec($cmd)) {
    @unlink($tmpFile);
    @unlink($tmpFile2);
    $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
    exit(1);
}

if (!$processing->splitSubtitlesWordByWord($tmpFile, $tmpFile2, $assOption, $oneline )) {
    @unlink($tmpFile);
    @unlink($tmpFile2);
    $processing->writeToLog("Error: cannot prepare fixed ass file");
    exit(1);
}

$cmd = join(" ", array(
    "ffmpeg -y  -probesize 100M -analyzeduration 50M",
    "-i \"$video\"",
    "-filter_complex \"",
    "subtitles=$tmpFile2\"",
    "-map 0:a? -c:a aac -ac 2 -ar 44100 -b:a 128k",
    "-c:v h264 -preset veryfast -crf 18",
    "-r $fps",
    "\"$output\"",

));

$processing->writeToLog("Info: prepared ffmpeg command : $cmd");
if (!$processing->doExec($cmd)) {
    @unlink($tmpFile);
    @unlink($tmpFile2);
    $processing->writeToLog("Error: cannot execute ffmpeg command : $cmd");
    exit(1);
}

@unlink($tmpFile);
@unlink($tmpFile2);

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
        Script burn the SRT subtitles 'youtube style word by word' to video.
	Usage: php $script --video /path/video.mp4 --output /path/output.mp4 --srt /path/subtitles.srt
    [--oneline ]

    [--Fontname \"Arial\" ]
    [--Fontsize 24 ]
    [--PrimaryColour \"&Hffffff\" ]
    [--SecondaryColour \"&Hffffff\" ]
    [--OutlineColour \"&H000000\" ]
    [--BackColour \"&H000000\" ]
    [--Bold ]
    [--Italic ]
    [--Underline ]
    [--StrikeOut ]
    [--ScaleX 100 ]
    [--ScaleY 100 ]
    [--Spacing 0 ]
    [--Angle 0 ]
    [--BorderStyle 4 ]
    [--Outline 0 ]
    [--Shadow 0 ]
    [--Alignment 1 ]
    [--MarginL 10 ]
    [--MarginR 10 ]
    [--MarginV 10 ]


    where:
    --output  path to output file
    --video  source video file
    --srt source srt subtitles
    --oneline show subtitles in one line


    --Fontname \"Arial\"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Fontsize 24  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --PrimaryColour \"&Hffffff\"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --SecondaryColour \"&Hffffff\"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --OutlineColour \"&H000000\"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --BackColour \"&H000000\"  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Bold  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Italic  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Underline  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --StrikeOut  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --ScaleX 100  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --ScaleY 100  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Spacing 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Angle 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --BorderStyle 4  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Outline 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Shadow 0  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --Alignment 1  - ASS optiion. 1 - left, 2- center, 3 - right
    --MarginL 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --MarginR 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'
    --MarginV 10  - ASS optiion see http://www.tcax.org/docs/ass-specs.htm Section 'Style lines, [v4 Styles] section'

	Example: php $script --video /path/video.mp4 --output /path/output.mp4 --srt /path/subtitles.srt\n";

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

    /*
     * date2unix
     * this function translate time in format 00:00:00.00 to seconds
     *
     * @param    string $t
     * @return    float
     */
    public function date2unix($dateStr)
    {
        $time = strtotime($dateStr);
        if (!$time) {
            $this->error = "Incorrect date format for string '$dateStr'";
        }
        return ($time);
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

    /**
     * float2time
     * this function translate time from seconds to format 00:00:00.00
     *
     * @param    float $i
     * @return    string
     */
    public function float2time($i)
    {
        $h = intval($i / 3600);
        $m = intval(($i - 3600 * $h) / 60);
        $s = $i - 60 * floatval($m) - 3600 * floatval($h);
        return sprintf("%01d:%02d:%05.2f", $h, $m, $s);
    }
/*
    public function splitLineByWords($line, $start, $end)
    {
        $newLines = array();
        $line = preg_replace("/\{.+?[^\{]\}/", "", $line);
        print "$line\n";
        if (strlen($line) == 0) {
            return ([]);
        }
        $cps = 14;
        //$words = str_word_count($line, 1 );
        $words = preg_split("/[\s,]+/", $line ) ;
        
        $delta = strlen($line) / ($end - $start);

        if ($delta > $cps) {
            $delta = $cps;
        }
        $timeEnd = $start;
        $timeStart = $start;
        $newLine = "";
        foreach ($words as $item) {
            //var_dump($item);
            $newLine = "$newLine $item";
            $timeEnd = $start + strlen($item) / $delta;
            $resultString = "Dialogue: 0," . $this->float2time($timeStart) . "," . $this->float2time($timeEnd) . ",Default,,0,0,0,,$newLine";
            $timeStart = $timeEnd;
            $newLines[] = $resultString;
        }
        //Dialogue: 0,0:00:00.90,0:00:01.37,YTStyle,,0,0,0,,{\1a&HFF&\2a&HFF&\3a&HFF&\4a&HFF&\fs54}\h{\r}I {\1a&HFF&\2a&HFF&\3a&HFF&\4a&HFF&\fs54}\h{\r}
        return ($newLines);
    }
*/
    public function splitSubtitlesWordByWord($input, $output, $assOption, $oneline)
    {
        $newAss = array();
        //            "Style: main,$font,$fontSize,$fontColor,&H000000FF,&H80FFFFFF,&H00000000,0,0,0,0,100,100,0,0,4,0.1,0,$alignment,$MarginL,$MarginR,$MarginV,1",

        try {
            $ass = file($input, FILE_IGNORE_NEW_LINES);
            $timeStart = 0;
            $timeEnd = 0;
            $cps = 16;
            $lastLine = "";
            $i = 1;
            $additionalLines = array();
            foreach ($ass as $key => $item) {

                $matches = null;
                if (preg_match("/^\[Events\]/", $item, $matches)) {
                    $style = join(",", array(
                        //Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
                         //Style: Default,Arial,16,&Hffffff,&Hffffff,&H0,&H0,0,0,0,0,100,100,0,0,1,1,0,2,10,10,10,0
                         "Style: Main",
                        $assOption['Fontname'],
                        $assOption['Fontsize'],
                        $assOption['PrimaryColour'],
                        $assOption['SecondaryColour'],
                        $assOption['OutlineColour'],
                        $assOption['BackColour'],
                        $assOption['Bold'],
                        $assOption['Italic'],
                        $assOption['Underline'],
                        $assOption['StrikeOut'],
                        $assOption['ScaleX'],
                        $assOption['ScaleY'],
                        $assOption['Spacing'],
                        $assOption['Angle'],
                        $assOption['BorderStyle'],
                        $assOption['Outline'],
                        $assOption['Shadow'],
                        $assOption['Alignment'],
                        $assOption['MarginL'],
                        $assOption['MarginR'],
                        $assOption['MarginV'],
                        0,

                    ));
                    $newAss[] = $style;

                    $style = join(",", array(
                        //Format: Name, Fontname, Fontsize, PrimaryColour, SecondaryColour, OutlineColour, BackColour, Bold, Italic, Underline, StrikeOut, ScaleX, ScaleY, Spacing, Angle, BorderStyle, Outline, Shadow, Alignment, MarginL, MarginR, MarginV, Encoding
                         //Style: Default,Arial,16,&Hffffff,&Hffffff,&H0,&H0,0,0,0,0,100,100,0,0,1,1,0,2,10,10,10,0
                         "Style: Top",
                        $assOption['Fontname'],
                        $assOption['Fontsize'],
                        $assOption['PrimaryColour'],
                        $assOption['SecondaryColour'],
                        $assOption['OutlineColour'],
                        $assOption['BackColour'],
                        $assOption['Bold'],
                        $assOption['Italic'],
                        $assOption['Underline'],
                        $assOption['StrikeOut'],
                        $assOption['ScaleX'],
                        $assOption['ScaleY'],
                        $assOption['Spacing'],
                        $assOption['Angle'],
                        $assOption['BorderStyle'],
                        $assOption['Outline'],
                        $assOption['Shadow'],
                        $assOption['Alignment'],
                        $assOption['MarginL'],
                        $assOption['MarginR'],
                        $assOption['MarginV'] + $assOption['Fontsize'] + round($assOption['Fontsize'] / 10),
                        0,

                    ));

                    $newAss[] = $style;
                    $newAss[] = $item;
                }

                if (preg_match("/Dialogue:\s*\d+,(\d+:\d+:\d+.\d+),(\d+:\d+:\d+\.\d+),.+,.+,.+,.+,(.+)$/", $item, $matches)) {
                    $start = $this->time2float($matches[1]);
                    $end = $this->time2float($matches[2]);
                    $line = $matches[3];

                    //$newLines = array();
                    $line = trim(preg_replace("/\{.+?[^\{]\}/", "", $line));
                    print "$line\n";
                    if (strlen($line) == 0) {
                        $line = " ";
                    }
                    $words = preg_split("/[\s,]+/", $line ) ;
                    //$words = str_word_count($line, 1);
                    $delta = strlen($line) / ($end - $start);

                    if ($delta > $cps) {
                        $delta = $cps;
                    }
                    if ($timeStart < $start) {
                        //$timeEnd=$start;
                        $timeStart = $start;
                        $timeEnd = $start;
                    }

                    $duration = 0;
                    $newLine = "";


                    foreach ($words as $item) {
                        $duration += strlen($item) / $delta;
                        $newLine = "$newLine $item";
                        $timeEnd = $timeStart + strlen($item) / $delta;

                        $resultString = "Dialogue: 0," . $this->float2time($timeStart) . "," . $this->float2time($timeEnd) . ",Main,,0,0,0,,$newLine";
                        //$resultString = "Dialogue: 0," . $this->float2time($timeStart) . "," . $this->float2time($timeEnd) . ",Main,,0,0,0,,{\\pos(10,70)}$newLine";
                        $timeStart = $timeEnd;
                        //$newLines[] = $resultString;
                        $newAss[] = $resultString;
                    }

                    $additionalLines[$i]['timeEnd']=$timeEnd;

                    $i++;
                    $additionalLines[$i]['timeStart']=$timeEnd;
                    $additionalLines[$i]['text'] = $newLine;                    

                } else {
                    $newAss[] = $item;

                }

            }
            //var_dump($additionalLines);
            if( !$oneline) {
                foreach ($additionalLines as $key => $item) {
                    if( isset($item['timeEnd'] ) && isset($item['timeStart'] )) {
                        $timeEndFixed = ($item['timeEnd'] - $item['timeStart'] > 2) ? ($item['timeStart'] + 2) : $item['timeEnd'];
                        $resultString = "Dialogue: 0," . $this->float2time($item['timeStart']) . "," . $this->float2time($timeEndFixed) . ",Top,,0,0,0,," . $item['text'];
                        $newAss[] = $resultString;
                    }
    
                }
            }


            $content = join(PHP_EOL, $newAss);
            if (!file_put_contents($output, $content)) {
                $this->writeToLog("Error: Cannot save file $output");
                return(false);
            }
        } catch (Exception $e) {
            $this->writeToLog("Error: " . $e->getMessage());
        }
        return(true);
    }
}
