<?php


namespace App\Services\Delivery\Data;


use Illuminate\Support\Arr;

class DeliveryCustomerData {

    public $user_id;
    public $name;
    public $devices = [];


    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function setParams($params){
        $this->user_id            = Arr::get($params,'user_id');
        $this->name               = Arr::get($params,'name');
        $this->devices            = Arr::get($params,'devices', null);
    }

    public function toArray(array $keys=[]){

        $items=[
            'user_id'             =>$this->user_id,
            'name'                =>$this->name,
            'devices'             =>$this->devices,
        ];

        foreach($keys as $currentKey=>$newKey){
            $value=Arr::pull($items,$currentKey);
            Arr::set($items,$newKey,$value);
        }
        return $items;
    }


}
