<?php
$code = 806302089253836506; //根据运单编号查询物流信息
$appid = ''; //商户号
$appkey = '';
$express = new \Zhangpeng\Express\Express($appid, $appkey);
$logisticResult = $express->getOrderInfoByJson($code);
if (is_array($logisticResult)) {
var_dump($logisticResult); //打印物流信息
}else {
    echo $logisticResult; //输出错误信息
}
