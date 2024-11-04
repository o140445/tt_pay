<?php

namespace app\common\service;

use app\common\service\channels\APayChannel;
use app\common\service\channels\ChannelInterface;

class PaymentService
{
    protected ChannelInterface $channel;

    public function __construct(string $code)
    {
       switch ($code) {
           case 'APay':
               $this->channel = new APayChannel();
               break;
           default:
               throw new \Exception('未知支付渠道');
       }
    }

    public function pay($channel, $data)
    {
        return $this->channel->pay($channel, $data);
    }

    public function outPay($channel, $data)
    {
        return $this->channel->outPay($channel, $data);
    }

    public function payNotify($channel, $data)
    {
        return $this->channel->payNotify($channel, $data);
    }

    public function config()
    {
        return $this->channel->config();
    }

    public function outPayNotify($channel, $data)
    {
        return $this->channel->outPayNotify($channel, $data);
    }

    public function response()
    {
        return $this->channel->response();
    }
}
