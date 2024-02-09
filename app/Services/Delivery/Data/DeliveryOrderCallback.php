<?php

namespace App\Services\Delivery\Data;


use Illuminate\Support\Arr;

class DeliveryOrderCallback {

    public $deliveryId;
    public $orderId;
    public $status;
    public $receivedAt;
    public $captainId;
    public $captainName;
    public $captainMobile;
    public $trackingUrl;
    public $chatUrl;

    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function setParams($params){
        $this->deliveryId               = Arr::get($params,'delivery_id');
        $this->orderId                  = Arr::get($params,'order_id');
        $this->status                   = Arr::get($params,'status');
        $this->captainId                = Arr::get($params,'captain_id');
        $this->captainName              = Arr::get($params,'captain_name');
        $this->captainMobile            = Arr::get($params,'captain_mobile');
        $this->trackingUrl              = Arr::get($params,'tracking_url');
        $this->chatUrl                  = Arr::get($params,'chat_url');
        if(Arr::get($params,'received_at')){
            $this->receivedAt          = now()->timestamp(Arr::get($params,'received_at'));
        }
    }

    public function toArray(array $keys=[]){
        $items=[
            'deliveryId'            =>$this->deliveryId,
            'orderId'               =>$this->orderId,
            'status'                =>$this->status,
            'receivedAt'            =>$this->receivedAt,
            'captainId'             =>$this->captainId,
            'captainName'           =>$this->captainName,
            'captainMobile'         =>$this->captainMobile,
            'trackingUrl'           =>$this->trackingUrl,
            'chatUrl'               =>$this->chatUrl,
        ];

        foreach($keys as $currentKey=>$newKey){
            $value=Arr::pull($items,$currentKey);
            Arr::set($items,$newKey,$value);
        }
        return $items;
    }

    public function getOrderId(){
        return $this->orderId;
    }

    public function getDeliveryId(){
        return $this->deliveryId;
    }

    public function getStatus(){
        return $this->status;
    }

    public function getCaptainId(){
        return $this->captainId;
    }

    public function getCaptainName() {
        return $this->captainName;
    }

    public function getCaptainMobile() {
        return $this->captainMobile;
    }

    public function getTrackingUrl(){
        return $this->trackingUrl;
    }

    public function getChatUrl(){
        return $this->chatUrl;
    }

    public function getReceivedAt(){
        return $this->receivedAt;
    }

}
