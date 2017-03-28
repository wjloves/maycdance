<?php

namespace Core;

use Illuminate\Container\Container;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResqonse;

class Controller
{
    protected $twig;
    protected $request;
    protected $response;

    /**
     * 初始化，注入container
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;

        // 初始化 DB类 初始化Redis TODO 应该用 事件监听的方式来初始化 因为有可能在不需要db的情况下也初始化了
        $container->make('db');
        $container->make('redis');
        $this->request = $container->make('request');
        if(method_exists($this, '__init__'))
        {
            return call_user_func_array(array($this, '__init__'),array());
        }
    }

    /**
     * 渲染模板
     *
     * @param $tpl
     * @param $params
     * @return SymfonyResqonse
     */
    public function render($tpl, $params = [])
    {
        $twig = $this->container->make('view');
        // 必须以html.twig结尾
        return new SymfonyResqonse($twig->render($tpl . '.html.twig', $params, true));
    }

    public  function  Orequest($requestUrl=''){
        $url = $this->container->make('url');
        return new RedirectResponse($url->route($requestUrl));
    }

    /**
     * 消息显示页面
     * @param string $message   返回字段
     * @param string $url       跳转链接
     * @param bool|false $callBack    是否跳转
     * @return SymfonyResqonse
     */
    public function showMessage($message = '',$url= '',$callBack=false)
    {
         return $this->render('showMessage' ,array('msg' => $message , 'url'=>$url));
    }


    /**
     * 给变量赋值
     *
     * @param string|array $var
     * @param string $value
     */
    public function assign($var, $value = NULL)
    {
        if(is_array($var)) {
            foreach($var as $key => $val) {
                $this->data[$key] = $val;
            }
        } else {
            $this->data[$var] = $value;
        }
    }

}
