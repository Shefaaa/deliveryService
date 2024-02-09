<?php

namespace App\Services\Delivery\Data;

use App\Services\Delivery\Enums\DeliveryPaymentTypeEnum;
use App\Services\Delivery\Exceptions\PaymentTypeException;
use Illuminate\Support\Arr;

class DeliveryOrderRequest
{

    public $order_id     = "";
    public $payment_type = "";
    public $description  = "";
    public $items        = [];
    public $meta_data    = [];
    public $tasks        = [];
    public $order_type   = "";
    public $customer     =  [];


    public function __construct(array $params=[])
    {
        $this->setParams($params);

    }

    public function setParams($params){
        $this->setItems(Arr::get($params,'items',[]));
        $this->setMetaData(Arr::get($params,'meta_data',[]));
        $this->setTasks(Arr::get($params,'tasks',[]));
        $this->setPaymentType(Arr::get($params,'payment_type',''));
        $this->setDescription(Arr::get($params,'description',''));
        $this->setCustomer(Arr::get($params,'customer',[]));
        if(Arr::get($params,'order_id')){
            $this->setOrderId(Arr::get($params,'order_id'));
        }
        if(Arr::get($params,'order_type')) {
            $this->setOrderType(Arr::get($params, 'order_type'));
        }

    }

    public function setItems(array $items=[])
    {
        foreach($items as $item){
            $this->items[] = new DeliveryOrderItem($item);
        }
    }

    public function getItemsData(array $keys=[]){
        $items=$this->items;
        if(!empty($keys)){
            foreach ($items as &$item){
                /* @var DeliveryOrderTask $items  */
                $item=$item->toArray($keys);
            }
        }

        return $items;
    }

    public function setMetaData(array $items=[])
    {
        foreach($items as $item){
            $this->meta_data[] = new DeliveryOrderMetaData($item);
        }
    }

    public function getMetaData(array $keys=[]){
        $items=$this->meta_data;
        if(!empty($keys)){
            foreach ($items as &$item){
                /* @var DeliveryOrderTask $items  */
                $item=$item->toArray($keys);
            }
        }

        return $items;
    }

    public function setTasks(array $items=[])
    {
        foreach($items as $item){
            $this->tasks[] = new DeliveryOrderTask($item);
        }
    }

    public function getTasks(array $keys=[]){
        $items=$this->tasks;

        if(!empty($keys)){
            foreach ($items as &$item){
                /* @var DeliveryOrderTask $items  */
                $item=$item->toArray($keys);
            }
        }

        return $items;
    }

    public function setPaymentType(string $value){

        if($value && DeliveryPaymentTypeEnum::isNotExists($value)){
            throw new PaymentTypeException('Payment Type are not valid.');
        }
        $this->payment_type=$value;
    }

    public function getPaymentType(){
        return $this->payment_type;
    }

    public function setDescription(string $value){
        $this->description=$value;
    }

    public function getDescription(){
        return $this->description;
    }

    public function setOrderId(string $value){

        if(!$value){
            throw new \Exception('Order ID is required.');
        }
        $this->order_id=$value;
    }

    public function getOrderId(){
        return $this->order_id;
    }

    public function setOrderType(string $value){

        if(!$value){
            throw new \Exception('Order Type is required.');
        }
        $this->order_type=$value;
    }

    public function getOrderType(){
        return $this->order_type;
    }

    public function setCustomer(array $items=[]) {
        foreach($items as $item){
            $this->customer = new DeliveryCustomerData($item);
        }
    }

    public function getCustomer(array $keys=[]){
        $item=$this->customer;
        if(!empty($keys)){
            /* @var DeliveryOrderTask $items  */
            $item=$item->toArray($keys);
        }
        return $item;
    }


}
