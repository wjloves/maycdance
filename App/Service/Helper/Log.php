<?php
namespace App\Service\Helper;
use App\Service\Helper\Helper;
/**
 * 记录日志
 *
 * @author skey <cookphp@gmail.com>
 */
class Log{
    // 日志记录方式
    protected static $type = array('system', 'mail', 'tcp', 'file');

    // 日期格式
    protected static $date_format =  '[ c ]';

    // 文件大小限制
    protected static $file_size_limit = '1024';

    // 日志等级
    protected static $level = array('RECORD', 'EMERG', 'ALERT', 'CRIT', 'ERR', 'WARN', 'NOTICE');

    // 文件日志配置
    protected static $inc_log = null;
    // 格式：array(目录, 目录描述，错误级别)


    /**
     * 日志保存
     *
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string $extra 额外参数
     * @return void
     */
    public static function save($param = array())
    {

        /*
        0 RECORD // 程序执行日志
        1 EMERG  // 严重错误: 导致系统崩溃无法使用
        2 ALERT  // 警戒性错误: 必须被立即修改的错误
        3 CRIT   // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
        4 ERR    // 一般错误: 一般性错误
        5 WARN   // 警告性错误: 需要发出警告的错误
        6 NOTICE // 通知: 程序可以运行但是还不够完美的错误


        $param['level'] = array(1, 2, 3, 4, 5, 6);  // 错误等级
        $param['message'] = '';                     // 错误日志
        $param['date_foramt'] = '[ Y-m-d h:m:i ]';  // 日期格式
        $param['file'] = '100001';                  // 存储路径
        */

        if(empty($param['date_format']))
        {
            $param['date_format'] = self::$date_format;
        }

        // 记录日志类型
        if (empty($param['type']) or !in_array($param['type'], self::$$type))
        {
            $param['type'] = 'file';
        }

        if(empty($param['key']))
        {
            return false;
        }

        switch ($param['type'])
        {
            case 'file':

                // 获取配置文件
                $config = self::get_config($param['key']);
                if(empty($config))
                {
                    file_put_contents( PATH_STATIC_LOG . '/inc_log.log', "[".date('Y-m-d H:i:s')."] "." | ".implode(',', $param));
                    die("inc_log key not exist!");
                }
                $param['level'] = $config['level'];
                $param['path'] = $config['path'];

                // 文件方式记录日志信息
                if(!empty($param['message']) or in_array($param['level'], array(0, 1, 2, 3, 4, 5, 6)))  {
                    $param['log'] = date($param['date_format'])." [ ".self::$level[$param['level']]." ]: {$param['message']}\r\n";
                }
                else
                {
                    return false;
                }
                return self::save_file($param);
                break;
            default:
                break;
        }
        return false;
    }

    /**
     * 存储为文件
     *
     * @param array $param
     * @return bool
     */
    protected static function save_file($param)
    {
        if(self::path_exists(dirname($param['path'])) === false)
        {
            return false;
        }
        /*
        if(is_file($path) && self::$file_size_limit <= filesize($path))
        {
        rename($path, dirname($path).'/'.time().'-'.basename($path)); // 检测日志文件大小，超过配置大小则备份日志文件重新生成
        }
        */
        return error_log($param['log'], 3, $param['path']);
    }

    /* 路径是否存在? */
    protected  static  function path_exists($path){
        $pathinfo = pathinfo ( $path . '/tmp.txt' );
        if (! empty ( $pathinfo ['dirname'] ))
        {
            if (file_exists ( $pathinfo ['dirname'] ) === false)
            {
                if (mkdir ( $pathinfo ['dirname'], 0777, true ) === false)
                {
                    return false;
                }
            }
        }
        return $path;
    }
    /**
     * 获取文件日志存放路径
     *
     * @return string
     */
    protected static function get_config($key)
    {
        $_logname_month = date('Y-m') . '.log';
        $_logname_month_day = date('Y-m') . '/' . date('Y-m-d') . '.log';
        $inc_log = array();

        // 用户模块
        $inc_log['2000001'] = array('user/ation_log.log', '用户模块/用户操作动态', 0);
        $inc_log['2000002'] = array('user/reg.log', '用户模块/注册用户', 0);
        $inc_log['2000003'] = array('user/login.log', '用户模块/用户登录', 0);
        $inc_log['2000004'] = array('user/user_detail.log', '用户模块/用户详细', 0);
        $inc_log['2000006'] = array('user/user_api_server_history'.$_logname_month_day, '用户模块/用户API服务流水/天', 0);
        $inc_log['2000007'] = array('user/user_api_server_err.log', '用户模块/用户API服务错误日志', 0);

        $inc_log['2000060'] = array('api/api_xy_bbs_login_'.$_logname_month_day, 'API模块/论坛用户API登录日志', 0);
        $inc_log['2000061'] = array('api/api_xy_bbs_login_fail.log', 'API模块/论坛用户API登录失败日志', 0);

        // 其它类型
        $inc_log['4000001'] = array('path_exist.log', '新建目录失败日志', 1);
        $inc_log['4000002'] = array('mysql/mysql_error.log', 'MySQL错误日志/MySQL错误日志', 1);
        $inc_log['4000004'] = array('search/search.log', '搜索错误日志/搜索错误日志', 1);
        $inc_log['4000005'] = array('upload/upload.log', '上传错误日志/上传错误日志', 1);
        $inc_log['4000006'] = array('api/api_error/api.log', 'API错误日志/API错误日志', 0);
        $inc_log['4000008'] = array('mail/'.$_logname_month, '邮件错误日志/邮件发送错误日志', 0);
        $inc_log['4000009'] = array('sms/'.$_logname_month, '短信错误日志/短信发送错误日志', 0);
        $inc_log['4000010'] = array('api/api_link.log'.$_logname_month_day, '接口调用日志', 0);

        //积分
        $inc_log['5000001'] = array('score/api_user_score'.$_logname_month_day,'用户积分接口调用日志',0);

        $data = $inc_log;
        if(key_exists($key, $data)) {
            return array('path' => PATH_STATIC_LOG . '/'.$data[$key][0], 'level' => $data[$key][2]);
        } else {
            return false;
        }
    }
}
?>