<?php

namespace App\Services\Delivery\Data ;



use Illuminate\Support\Arr;

class DeliveryOrderItem {

    public $name;
    public $description;
    public $quantity;
    public $price;
    public $image;

    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function setParams($params){
        $this->name         = Arr::get($params,'name');
        $this->description  = Arr::get($params,'description');
        $this->quantity     = Arr::get($params,'quantity');
        $this->price        = Arr::get($params,'price');
        $this->image        = Arr::get($params,'image');
    }

    public function toArray(array $keys=[]){
        $items=[
            'name'          =>$this->name,
            'description'   =>$this->description,
            'quantity'      =>$this->quantity,
            'price'         =>$this->price,
            'image'         =>$this->image,
        ];

        foreach($keys as $currentKey=>$newKey){
            $value=Arr::pull($items,$currentKey);
            Arr::set($items,$newKey,$value);
        }
        return $items;
    }

}
