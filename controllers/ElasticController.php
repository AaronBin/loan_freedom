<?php
namespace app\controllers;
use app\libs\Factory;
use app\services\ESService;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/9/2
 * Time: 16:21
 */

class ElasticController extends BaseController
{

    /**
     * @param $content
     * @param null $page_size
     * @param null $page_now
     * 根据content 字段查询es数据
     */
    public function actionGetMessage($content,$page_size=null,$page_now=null)
    {
        $this->_success['data'] = Factory::get(ESService::class)->actionGetMessage($content,$page_size,$page_now);
        $this->_back = $this->_success;
        $this->json();
    }


    /**
     * @param $convert_id
     * 根据content 字段查询es数据
     */
    public function actionGetConvert($convert_id)
    {
        $this->_success['data'] = Factory::get(ESService::class)->actionGetConvert($convert_id);
        $this->_back = $this->_success;
        $this->json();
    }


    /*
     * 根据 record_ids 返回列表信息
     */
    public function actionGetRecordList($record_ids)
    {
        $this->_success['data'] = Factory::get(ESService::class)->actionGetRecordList($record_ids);
        $this->_back = $this->_success;
        $this->json();
    }

    /**
     * 写入es
     */
    public function actionWrite()
    {
        $param = \Yii::$app->request->post('param');
        try{
            Factory::get(ESService::class)->actionWrite($param);
            $this->_back = $this->_success;
        }catch (\Exception $e){
            $this->_back = $this->_failed;
        }
        $this->json();
    }

    /**
     * @param $task_id
     * @param $content
     * 更新es
     */
    public function actionUpdate()
    {
        $task_id = \Yii::$app->request->post('task_id');
        $content = \Yii::$app->request->post('content');
        try{
            Factory::get(ESService::class)->actionUpdate($task_id,$content);
            $this->_back = $this->_success;
        }catch (\Exception $e){
            $this->_back = $this->_failed;
        }
        $this->json();
    }


    /**
     * @param $task_id
     * @param $content
     * 已经质检更新状态
     */
    public function actionCheckUpdate()
    {
        $check_id = \Yii::$app->request->post('check_id');
        $check_status = \Yii::$app->request->post('check_status');
        try{
            Factory::get(ESService::class)->actionCheckUpdate($check_id,$check_status);
            $this->_back = $this->_success;
        }catch (\Exception $e){
            $this->_back = $this->_failed;
        }
        $this->json();
    }



    /**
     * 多关键字同时检索
     * @param $contents
     * @param $check_status
     * @param int $pageNum
     */
    public function actionGetContents($contents,$check_status='',$pageNum=1)
    {
        $this->_success['data'] = Factory::get(ESService::class)->actionGetContents($contents,$check_status,$pageNum);
        $this->_back = $this->_success;
        $this->json();
    }

}