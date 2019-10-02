<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 16.09.2019
 * Time: 21:47
 */

namespace P3rc1val\streaming;


class Clip {

    public static function create($hostId, $frameData){
        $targetDir = \P3rc1val\Config::STREAM_DATA_DIR.$hostId;
        $framesDir = $targetDir.DIRECTORY_SEPARATOR.'frames';
        $clipsDir = $targetDir.DIRECTORY_SEPARATOR.'clips';

        //create directories for this host if they not exist
        if(!file_exists($targetDir)){ mkdir($targetDir); }
        if(!file_exists($framesDir)){ mkdir($framesDir); }
        if(!file_exists($clipsDir)){ mkdir($clipsDir); }

        //get start time for clip creation
        $startTime = microtime(true);

        //save frames to jpg files
        for($i = 0; $i < count($frameData['frames']); $i++){
            $image = imagecreatefromstring(base64_decode($frameData['frames'][$i]));
            imagejpeg($image, $framesDir.DIRECTORY_SEPARATOR.'frame'.$i.'.jpg');
        }

        //get name for new clip - starts with 0 and grows
        $outputName = '0-clip.mp4';
        $files = glob($clipsDir.DIRECTORY_SEPARATOR.'*.mp4');
        if(count($files) > 0){
            $outputName = count($files).'-clip.mp4';
        }

        //create mp4 clip in output dir
        $ffmepg = new FFmpeg();
        $ffmepg->setInputDir($framesDir);
        $ffmepg->setOutputDir($clipsDir);
        $ffmepg->create('frame%d.jpg', $outputName);

        //delete frame files
        $files = glob($framesDir.DIRECTORY_SEPARATOR.'*'); // get all file names
        foreach($files as $file){ // iterate files
            if(is_file($file))
                unlink($file); // delete file
        }

        //calculate clip creation time
        $timeClipSaved = microtime(true)-$startTime;

        return [
            'frd' => time()-intval($frameData['sendTime']), //frame receive delay
            'cst' => $timeClipSaved //time of clip generation
        ];
    }
}