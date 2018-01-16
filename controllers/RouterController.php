<?php namespace app\controllers;
/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 18/1/16
 * Time: 09:45
 */

class RouterController extends BaseController
{

    var $app = null;

    /**
     * single instance wechat function
     * @return WechatObject the wechat object
     */
    protected function app()
    {
        if ($this->app === null) {

        }
        return $this->app;
    }


    public function actionApi(){


        echo 111;
    }
}