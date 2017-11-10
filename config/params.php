<?php

return [
    'adminEmail' => 'admin@example.com',
    'qa_params' => [
        'es_host' => 'http://10.241.104.66:9200',
        //'es_host' => 'http://127.0.0.1:9200',
        '_index'  => 'call_system_record',
        '_type'   => 'logs',
    ],
    'oss' => [
        'access_key_id' => 'LTAIRcIsds2Olwev',
        'access_key_secret' => 'CegkdzzDDpA9DkTcfkP2m5ivC8xFtK',
        'endpoint'  => 'oss-cn-hangzhou.aliyuncs.com',
        'bucket'    => 'afterloan',
        'timeout'   => 3600,
    ],
    'convert_sdk' => [
        'sdk_host' => '192.168.39.200',
    ],
];
