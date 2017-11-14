<?php
namespace app\controllers;
use app\libs\Factory;
use app\services\ConvertService;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/16
 * Time: 16:21
 */

class ConvertController extends BaseController
{
    public function actionConvert()
    {
        $url   = \Yii::$app->request->post('url');
        $debug = \Yii::$app->request->post('debug');
        try{
           $this->_success['data'] = Factory::get(ConvertService::class)->getConvert($url,$debug);
            $this->_back = $this->_success;
        }catch (\Exception $e){
            $this->_failed['msg'] = $e->getMessage();
            $this->_back = $this->_failed;
        }
        $this->json();
    }

    public function actionDemo()
    {
        try{
            exec('ls',$output,$returnVal);

        }catch (\Exception $e){
            var_dump($e->getMessage());
        }
        var_dump($output);
    }

}