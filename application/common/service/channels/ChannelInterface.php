<?php

namespace app\common\service\channels;

interface ChannelInterface
{
    // config
    public function config() : array;

    // pay
    public function pay($channel, $params) : array;

    // outPay
    public function outPay($channel, $params) : array;

    // payNotify
    public function payNotify($channel, $params) : array;

    //outPayNotify
    public function outPayNotify($channel, $params) : array;

    // 返回
    public function response() : string;

    // getNotifyType
    public function getNotifyType($params) : string;

    // getPayInfo
    public function getPayInfo($orderIn) : array;

    // parseVoucher
    public function parseVoucher($channel, $params) : array;

    // getVoucherUrl
    public function getVoucherUrl($params) : string;

}