<?php


namespace frontend\actions;


use P3rc1val\auth\User;
use P3rc1val\database\models\Hosts;
use P3rc1val\routing\Router;

/**
 * @method static setCustomName(User $user)
 */
class HostDataActions extends AbstractActions {

    /**
     * @param User $user
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionSetCustomName(User $user){
        $user->authenticate();
        $hostId = $this->getRequestData()->hostId;
        $customName = trim($this->getPostData()->customName);

        $customName = strlen($customName) > 0 ? $customName : null;

        $resolver = Router::resolver(Router::JSON);
        /* @var $host Hosts*/
        $host = Hosts::find($hostId)->one();
        if($host){
            $host->custom_name = $customName;
            $resolver->resolve('success', $host->save());
        }else{
            $resolver->resolve('success', false);
            $resolver->resolve('message', "cannot find specified host");
        }
        $resolver->end();
    }
}