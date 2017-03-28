<?php

namespace Core;

use Illuminate\Container\Container;
use App\Service\Commands\CommandContext;
/**
 * 服务基类
 *
 * Class Service
 * @package Core
 */
class Service
{
    /**
     * 容器注入
     * @var
     */
    protected $container;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }


}