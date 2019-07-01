<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019-06-27
 * Time: 16:58
 */

/*
 * 没有对接VIVO的单推接口，直接使用多推的流程实现
 */



require_once '../vendor/autoload.php';

set_time_limit(0);
ini_set('display_errors', 'On');
error_reporting(E_ALL);

// 然后可以这样使用。
$title = "推送的消";
$message = "需要推送的消22222息内容";
$appid = '10004';
$appKey = '25509283-3767-4b9e-83fe-b6e55ac6243e';
$appSecret = '25509283-3767-4b9e-83fe-b6e55ac6243e';
$AccessTokenArr = ['1231312321', '123123123'];
$action_parameters = ['a' => 'b', 'c' => 'd'];
//$action_parameters = '';

$push = new \OppoPush\OppoPush($appid, $appKey, $appSecret, './abc.log');

//var_dump($push->getAccessToken());exit;
//var_dump($push->setTitle($title)->setcontent($message)->setaction_parameters($action_parameters)->broadcast());exit;

var_dump($push->setTitle($title)->setcontent($message)->setaction_parameters($action_parameters)->unicast_batch($AccessTokenArr));exit;