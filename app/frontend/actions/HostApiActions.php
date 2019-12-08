<?php


namespace frontend\actions;

use P3rc1val\Config;
use P3rc1val\database\models\Hosts;
use P3rc1val\routing\Router;
use P3rc1val\util\ArrayUtils;

/**
 * @method static isRunning()
 * @method static queryActivity()
 * @method static upload()
 */
class HostApiActions extends AbstractActions {

    /**
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionIsRunning(){
        $r = Router::resolver(Router::JSON);

        if(ArrayUtils::requireKeys($_GET, 'id')) {
            $id = $_GET['id'];
            /* @var $dbRow Hosts | null*/
            $dbRow = Hosts::find($id)->one();
            if($dbRow == null){
                $r->resolve('result.running', false)->end();
            }else{
                $isRunning = (time()-strtotime($dbRow->last_check) <= 6);
                $r->resolve('result.running', $isRunning)->end();
            }
        }else{
            $r->resolve('result.error', 'NO ID')->end();
        }
    }

    /**
     * @throws \DbModelApi\exceptions\ColumnsNotSetException
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionQueryActivity(){
        $r = Router::resolver(Router::JSON);
        if(ArrayUtils::requireKeys($_GET, 'hostname', 'id')){
            $hostname = $_GET['hostname'];
            $id = $_GET['id'];

            /* @var $dbRow Hosts | null*/
            $dbRow = Hosts::find($id)->one();
            if($dbRow == null){
                $newRow = new Hosts();
                $newRow->id = $id;
                $newRow->hostname = $hostname;
                $newRow->last_check = date('Y-m-d H:i:s',time());
                $newRow->save();

                $r->resolve('result.body', false);
                $r->resolve('result.debug', $newRow->getLastSaveErrors())->end();
            }else{
                $dbRow->last_check = date('Y-m-d H:i:s', time());
                $dbRow->save();
                $r->resolve('result.body', ($dbRow->user != null))->end();
            }
        }else{
            $r->resolve('result.error', 'NO HOSTNAME OR MAC')->end();
        }
    }

    /**
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionUpload(){

        $r = Router::resolver(Router::RAW);
        if(!array_key_exists('ID', $_POST)){
            $r->resolve("NO HOST ID FOUND")->end();
        }
        if(count($_FILES) == 0){
            $r->resolve("NO FILES UPLOADED")->end();
        }
        $hostId = $_POST['ID'];
        $host = Hosts::find($hostId)->one();
        if(!$host){
            $r->resolve("HOST NOT FOUND")->end();
        }
        $hostDir = Config::HOST_UPLOADS_DIR.$hostId;
        if(!file_exists($hostDir)){mkdir($hostDir);}

        foreach ($_FILES as $id => $fileData) {
            $targetName = $hostDir.DIRECTORY_SEPARATOR.$fileData['name'];
            if(!move_uploaded_file($fileData['tmp_name'], $targetName)){
                $r->resolve("CANNOT SAVE UPLOADED FILE")->end();
            }
            $description = $_POST[$id.'_DESC'];
            file_put_contents($hostDir.DIRECTORY_SEPARATOR.$id.'.json', $description);
        }

        $r->resolve("UPLOAD OK")->end();
    }
}