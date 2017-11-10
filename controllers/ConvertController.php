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
        $url = \Yii::$app->request->post('url');
        $url = "http://afterloan.oss-cn-hangzhou.aliyuncs.com/record/15102922237866547600.mp3?OSSAccessKeyId=LTAIRcIsds2Olwev&Expires=1510298433&Signature=Vgi57v5HDXUOdhQ7tfjpBjhB%2FWo%3D";
        try{
           $this->_success['data'] = Factory::get(ConvertService::class)->getConvert($url);
            $this->_back = $this->_success;
        }catch (\Exception $e){
            $this->_failed['msg'] = $e->getMessage();
            $this->_back = $this->_failed;
        }
        $this->json();
    }

}