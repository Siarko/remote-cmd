<?php
/**
 * Created by PhpStorm.
 * User: SiarkoWodór
 * Date: 26.11.2019
 * Time: 21:12
 */

namespace frontend\actions;

use P3rc1val\auth\User;
use P3rc1val\Config;
use P3rc1val\routing\Router;
use P3rc1val\templates\TemplateParser;
use P3rc1val\util\ArrayUtils;

/**
 * @method static login($user, $htmlTemplate, $pageTemplate)
 * @method static logout(User $user)
 * @method static test()
 * @method static loginPost(User $user)
 * @method static home(TemplateParser $pageTemplate, User $user)
 */
class EntryActions extends AbstractActions {


    /**
     * @param User $user
     * @param TemplateParser $htmlTemplate
     * @param TemplateParser $pageTemplate
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionLogin(User $user, TemplateParser $htmlTemplate, TemplateParser $pageTemplate){
        if($user->isLogged()){
            Router::resolver(Router::REDIRECT)->resolve('home')->end();
        }
        $htmlTemplate->addCssFile('assets/css/login');
        $pageTemplate->setFilePath(Config::HTML_TEMPLATES.'LoginPanel.php');
    }

    /**
     * @param User $user
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionLoginPost(User $user){
        if(ArrayUtils::requireKeys($_POST, 'login', 'password')){
            $user->login($_POST['login'], $_POST['password']);
            if($user->isLogged()){
                Router::resolver(Router::REDIRECT)
                    ->resolve('home')->end();
            }else{
                Router::resolver(Router::REDIRECT)
                    ->resolve('login')
                    ->resolve(['cause' => User::LOGIN_ERR_INVALID_DATA])->end();
            }
        }
        Router::resolver(Router::REDIRECT)
            ->resolve('login')
            ->resolve(['cause' => User::LOGIN_ERR_NO_DATA])->end();
    }

    /**
     * @param User $user
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionLogout(User $user){
        $user->logout();
        Router::resolver(Router::REDIRECT)->resolve('login')->end();
    }

    /**
     * @param TemplateParser $pageTemplate
     * @param User $user
     * @throws \P3rc1val\routing\ResolverEndException
     */
    public function actionHome(TemplateParser $pageTemplate, User $user){
        $user->authenticate();
        $pageTemplate->setFilePath(Config::HTML_TEMPLATES.'MainPanel.php');
        $pageTemplate->user = $user;
    }
}