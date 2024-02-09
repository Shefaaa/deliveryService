<?php


namespace App\Services\Delivery\Data;


use Illuminate\Support\Arr;

class DeliveryOrderMetaData {

    public $label;
    public $data;
    public $language_code;


    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function setParams($params){
        $this->label            = Arr::get($params,'label');
        $this->data             = Arr::get($params,'data');
        $this->language_code    = Arr::get($params,'language_code');
    }

    public function toArray(array $keys=[]){

        $items=[
            'label'             =>$this->label,
            'data'              =>$this->data,
            'language_code'     =>$this->language_code,
        ];

        foreach($keys as $currentKey=>$newKey){
            $value=Arr::pull($items,$currentKey);
            Arr::set($items,$newKey,$value);
        }
        return $items;
    }


}
