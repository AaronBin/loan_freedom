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

    protected $_back      = [];
    protected $_success   = ['code' => self::SUCCESS_CODE,'msg' => 'Success', 'data' => []];
    protected $_failed    = ['code' => self::FAULT_CODE,'msg' => 'Fault', 'data' => []];

    /**
     * return json
     */
    public function json()
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($this->_back));
    }

}