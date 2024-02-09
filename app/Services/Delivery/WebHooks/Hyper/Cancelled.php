<?php

namespace App\Services\Delivery\WebHooks\Hyper;

use App\Services\Delivery\Data\DeliveryOrderCallback;
use App\Services\Delivery\Enums\DeliveryClientEnum;
use Integration\Models\DeliveryOrder;
use App\Order;
use App\Constants\General;

class Cancelled {


    /**
     * @var DeliveryOrderCallback
     */
    private $requestData;

    /**
     * @var Order
     */
    private $order;

    /**
     * @var DeliveryOrder
     */
    private $deliveryOrder;

    /**
     * @var boolean
     */
    private $canUpdateOrder;
    /**
     * @var DeliveryClientEnum
     */
    private $deliveryClient;
    public function __construct( $deliveryClient ,$deliveryOrder,$requestData) {

        $this->deliveryOrder=$deliveryOrder;
        $this->order=$deliveryOrder->order;
        $this->requestData=$requestData;
        $this->deliveryClient=$deliveryClient;
        $this->canUpdateOrder=$this->deliveryOrder->isLastUpdateBefore($this->requestData->getReceivedAt());
    }

    public function handle() {
        if($this->canUpdateOrder){
            $this->deliveryOrder->update([
                "platform_order_status"=>$this->requestData->getStatus(),
                "platform_received_at"=>$this->requestData->getReceivedAt(),
                "platform_captain_id"=>$this->requestData->getCaptainId(),
                "platform_captain_mobile"=>$this->requestData->getCaptainMobile(),
                "platform_captain_name"=>$this->requestData->getCaptainName(),
                "tracking_url"=>$this->requestData->getTrackingUrl(),
            ]);
        }

    }
}
