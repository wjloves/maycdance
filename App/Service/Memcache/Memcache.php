<?php
namespace App\Service\Memcache;
/**
 * memcache 操作类
 *
 * @since 2011-03-14 10:28:41
 * $Id: pub_memcache.php 1 2013-07-18 09:24:31Z  $
 */

class Memcache{
    /**
     * Memcache 对象
     *
     * @var obj
     */
    protected static $mc = null;

    /**
     * 缓存前缀
     *
     * @var string
     */
    protected static $prefix = 'U';

    /**
     * 缓存键值配置
     *
     * @var array
     */
    protected static $cache_key_config = null;

    /**
     * 构造函数
     * @parem $key 服务器
     * @return resource
     */
    public static function init_memcache( $key='' )
    {
        // 如果多服务器使用memcached扩展
        if (!empty($GLOBALS['config']['memcache']['servers']))
        {

            if (empty(self::$mc['memcached']))
            {
                self::$mc['memcached'] = new \Memcached();
                self::$mc['memcached'] -> setOption(\Memcached::HAVE_IGBINARY, FALSE);
                self::$mc['memcached'] -> setOption(\Memcached::OPT_SERIALIZER, \Memcached::SERIALIZER_PHP);
                self::$mc['memcached'] -> addServers($GLOBALS['config']['memcache']['servers']);
                self::get_cache_key_config(); // 配置
            }
            return self::$mc['memcached'];
        }
        else
        {
            $k = empty($GLOBALS['config']['memcache'] ['mc'] [substr($key, 0, 1)]) ? 'default' : substr($key, 0, 1);

            if( self::$mc[$k] === null )
            {
                $mc_path = parse_url ( $GLOBALS['config']['memcache'] ['mc'] [$k] );

                self::$mc[$k] = new Memcache();
                self::$mc[$k]->connect ( $mc_path ['host'], $mc_path ['port'] );
                unset($mc_path);
                self::get_cache_key_config(); // 配置
            }

            return self::$mc[$k];
        }
    }

    /**
     * 缓存键值配置
     *
     * @return void
     */
    public static function get_cache_key_config()
    {

        if (empty(self::$cache_key_config))
        {
            include_once __CONFIG__.'/cacheList.php';// PATH_CONFIG . "/inc_cache_list.php";
            self::$cache_key_config =& $config['cache_list'];
        }
        return self::$cache_key_config;
    }

    /**
     * 获取缓存前缀
     *
     * @param string $prefix
     * @return string
     */
    private static function _get_cache_prefix( $prefix )
    {
        if( isset(self::$cache_key_config[$prefix]['prefix']) )
        {
            $prefix = self::$cache_key_config[$prefix]['prefix'];
        }
        else
        {
            // 写入错误日志
            //log_msg("Memcache Prefix {$prefix} not Defined.");
            trigger_error("Memcache Prefix {$prefix} not Defined.", E_USER_WARNING);
        }
        return self::$prefix. '_'. $prefix. '_';
    }

    /**
     * 获取缓存
     *
     * @param <string> $prefix
     * @param <string> $key
     * @return <string | array | bool >
     */
    public static function get($prefix, $key='')
    {
        if( empty($GLOBALS['config']['memcache']['is_mc_enable']) )
        {
            return false;
        }
        $m = self::init_memcache( $prefix );
        $prefix = self::_get_cache_prefix($prefix);
        $key = empty($key) ? $prefix : $prefix. $key;
        $return = $m->get( $key );
        return $return;
    }

    /**
     * 获取多个
     *
     * @param <type> $prefix
     * @param <array> $keys array($key1,$key2,...)
     * @return array array($key1=>$value1, $key2=>$value2,...)
     */
    public static function get_multi( $prefix, $key_array )
    {
        $return = array('is_all'=>0, 'data'=>array());
        if( empty($key_array) )
        {
            return $return;
        }
        if( !is_array($key_array))
        {
            $key_array = array($key_array);
        }

        $m = self::init_memcache( $prefix );

        $prefix = self::_get_cache_prefix($prefix);
        $key_array = array_unique($key_array);

        $list = $keys = array();
        foreach( $key_array as $key )
        {
            $return['data'][$key] = null;
            $keys[] = $prefix. $key;
        }

        if( isset($GLOBALS['config']['memcache']['is_mc_enable']) && $GLOBALS['config']['memcache']['is_mc_enable'] === true )
        {
            $list = isset(self::$mc['memcached']) ? $m->getMulti($keys) : $m->get( $keys );
            $list = is_array($list) ? $list : array();
            foreach( $list as $k=>$row )
            {
                $key = str_replace( $prefix, '', $k );
                $return['data'][$key] = $row;
            }
        }
        // 是否全部取到
        $return['is_all'] = count($key_array) == count($list) ? 1 : 0;
        return $return;
    }

    /**
     * 设置缓存
     *
     * @param <string> $prefix
     * @param <string> $key
     * @param <string|array> $value
     * @return bool
     */
    public static function set($prefix, $key, $value)
    {
        if( empty($GLOBALS['config']['memcache']['is_mc_enable']) )
        {
            return false;
        }

        $m = self::init_memcache( $prefix );

        $cache_prefix = self::_get_cache_prefix($prefix);
        $key = empty($key) ? $cache_prefix : $cache_prefix. $key;
        $timeout = intval(isset(self::$cache_key_config[$prefix])?self::$cache_key_config[$prefix]['timeout']:0);

        if (!isset(self::$mc['memcached']))
        {
            $compress= (is_bool($value) || is_int($value) || is_float($value)) ? false : MEMCACHE_COMPRESSED;
            return $m->set( $key, $value, $compress, $timeout );
        }
        else
        {
            return $m->set( $key, $value, $timeout );
        }

    }


    /**
     * @description 设置缓存,copy 上面的 set 方法兼容历史 pub_user_api _SN 方法
     * @param <string> $prefix
     * @param <string> $key
     * @param <string|array> $value
     * @return bool
     * @author Jarvis
     * @history Create 2016/02/29
     */
    public static function mc_set($prefix, $key, $value, $timeout=86400)
    {
        if( empty($GLOBALS['config']['memcache']['is_mc_enable']) )
        {
            return false;
        }

        $m = self::init_memcache( $prefix );

        $cache_prefix = MYAPI_MD_PREFIX . '_' . $prefix;

        $key = (false === $key) ? $cache_prefix : $cache_prefix . '_' .  $key;

        if (!isset(self::$mc['memcached']))
        {
            $compress= (is_bool($value) || is_int($value) || is_float($value)) ? false : MEMCACHE_COMPRESSED;
            return $m->set( $key, $value, $compress, $timeout );
        }
        else
        {
            return $m->set( $key, $value, $timeout );
        }

    }



    /**
     * 删除缓存
     *
     * @param <string> $prefix
     * @param <string> $key
     * @return bool
     */
    public static function del($prefix, $key='')
    {
        if( empty($GLOBALS['config']['memcache']['is_mc_enable']) )
        {
            return false;
        }

        $m = self::init_memcache( $prefix );

        $prefix = self::_get_cache_prefix($prefix);
        $key = empty($key) ? $prefix : $prefix. $key;

        return $m->delete( $key, 0 );
    }
}
