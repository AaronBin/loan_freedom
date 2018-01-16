<?php namespace app\controllers;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 18/1/16
 * Time: 09:45
 */

class RouterController extends BaseController
{

    public function actionApi()
    {
        $this->wechat()->valid();
        $type = $this->wechat()->getRev()->getRevType();
        $openid = $this->wechat()->getRevFrom();

        var_dump($openid);
    }
}