<?php

// 定义web目录位置
define('BASEDIR',dirname(__DIR__));

$app = require BASEDIR . '/App/app.php';

$app->goRun();
