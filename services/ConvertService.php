<?php namespace app\services;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/24
 * Time: 16:11
 */

class ConvertService extends BaseService
{
    /**
     * @param $url
     * @return string
     * @throws \Exception
     * 转写服务接口
     */
    public function getConvert($url)
    {
        try{
            $this->createDir();
            $urls = explode('&',$url);
            $url  = $urls[0].'  '.$urls[1].' '.$urls[2];
            $path = $_SERVER['DOCUMENT_ROOT'];
            $path = substr($path,0,strlen($path)-4).'/libs/jar/java-record-convert.jar';
            $host = \Yii::$app->params['convert_sdk']['sdk_host'];
            $java = "java -jar {$path} {$host}  {$url}";
            exec($java,$output,$returnVal);

            exit(json_encode($output));

            $data = end($output);
            return $this->formatData($data);
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
    /**
     * @param $data
     * @return string
     * @throws \Exception
     * 对转写后的数据做格式化返回
     */
    public function formatData($data)
    {
        if(!$data) throw new \Exception('data is empty');
        $data = explode("|",$data);
        $data = array_filter($data);
        array_pop($data);
        $call_content = '';
        foreach($data as $key=>$val)
        {
            $content = json_decode($val,true);
            $call_content.= $key ? ';段落-'.$content['rl'].':' : '段落-'.$content['rl'].':';
            if(empty($content)){
                continue;
            }
            if(empty($content['ws'])){
                continue;
            }
            foreach($content['ws'] as $ke=>$ve)
            {
                if(empty($ve['cw'])){
                    continue;
                }
                foreach($ve['cw'] as $k=>$v)
                {
                    $call_content.= $v['w'];
                }
            }
        }
        return $call_content;
    }

    public function createDir()
    {
        $dir = [
            '/tmp/record/pcm',
            '/tmp/record/video'
        ];
        try{
            foreach($dir as $val)
            {
                if (0 !== strrpos($val, '/')) {
                    $val.= '/';
                }
                //创建保存目录
                if (!file_exists($val) && !mkdir($val, 0777, true)) {
                    return false;
                }
            }
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }
}