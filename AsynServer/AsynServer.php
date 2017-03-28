<?php
namespace AsynServer;
use App\Models\AccessStat;
use App\Service\Helper\Helper;

/**
 * @description 后台一直运行的异步服务器，用于接收异步处理统计更新
 * */
class AsynServer
{
    /**
     * @description 应用的对象
     * @var \Core\Application
     * */
    private $app;

    /**
     * @description swoole服务的示例
     * */
    private $sw;
    /**
     * @description 实例化
     * @param \Core\Application
     * */
    public function __construct($app)
    {
        $this->app = $app;
        $this->app->make('db');
        $this->app->make('redis');
        $this->sw = new \swoole_server($app->config['server']['host'] , $app->config['server']['port'] );

    }

    /**
     * @description 开启服务
     * */
    public function run()
    {
        $this->sw->set($this->app->config['server']['setting']);
        $version = explode('.', SWOOLE_VERSION);
        $this->sw->on('Start', array($this, 'onMasterStart'));
        $this->sw->on('Shutdown', array($this, 'onMasterStop'));
//        $this->sw->on('ManagerStop', array($this, 'onManagerStop'));
        $this->sw->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->sw->on('Connect', array($this, 'onConnect'));
        $this->sw->on('Receive', array($this, 'onReceive'));
        $this->sw->on('Close', array($this, 'onClose'));
        $this->sw->on('WorkerStop', array($this, 'onShutdown'));
        if (is_callable(array($this, 'onTimer')))
        {
            $this->sw->on('Timer', array($this, 'onTimer'));
        }
        if (is_callable(array($this, 'onTask')))
        {
            $this->sw->on('Task', array($this, 'onTask'));
            $this->sw->on('Finish', array($this, 'onFinish'));
        }
        $this->sw->start();
    }
    public function onMasterStart($serv)
    {

    }
    public function onMasterStop($serv)
    {

    }
    public function onWorkerStart($serv , $work_id)
    {
        echo 1;
        $this->sw->addtimer(2000);
    }

    public function checkStatRedisData()
    {
//        echo 1;
//        var_dump(Helper::getRedis() , $this->app->config['config.accessRecord']['listKey']);
        $stat = Helper::getRedis()->rPop($this->app->config['config.accessRecord']['listKey']);
        if($stat !== false)
        {
            //进行数据更新
            list($clientId ,$connectType )= explode(':' , $stat);

            AccessStat::incrementTimes($clientId ,$connectType );

        }
        unset($stat);
    }

    public function onTimer($time_id ,$params  )
    {
        $this ->checkStatRedisData();
    }
    public function onConnect()
    {

    }
    public function onReceive()
    {

    }
    public function onClose()
    {

    }
    public function onShutdown()
    {

    }

    public function shutdown()
    {
        return $this->sw->shutdown();
    }

    public function close($client_id)
    {
        return $this->sw->close($client_id);
    }

    public function addListener($host, $port, $type)
    {
        $this->sw->addlistener($host, $port, $type);
    }

    public function send($client_id, $data)
    {
        return $this->sw->send($client_id, $data);
    }
}
