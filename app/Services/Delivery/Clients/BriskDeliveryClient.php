<?php

namespace App\Services\Delivery\Clients;

use App\Services\Delivery\Data\DeliveryOrderCallback;
use App\Services\Delivery\Data\DeliveryOrderRequest;
use App\Services\Delivery\Enums\DeliveryClientEnum;
use App\Services\Delivery\Enums\DeliveryPaymentTypeEnum;
use App\Services\Delivery\Enums\DeliveryTaskTypeEnum;
use App\Services\Delivery\Interfaces\DeliveryServiceInterface;
use App\Services\Http\Enums\HttpClientEnum;
use App\Services\Http\HttpService;
use Firebase\JWT\JWT;
use Illuminate\Support\Arr;
use Integration\Models\DeliveryOrder;
use App\Models\MiniMarket\MiniMarketOrder;
use App\Order;
use App\Constants\General;
use App\Services\Delivery\Helpers\DeliveryOrderCancellationHookHelper;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class BriskDeliveryClient implements DeliveryServiceInterface {

    private static $instance = null;

    /** Secret key for API integration
     * @var string
     */
    private $secret;

    /** Client ID - Delivery Platform ID
     * @var string
     */
    private $clientID;

    /** Delivery Platform "BRISK" Base URL
     * @var string
     */
    private $baseUrl;

    /** JWT time to live (value are in seconds)
     * @var integer
     */
    private $timeToLive=40;

    /**
     * @var Client
     */
    private $guzzleClient;

    /**
     * @var String
     */
    private $webHooksPrefixNamespace= "App\Services\Delivery\WebHooks";

    /** create order endPoint
     * @var string
     */
    private $createOrderEndPoint;

    /** cancel order endPoint
     * @var string
     */
    private $cancelOrderEndPoint;

    /** get order endPoint
     * @var string
     */
    private $getOrderEndPoint;

    /** update order endPoint
     * @var string
     */
    private $updateOrderTaskEndPoint;

    public function __construct() {

        $this->guzzleClient=HttpService::getInstance(HttpClientEnum::GUZZLE);

        /* Get credentials from .service-container && initial params*/
        $this->clientID =config('services.brisk.clientID');
        $this->secret =config('services.brisk.secret');
        $this->baseUrl = config('services.brisk.baseUrl');
        $this->createOrderEndPoint = $this->baseUrl.config('services.brisk.createOrderEndPoint');
        $this->cancelOrderEndPoint = $this->baseUrl.config('services.brisk.cancelOrderEndPoint');
        $this->getOrderEndPoint = $this->baseUrl.config('services.brisk.getOrderEndPoint');
        $this->updateOrderTaskEndPoint = $this->baseUrl.config('services.brisk.updateOrderTaskEndPoint');
    }


    /**
     * Send ready order to the Delivery Platform
     * @param $order
     * @return platform_order_details
     */
    public function createDeliveryOrder(DeliveryOrderRequest $order)
    {
        try{
            if($this->canSendOrderToDelivery($order)) {
                $response = $this->guzzleClient->post($this->createOrderEndPoint, [
                    'json' => $this->prepareData($order),
                    'query' => [
                        'client_id' => $this->clientID
                    ],
                    'headers' => [
                        'Authorization' => "Bearer " . $this->generateToken($this->secret, $this->timeToLive)
                    ]
                ]);
                $result = json_decode($response->getBody(), true);

                DeliveryOrder::create([
                    'order_type' => $order->getOrderType(),
                    'order_id' => $order->getOrderId(),
                    'platform_order_id' => data_get($result, 'id'),
                    'platform_received_at' => now()->parse(data_get($result, 'createdAt')),
                ]);
                return $result;
            }
        }catch(ClientException $e){
            return $e->getMessage()
        }
    }

    /**
     * Get order data from deliveryPlatform
     * @param $deliveryOrderId
     * @return json Response
     */
    public function getDeliveryOrder($deliveryOrderId) {

        try{
            $response = $this->guzzleClient->retry(10,30)->get(str_replace('{brisk_order_id}',$deliveryOrderId,$this->getOrderEndPoint), [
                'query' => [
                    'client_id' => $this->clientID
                ],
                'headers' => [
                    'Authorization' => "Bearer " . $this->generateToken($this->secret, $this->timeToLive),
                    'Content-Type' => 'application/json',
                ]
            ]);

            return json_decode($response->getBody(), true);

        }catch(ClientException $e){
            return $e->getMessage();
        }
    }

    /**
     *
     * @param $deliveryOrderId
     * @return json Response
     */
    public function cancelDeliveryOrder($deliveryOrderId) {
        try{
            $response = $this->guzzleClient->patch(str_replace('{brisk_order_id}',$deliveryOrderId,$this->cancelOrderEndPoint), [
                'query' => [
                    'client_id' => $this->clientID
                ],
                'headers' => [
                    'Authorization' => "Bearer " . $this->generateToken($this->secret, $this->timeToLive),
                    'Content-Type' => 'application/json',
                ]
            ]);
        }catch(ClientException $e){
            return $e->getMessage();
        }
    }


    /**
     * create object when selected deliveryPlatformType in DeliveryService Class
     * @return instance of BriskDeliveryClient
     */
    public static function instance() {
        if (self::$instance == null) {

            self::$instance = new BriskDeliveryClient();
        }
        return self::$instance;
    }


    /**
     * Generate tooken inorder to connect deliveryPlatform API
     * @param $secret
     * @param int $timeToLive
     * @return string
     */
    public function generateToken($secret,int $timeToLive=40){
        return $token=JWT::encode([
            'exp'=>time()+$timeToLive
        ],$secret);
    }

    /**
     * @param DeliveryOrderRequest $order
     * @return boolean
     */
    private function canSendOrderToDelivery(DeliveryOrderRequest $order){

        $deliveryOrder=DeliveryOrder::where([
            'order_type'=>$order->getOrderType(),
            'order_id'=>$order->getOrderId()
        ])->first();

        $sendingPossibility =!$deliveryOrder;

        if($deliveryOrder && $deliveryOrder->isFailedToAssign()){
            $sendingPossibility = $deliveryOrder->delete();
        }

        return $sendingPossibility;

    }

    /**
     * Prepare required data to send the order to the deliveryPlatform
     * @param  $order
     * @return Array
     */
    public function prepareData(DeliveryOrderRequest $order){

        $data=[
            "backendId"=>$order->getOrderId(),
            "paymentType"=>$this->getPaymentType($order->getPaymentType()),
            "metaData"=>$order->getMetaData(['language_code'=>'languageCode']),
            "tasks"=>$this->getTasks(
                $order->getTasks([
                    'pay_at_pickup'=>'payAtPickup',
                    'collect_at_delivery'=>'collectAtDelivery',
                    'task_type'=>'taskType'
                ])
            ),
            "customer"=>$order->getCustomer(['user_id'=>'backendId'])
        ];
        return $data;
    }

    /**
     * Inform the driver of collecting money
     * @param  $paymentType
     * @return String
     */
    public function getPaymentType($paymentType){

        if(in_array($paymentType,DeliveryPaymentTypeEnum::getCashTypes())){
            $paymentType='CASH_ON_DELIVERY';
        }elseif(in_array($paymentType,DeliveryPaymentTypeEnum::getOnlineTypes())){
            $paymentType='PREPAID';
        }

        return $paymentType;
    }

    /**
     * return array of order details: userAddress, items
     * @param  $tasks
     * @return Array
     */
    public function getTasks($tasks){

        foreach($tasks as &$task){
            if(in_array($task['taskType'],[DeliveryTaskTypeEnum::DELIVERY])) {
                $task['taskType'] = 'DELIVERY';
            }elseif(in_array($task['taskType'],[DeliveryTaskTypeEnum::PICK_UP])){
                $task['taskType'] = 'PICK_UP';
            }elseif(in_array($task['taskType'],[DeliveryTaskTypeEnum::ON_DEMAND_PICK_UP])){
                $task['taskType'] = 'ON_DEMAND_PICK_UP';
            }
            $taskAddress=Arr::get($task,'address',[]);
            if(!is_array($taskAddress) || empty($taskAddress)){
                $taskAddress=[
                    'email'=>Arr::get($task,'email'),
                    'phone'=>Arr::get($task,'phone'),
                    'description'=>Arr::get($task,'address_description'),
                    'latitude'=>Arr::get($task,'latitude'),
                    'longitude'=>Arr::get($task,'longitude'),
                ];
            }else{
                Arr::set($taskAddress,'description',Arr::pull($taskAddress,'address_description'));
                Arr::set($task,'address',$taskAddress);
            }
            $task=Arr::only($task,[
                'name','description','image','rank','payAtPickup',
                'collectAtDelivery','items','taskType','address'
            ]);
        }
        return $tasks;
    }

    /**
     * called from Brisk_called_back_webHook API
     * @param  $requestData
     * @return String
     */
    public function onCallback(DeliveryOrderCallback $requestData) {
        $deliveryOrder = DeliveryOrder::where('order_id',$requestData->getOrderId())
            ->where('platform_order_id',$requestData->getDeliveryId())->first();
        $success=true;
        if($deliveryOrder){
            $orderType = "Hyper";
            if(!$deliveryOrder->isHyperMarket()) {
                $orderType = "MiniMarket";
            }
            $success= self::callBackWebHooks($requestData, $deliveryOrder,$orderType);
        }
        return $success;
    }

    /**
     * execute webHook class and update data
     * @param  $requestData
     * @param  $deliveryOrder
     * @param  $type
     *
     */
    public function callBackWebHooks($requestData, $deliveryOrder,$type) {

        try{
            $class="";
            switch ($requestData->getStatus()){
                case "ASSIGNED":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."Assigned";
                    break;
                case "COLLECTING_ORDER":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."CollectingOrder";
                    break;
                case "ON_THE_WAY":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."OnTheWay";
                    break;
                case "DELIVERED":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."Delivered";
                    break;
                case "CANCELED":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."Cancelled";
                    break;
                case "FAILED_TO_ASSIGN":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."FailedToAssign";
                    break;
                case "RE_ASSIGNING":
                    $class=$this->webHooksPrefixNamespace."\\$type\\"."ReAssign";
                    break;
            }
            if($class){
                (new $class(DeliveryClientEnum::BRISK , $deliveryOrder,$requestData))->handle();
            }
            return true;

        }catch (\Exception $exception){
            return $exception->getMessage();
        }

    }

}
