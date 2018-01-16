<?php

namespace app\controllers;
class SiteController extends BaseController
{

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
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
