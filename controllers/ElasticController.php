<?php
namespace app\controllers;
use yii\web\Controller;
use Elasticsearch\ClientBuilder;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/9/2
 * Time: 16:21
 */

class ElasticController extends Controller
{
    public $_host        = 'http://10.241.104.66:9200';
    //public $_host        = 'http://127.0.0.1:9200';
    public $_index       = 'call_system_record';
    public $_type        = 'logs';
    public $client       = null;
    public $back_num     = 100;
    public function init()
    {
        header('Content-Type:application/json; charset=utf-8');
        require '../vendor/autoload.php';
        $this->client =  ClientBuilder::create()
            ->setHosts([$this->_host])
            ->build();
    }

    /**
     * @param $content
     * @param null $page_size
     * @param null $page_now
     * 根据content 字段查询es数据
     */
    public function actionGetMessage($content,$page_size=null,$page_now=null)
    {
        if(!$this->client)
        {
            $this->init();
        }
        $page_size = $page_size ? $page_size : $this->back_num;
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => [
                'size'  => $page_size,
                'from'  => $page_now ? ($page_now-1) * $page_size : 0,
                'query' => [
                    'bool' => [
                        'should' => [
                            'match_phrase' => [
                                'content' => $content
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $result = $this->client->search($params);
        $result =  isset($result['hits']) ? $result['hits'] : [];
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode([
            'code' => 1000,
            'message' => 'success',
            'data' => $result
        ]));
    }


    /**
     * @param $convert_id
     * 根据content 字段查询es数据
     */
    public function actionGetConvert($convert_id)
    {
        if(!$this->client)
        {
            $this->init();
        }
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => [
                'query' => [
                    'bool' => [
                        'should' => [
                            'match_phrase' => [
                                'task_id' => $convert_id
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $result = $this->client->search($params);
        $result =  isset($result['hits']) ? $result['hits'] : [];
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode([
            'code' => 1000,
            'message' => 'success',
            'data' => $result
        ]));
    }


    /*
     * 根据 record_ids 返回列表信息
     */
    public function actionGetRecordList($record_ids)
    {
        $this->init();
        try{
            $result = [];
            $record_ids = explode(',',$record_ids);
            foreach($record_ids as $key=>$val)
            {
                $temp = $this->getField('check_id',$val);
                foreach($temp as $k=>$v)
                {
                    $result[] = isset($v['_source']) ? $v['_source'] : [];
                }
            }
        }catch (\Exception $e){
            return $e->getMessage();
        }
        $result =  $this->getKeyByArray($result,'check_id');
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode([
            'code' => 1000,
            'message' => 'success',
            'data' => $result
        ]));
    }

    public function getField($field,$value)
    {
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => [
                'size'  => $this->back_num,
                'query' => [
                    'match' => [
                        $field => $value,
                    ]
                ],
            ]
        ];

        $result = $this->client->search($params);
        return isset($result['hits']['hits']) ? $result['hits']['hits'] : [];
    }

    public function getKeyByArray($data,$field)
    {
        $result = [];
        if(!empty($data))
        {
            foreach($data as $key=>$val){
                $result[$val[$field]] = $val;
            }
        }
        return $result;
    }


    /**
     * 写入es
     */
    public function actionWrite()
    {
        $param = \Yii::$app->request->post('param');
        $this->init();
        $index = [
            'index'  => $this->_index,
            'type'   => $this->_type,
            'body'   => $param
        ];
        try{
            $res = $this->client->index($index);
            if($res){
                echo json_encode([
                    'code' => 1000,
                    'message' => 'success',
                ]);
            }
        }catch (\Exception $e){
            echo json_encode([
                'code' => 1001,
                'message' => $param
            ]);
        }

    }

    /**
     * @param $task_id
     * @param $content
     * 更新es
     */
    public function actionUpdate($task_id,$content)
    {
        $this->init();
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => [
                'query' => [
                    'match' => [
                        'task_id' => $task_id,
                    ]
                ],
                'script' => [
                    "inline" => "ctx._source.content = '{$content}'",
                ],
            ]
        ];
        try{
            $result = $this->client->updateByQuery($params);
            if(empty($result) && !$result['updated'])
            {
                throw new \Exception('update failed');
            }
            echo json_encode([
                'code' => 1000,
                'message' => 'success'
            ]);
        }catch (\Exception $e){
            echo json_encode([
                'code' => 1001,
                'message' => 'error'
            ]);
        }
    }

}