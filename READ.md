###Redis的使用

1. 支持多个redis的配置

     'redis' => array(
            'cluster' => false,
            'options' => array(
                \Redis::OPT_PREFIX => '',
                \Redis::OPT_SERIALIZER=> \Redis::SERIALIZER_PHP,
            ),
            'timeout' => 5,
            'default' => array(
                'host'     => '127.0.0.1',
                'port'     => 6379,
                'database' => 0,
            ),
            'redis32'=> array(
                'host'     => '127.0.0.2',
                'port'     => 6372,
                'database' => 4,
            ),
        ),

        在基础controller中使用$this->container->make('redis')
        $this->container->redis->get($key) ,是默认调用的default的连接
        如果需要调用redis32的连接 $this->container->redis->getClient('redis32')->get($key)

###服务注册配置说明

在config.php 中配置$config['service']

        $config['service'] = array(
            //alias => abstract
            'oauthServer'=>'App\Service\OAuth2\Server',
            'messageServer'=>'App\Service\Message\MessageService',
            'taskServer'=>'App\Service\Task\TaskService',

        );
        键为假名，值为服务对应的类
        启动服务只需要$this->make(假名) 就可以了

U_101  用户信息


###