<?php
/**
 * Created by PhpStorm.
 * User: zhaobin
 * Date: 18/1/16
 * Time: 14:27
 */

/**
 * @param $uid
 * @param $type
 * @param $info
 */
function trace_info($uid, $type, $info)
{
    _write_trace(intval($uid), $type, $info, '../runtime/trace/info/',date('Y-m-d'));
}

/**
 * @param $uid
 * @param $type
 * @param $info
 */
function trace_error($uid, $type, $info)
{
    _write_trace(intval($uid), $type, $info, './trace/error/',date('Y-m-d'));
}
/**
 * @author 1528
 * @param $uid
 * @param $type
 * @param $info
 * @param $dir
 * @param $file
 */
function _write_trace($uid,$type,$info,$dir,$file)
{
    if(!is_dir($dir))
    {
        mkdir($dir,0777,true);
    }
    if(!file_exists($file)){
        fopen($dir.$file,'a+');
    }

    if (is_writable($dir.$file)) {

        $string = "url[" .$_SERVER['HTTP_HOST'] . "]  date["
            . date('Y-m-d H:i:s', time()) . "] userId["
            . $uid . "] type["
            . $type . "] info["
            . var_export($info, true) . "]\n";
        file_put_contents($dir.$file,$string,FILE_APPEND);
    }

}