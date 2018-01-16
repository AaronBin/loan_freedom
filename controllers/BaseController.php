<?php namespace app\controllers;
use yii\web\Controller;
/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/25
 * Time: 11:05
 */

class BaseController extends  Controller{

    const SUCCESS_CODE = 1000;
    const FAULT_CODE   = 1001;

    protected $app = null;
    protected $_back      = [];
    protected $_success   = ['code' => self::SUCCESS_CODE,'msg' => 'Success', 'data' => []];
    protected $_failed    = ['code' => self::FAULT_CODE,'msg' => 'Fault', 'data' => []];

    protected function wechat()
    {
        if ($this->app === null)
        {
            $options = [
                'token' => \Yii::$app->params['weChat']['token'], //填写你设定的key
                'appid' => \Yii::$app->params['weChat']['appId'], //填写高级调用功能的app id
                'appsecret' => \Yii::$app->params['weChat']['appsecret'], //填写高级调用功能的app id
            ];
            require '../vendor/wechat/wechat-sdk.php';
            $this->app = new \Wechat($options);
        }
        return $this->app;
    }

    public function json()
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($this->_back));
    }

}