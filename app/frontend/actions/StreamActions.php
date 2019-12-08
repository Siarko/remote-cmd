<?php


namespace frontend\actions;


use P3rc1val\auth\User;
use P3rc1val\database\models\Hosts;
use P3rc1val\streaming\Mp4Reader;

/**
 * @method static clip(User $user)
 */
class StreamActions extends AbstractActions {

    /**
     * Read mp4 clip and serve it as a response
     * IF clip cannot be found echo -1
     * @param User $user
     */
    public function actionClip(User $user){
        /* @var $host Hosts*/
        $host = Hosts::find([Hosts::$USER => @$_SESSION['login']])->one();
        if(!$host){ die(); }
        $clipName = glob('streaming/'.$host->id.'/clips/*.mp4');
        if(count($clipName) > 0){
            $clipName = $clipName[0];
        }else{
            echo(-1);
            die();
        }

        Mp4Reader::read($clipName);
        unlink($clipName);
        die();
    }
}