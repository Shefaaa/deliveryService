<?php

namespace App\Services\Delivery\Data;


use App\Services\Delivery\Enums\DeliveryTaskTypeEnum;
use App\Services\Delivery\Exceptions\TaskTypeException;
use Illuminate\Support\Arr;

class DeliveryOrderTask
{

    public $name;
    public $description;
    public $address_description;
    public $image;
    public $rank;
    public $pay_at_pickup;
    public $collect_at_delivery;
    public $task_type;
    public $email;
    public $phone;
    public $latitude;
    public $longitude;
    public $task_datetime;
    public $items=[];
    public $address=[];

    public function __construct(array $params)
    {
        $this->setParams($params);
    }

    public function setParams($params){
        $this->name                 = Arr::get($params,'name');
        $this->description          = Arr::get($params,'description');
        $this->image                = Arr::get($params,'image');
        $this->rank                 = Arr::get($params,'rank');
        $this->pay_at_pickup        = Arr::get($params,'pay_at_pickup');
        $this->collect_at_delivery  = Arr::get($params,'collect_at_delivery');
        $this->email                = Arr::get($params,'email');
        $this->phone                = Arr::get($params,'phone');
        $this->latitude             = Arr::get($params,'latitude');
        $this->longitude            = Arr::get($params,'longitude');
        $this->task_datetime        = Arr::get($params,'task_datetime');
        $this->items                = Arr::get($params,'items',[]);
        $this->address_description  = Arr::get($params,'address_description');
        $this->address              = Arr::get($params,'address',[]);

        $this->setTaskType(Arr::get($params,'task_type',''));

    }

    public function toArray(array $keys=[]){
        $items=[
            'name'                  =>$this->name,
            'description'           =>$this->description,
            'image'                 =>$this->image,
            'rank'                  =>$this->rank,
            'pay_at_pickup'         =>$this->pay_at_pickup,
            'collect_at_delivery'   =>$this->collect_at_delivery,
            'task_type'             =>$this->getTaskType(),
            'email'                 =>$this->email,
            'phone'                 =>$this->phone,
            'latitude'              =>$this->latitude,
            'longitude'             =>$this->longitude,
            'task_datetime'         =>$this->task_datetime,
            'items'                 =>$this->items,
            'address_description'   =>$this->address_description,
            'address'               =>$this->address,
        ];

        foreach($keys as $currentKey=>$newKey){
            $value=Arr::pull($items,$currentKey);
            Arr::set($items,$newKey,$value);
        }
        return $items;
    }

    public function setTaskType(string $value){

        if($value && DeliveryTaskTypeEnum::isNotExists($value)){
            throw new TaskTypeException('Task Type are not valid.');
        }
        $this->task_type=$value;
    }

    public function getTaskType(){
        return $this->task_type;
    }
}
