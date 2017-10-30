<?php
namespace app\controllers;
use app\libs\Factory;
use app\services\OSSService;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/16
 * Time: 16:21
 */

class OssController extends BaseController
{

    public function actionOssUpload($url)
    {
        try{
            $this->_success['data'] = Factory::get(OSSService::class)->actionOssUpload($url);
            $this->_back = $this->_success;

        }catch (\Exception $e){
            $this->_success['data'] = $e->getMessage();
            $this->_back = $this->_failed;
        }
        $this->json();
    }


    /**
     * @param $object
     */
    public function actionSignUrl($object)
    {
        Factory::get(OSSService::class)->oss_init();
        try{
            $this->_success['data'] = Factory::get(OSSService::class)->client->signUrl(\Yii::$app->params['oss']['bucket'], $object,\Yii::$app->params['oss']['timeout']);
            $this->_back = $this->_success;
        } catch(\Exception $e) {
            $this->_success['data'] = $e->getMessage();
            $this->_back = $this->_failed;
        }
        $this->json();
    }

}