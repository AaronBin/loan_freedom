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

        $app = $this->wechat();

        echo "<pre>";
        print_r($app);
        exit;

//        $this->wechat()->valid();
//        $type = $this->wechat()->getRev()->getRevType();
//        //$openid = $this->wechat()->getRevFrom();
//        switch ($type) {
//            case \Wechat::MSGTYPE_EVENT:
//                $event     = $this->wechat()->getRevEvent();
//                $eventType = $event['event'];
//                $eventKey  = $event['key'];
//
//                switch ($eventType) {
//                    case 'SCAN':
//                        //return $this->scanEntry($eventKey);
//                        break;
//
//                    case 'subscribe':
//                        preg_match('/^qrscene_([\d]+)/', $eventKey, $e);
//                        if (isset($e[1])) {
//                            //$this->scanEntry($e[1]);
//                        } else {
//                            //$this->subscribeEntry();
//                        }
//                        break;
//
//                    case 'CLICK':
//                        switch ($eventKey) {
//                            case 'INDEX':
//                                //$this->CHECK_BALANCE();
//                                break;
//                            default:
//                                $this->wechat()->text('未知操作.')->reply();
//                                break;
//                        }
//                        break;
//                    default:
//                        $this->wechat()->news(array(
//                            array(
//                                'Title' => '欢迎上岸天使',
//                                'Description' => '欢迎上岸天使服务微信。',
//                                'PicUrl' => '',
//                                'Url' => \Yii::$app->params['host'] . '/Wxapp/Router/Close/'
//                            )
//                        ))->reply();
//                        break;
//                }
//        }

    }

}