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

        trace_info(0,'GET_INFO','hello');

        $this->wechat()->valid();
        $type = $this->wechat()->getRev()->getRevType();
        switch($type) {
            case \Wechat::MSGTYPE_TEXT:
                $this->wechat()->text("hello, I'm wechat")->reply();
                exit;
                break;
            case \Wechat::MSGTYPE_EVENT:
                break;
            case \Wechat::MSGTYPE_IMAGE:
                break;
            default:
                $this->wechat()->text("help info")->reply();
        }
    }

}