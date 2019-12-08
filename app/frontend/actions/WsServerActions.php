<?php


namespace frontend\actions;


use P3rc1val\auth\User;
use P3rc1val\routing\Router;
use P3rc1val\websocket\WsProcess;

/**
 * @method static getState(User $user)
 * @method static toggleState(User $user)
 */
class WsServerActions extends AbstractActions {

    /**
     * @param User $user
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionGetState(User $user){
        $user->authenticate();

        Router::resolver(Router::JSON)
            ->resolve('state', WsProcess::getState())
            ->end();
    }

    /**
     * @param User $user
     * @return array
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionToggleState(User $user){
        $user->authenticate();

        $state = WsProcess::getState();
        if($state === false){
            $result = WsProcess::start();
        }else{
            $result = WsProcess::kill($state);
        }
        return [
            Router::JSON => [$result]
        ];
    }

}