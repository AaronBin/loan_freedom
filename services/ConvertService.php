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
     * @throws \Exception
     * 转写服务接口
     */
    public function getConvert($url)
    {
        $url = "http://afterloan.oss-cn-hangzhou.aliyuncs.com/record/15100963705000415031.mp3?OSSAccessKeyId=LTAIRcIsds2Olwev Expires=1510217702 Signature=PRByYZOJswKkaCVNTn0EqdfPfhY%3D";
        try{
            $path = $_SERVER['DOCUMENT_ROOT'];
            $path = substr($path,0,strlen($path)-4).'/libs/jar/java-record-convert.jar';
            $host = \Yii::$app->params['convert_sdk']['sdk_host'];
            $java = "java -jar {$path} {$host}  {$url}";
            exec($java,$output,$returnVal);
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
}