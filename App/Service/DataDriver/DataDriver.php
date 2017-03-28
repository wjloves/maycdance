<?php
namespace App\Service\DataDriver;

use App\Service\Base\ResourceOwner;
use Core\Request;

abstract class DataDriver
{
    /**
     * @description 这个是资源拥有者，即授权的用户
     * @var \App\Service\Base\ResourceOwner
     * */
    protected $resourceOwner;


    public function __construct(\App\Service\Base\ResourceOwner $resourceOwner)
    {
        $this->resourceOwner = $resourceOwner;
    }

}