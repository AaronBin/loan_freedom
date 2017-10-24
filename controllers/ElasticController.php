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
    public $_index       = null;
    public $_type        = null;
    public $client       = null;
    public $back_num     = 100;
    public function init()
    {
        $this->_index = \Yii::$app->params['qa_params']['_index'];
        $this->_type  = \Yii::$app->params['qa_params']['_type'];

        header('Content-Type:application/json; charset=utf-8');
        require '../vendor/autoload.php';
        $this->client =  ClientBuilder::create()
            ->setHosts([\Yii::$app->params['qa_params']['es_host']])
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
    public function actionUpdate()
    {
        $task_id = \Yii::$app->request->post('task_id');
        $content = \Yii::$app->request->post('content');
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

    /**
     * 多滚关键字同时检索
     * @param $contents
     * @param int $pageNum
     */
    public function actionGetContents($contents,$pageNum=1)
    {
        $contents = json_decode($contents,true);
        $result  = [];
        foreach($contents as $key=>$val)
        {
            $data = $this->getFiledVal('content',$val);
            $result = array_merge($data,$result);
        }
        $start = $pageNum ? $pageNum : 1;
        $numRes = array_slice($result,($start-1),20);
        unset($data);
        $data['total'] = count($result);
        $data['hits']  = $numRes;
        exit(json_encode([
            'code'    => 1000,
            'message' => 'success',
            'data'    => $data
        ]));
    }

    public function getFiledVal($filed,$val)
    {
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => [
                'size'  => $this->back_num,
                'query' => [
                    'bool' => [
                        'should' => [
                            'match_phrase' => [
                                $filed => $val
                            ]
                        ]
                    ]
                ],
            ]
        ];
        $result = $this->client->search($params);
        return isset($result['hits']['hits']) ? $result['hits']['hits'] : [];
    }

}