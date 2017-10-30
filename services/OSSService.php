<?php namespace app\services;
use OSS\OssClient;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/24
 * Time: 16:11
 */

class OSSService extends BaseService
{

    public $client = null;
    public function oss_init()
    {
        require_once '../vendor/autoload.php';
        if(!isset($this->client)){
            $this->client = new OssClient(\Yii::$app->params['oss']['access_key_id'],
                \Yii::$app->params['oss']['access_key_secret'],\Yii::$app->params['oss']['endpoint']);
        }
        return $this->client;
    }

    public function actionOssUpload($url)
    {
        $this->oss_init();
        try{
            $data = $this->download($url);
            if(empty($data))
            {
                throw new \Exception('下载远程文件失败');
            }
            $object = 'record/'.$data['file_name'];
            $result = $this->client->uploadFile(\Yii::$app->params['oss']['bucket'],$object,$data['save_path']);
            unlink($data['save_path']);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
        return [
            'oss_key' => $data['file_name'],
            'oss_url' => $result['info']['url'],
        ];
    }

    public function download($url)
    {
        $save_dir  = $_SERVER['DOCUMENT_ROOT'].'/temp/';
        $filename  = time() . str_shuffle(mt_rand(1000000000, 9999999900)).'.mp3';
        if (trim($url) == '') {
            return false;
        }
        if (trim($save_dir) == '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir.= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true)) {
            return false;
        }
        //获取远程文件所采用的方法
        $ch = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $content = curl_exec($ch);
        curl_close($ch);

        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        $data = [
            'file_name' => $filename,
            'save_path' => $save_dir . $filename,
            'file_size' => $size
        ];
        return $data;
    }


}