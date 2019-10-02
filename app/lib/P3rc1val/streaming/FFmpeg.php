<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 07.09.2019
 * Time: 21:56
 */

namespace P3rc1val\streaming;

class FFmpeg {

    private $inputFrames = 10;
    private $outputFrames = 30;

    private $inputDir = null;
    private $outputDir = null;

    private function createCommand($inputName, $outputName){
        $result = "ffmpeg -y ";
        $result .= "-framerate ".$this->inputFrames." ";
        $result .= "-i ".$inputName." ";
        $result .= "-r ".$this->outputFrames." ";
        $result .= "-pix_fmt yuv420p ";
        $result .= "-movflags empty_moov+default_base_moof+frag_keyframe ";
        $result .= "-profile:v baseline \"$outputName\" 2>&1";
        return $result;
    }

    public function create($inputName, $outputName){
        $outputDir = $this->outputDir.DIRECTORY_SEPARATOR.$outputName;
        $command = $this->createCommand($inputName, $outputDir);
        if(!chdir($this->inputDir)){
            echo("[FFMPEG] Cannot enter input directory!\n");
            echo("[FFMPEG] ".$this->inputDir."\n");
        }
        chdir(".");
        return shell_exec($command);

    }

    /**
     * @return int
     */
    public function getInputFrames() {
        return $this->inputFrames;
    }

    /**
     * @param int $inputFrames
     */
    public function setInputFrames($inputFrames) {
        $this->inputFrames = $inputFrames;
    }

    /**
     * @return int
     */
    public function getOutputFrames() {
        return $this->outputFrames;
    }

    /**
     * @param int $outputFrames
     */
    public function setOutputFrames($outputFrames) {
        $this->outputFrames = $outputFrames;
    }

    /**
     * @return null
     */
    public function getInputDir() {
        return $this->inputDir;
    }

    /**
     * @param null $inputDir
     */
    public function setInputDir($inputDir) {
        $this->inputDir = $inputDir;
    }

    /**
     * @return null
     */
    public function getOutputDir() {
        return $this->outputDir;
    }

    /**
     * @param null $outputDir
     */
    public function setOutputDir($outputDir) {
        $this->outputDir = $outputDir;
    }


}