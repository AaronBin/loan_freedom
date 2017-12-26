<?php namespace app\services;
use Elasticsearch\ClientBuilder;

/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 17/10/24
 * Time: 16:11
 */

class ESService extends BaseService
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
     * 拼接公共参数
     * @param
     * @return array
     */
    public function jointParam($param)
    {
        $this->init();
        $params = [
            'index' => $this->_index,
            'type'  => $this->_type,
            'body' => $param
        ];

        return $params;
    }

    /**
     * @param $content
     * @param null $page_size
     * @param null $page_now
     * 根据content 字段查询es数据
     * @return array
     */
    public function actionGetMessage($content,$page_size=null,$page_now=null)
    {
        $page_size = $page_size ? $page_size : $this->back_num;
        $params = [
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
        ];
        $params = $this->jointParam($params);
        $result = $this->client->search($params);
        return isset($result['hits']) ? $result['hits'] : [];
    }


    /**
     * @param $convert_id
     * 根据content 字段查询es数据
     * @return array
     */
    public function actionGetConvert($convert_id)
    {
        $params = [
            'query' => [
                'bool' => [
                    'should' => [
                        'match_phrase' => [
                            'task_id' => $convert_id
                        ]
                    ]
                ]
            ],
        ];
        $params = $this->jointParam($params);
        $result = $this->client->search($params);
        return isset($result['hits']) ? $result['hits'] : [];
    }

    /*
    * 根据 record_ids 返回列表信息
    */
    public function actionGetRecordList($record_ids)
    {

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

        return  $this->getKeyByArray($result,'check_id');

    }

    public function getField($field,$value)
    {
        $params = [
            'size' => $this->back_num,
            'query' => [
                'match' => [
                    $field => $value,
                ]
            ],
        ];

        $params = $this->jointParam($params);
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
     * @param
     */
    public function actionWrite($param)
    {
        $params = $this->jointParam($param);
        try{
            $res = $this->client->index($params);
            if($res){
                return true;
            }else{
                throw new \Exception('写入失败');
            }
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * @param $task_id
     * @param $content
     * 更新es
     */
    public function actionUpdate($task_id,$content)
    {
        $params = [
            'query' => [
                'match' => [
                    'task_id' => $task_id,
                ]
            ],
            'script' => [
                "inline" => "ctx._source.content = '{$content}'",
            ],
        ];
        $params = $this->jointParam($params);
        try{
            $result = $this->client->updateByQuery($params);
            if(empty($result) && !$result['updated'])
            {
                throw new \Exception('update failed');
            }

        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }


    /**
     * @param $task_id
     * @param $content
     * 已经质检更新状态
     */
    public function actionCheckUpdate($check_id,$check_status)
    {
        $params = [
            'query' => [
                'match' => [
                    'check_id' => $check_id,
                ]
            ],
            'script' => [
                "inline" => "ctx._source.check_status = {$check_status}",
            ],
        ];
        $params = $this->jointParam($params);
        try{
            $result = $this->client->updateByQuery($params);
            if(empty($result) && !$result['updated'])
            {
                throw new \Exception('update failed');
            }
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * 多关键字同时检索
     * @param $contents
     * @param string $check_status
     * @param int $pageNum
     * @param $begin_time
     * @param $end_time
     * @return array
     */
    public function actionGetContents($contents,$check_status='',$pageNum=1,$begin_time,$end_time)
    {
        $contents = json_decode($contents,true);
        $result  = [];
        foreach($contents as $key=>$val)
        {
            $data = $this->getFiledVal('content',$val,$check_status,$begin_time,$end_time);
            $result = array_merge($data,$result);
        }
        if(!empty($result))
        {
            foreach($result as $key=>$val){
                $result[$key]['sort'] = $val['_source']['created_at'];
            }
            $temp = array();
            foreach ($result as $v) {
                $temp[] = $v['sort'];
            }
            array_multisort($temp, SORT_DESC,$result );
        }
        $start = $pageNum ? (($pageNum-1)*20) : 0;
        $numRes = array_slice($result,$start,20);
        unset($data);
        $data['total'] = count($result);
        $data['hits']  = $numRes;
        return $data;
    }

    public function getFiledVal($filed,$val,$check_status,$begin_time,$end_time)
    {
        $this->init();
        $data = [
            'properties' => [
                'created_at' => [
                    'type' => 'text',
                    'fielddata' => true,
                ]
            ]
        ];
        $url = \Yii::$app->params['qa_params']['es_host'].'/'.$this->_index.'/_mapping/'.$this->_type;
        $data = json_encode($data);
        $this->_curl_put($url,$data);

        if($check_status){
            $params = [
                'size'  => $this->back_num,
                'query' => [
                    'bool' => [
                        'must' => [
                            [
                                'match_phrase' => [
                                    $filed => $val
                                ]
                            ],
                            [
                                'match' => [
                                    'check_status' => $check_status
                                ]
                            ],
                        ],
                        'filter' => [
                            'range' => [
                                'created_at' => [
                                    'gte' => $begin_time,
                                    'lte' => $end_time
                                ]
                            ]
                        ]
                    ]
                ],
                'sort' => [
                    'created_at' => [
                        'order'=> 'desc',
                    ],
                ],
                'highlight' => [
                    'fields' => [
                        'content'=>[
                            'fragment_size' => 10000
                        ]
                    ]
                ],
            ];
        }else{
            $params = [
                'size'  => $this->back_num,
                'query' => [
                    'bool' => [
                        'must' => [
                            ['match_phrase' => [$filed => $val]],
                        ],
                        'filter' => [
                            'range' => [
                                'created_at' => [
                                    'gte' => $begin_time,
                                    'lte' => $end_time
                                ]
                            ]
                        ]
                    ],
                ],
                'sort' => [
                    'created_at' => [
                        'order'=> 'desc',
                    ],
                ],
                'highlight' => [
                    'fields' => [
                        'content'=>[
                            'fragment_size' => 10000,
                        ]
                    ]
                ],
            ];
        }

        $params = $this->jointParam($params);
        $result = $this->client->search($params);

        $result =  isset($result['hits']['hits']) ? $result['hits']['hits'] : [];
        if(!empty($result)) {
            foreach($result as $key=>$val)
            {
                $result[$key]['_source']['content'] = $val['highlight']['content'][0];
                unset($result[$key]['highlight']);
            }
        }
        return $result;
    }

    /**
     * @param $check_id
     * @param $call_time
     * @throws \Exception
     * 更新通话时间字段
     */
    public function actionResetCallTime($check_id,$call_time)
    {
        $url = \Yii::$app->params['qa_params']['es_host'].'/_cluster/settings';
        $data = [
            'transient' => [
                'script.max_compilations_per_minute' => 30
            ]
        ];
        $data = json_encode($data);
        $this->_curl_put($url,$data);

        $params = [
            'query' => [
                'match' => [
                    'check_id' => $check_id,
                ]
            ],
            'script' => [
                "inline" => "ctx._source.created_at = {$call_time}",
            ],
        ];
        $params = $this->jointParam($params);
        try{
            $result = $this->client->updateByQuery($params);

            if(empty($result) && !$result['updated'])
            {
                throw new \Exception('update failed');
            }
        }catch (\Exception $e){
            throw new \Exception($e->getMessage());
        }
    }

    public function _curl_put($url,$data)
    {
        $ch = curl_init(); //初始化CURL句柄
        curl_setopt($ch, CURLOPT_URL, $url); //设置请求的URL
        curl_setopt ($ch, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); //设为TRUE把curl_exec()结果转化为字串，而不是直接输出
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"PUT"); //设置请求方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//设置提交的字符串
        $output = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($output,true);
        return $result;
    }

}