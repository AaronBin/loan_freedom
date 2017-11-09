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
        $url = 'http://afterloan.oss-cn-hangzhou.aliyuncs.com/record/15101909733976164627.mp3?OSSAccessKeyId=LTAIRcIsds2Olwev&Expires=1510194891&Signature=dszqGyf01e6ButD7Uh1gW86rVCg%3D';
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