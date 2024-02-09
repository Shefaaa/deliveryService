<?php

namespace App\Services\Delivery\WebHooks\Hyper;

use App\Constants\General;
use App\Enums\Markets\AFailedToAssignAction;
use App\Helper;
use App\OrderBatch;
use App\Services\Delivery\Data\DeliveryOrderCallback;
use Integration\Models\DeliveryOrder;
use App\Order;
use App\Enums\MiniMarket\AHyperMarketOrderStatus;
use App\Services\Delivery\Enums\DeliveryCallBackStatusEnum;
use App\Enums\AEmployeeType;
use App\OrderActivity;
use App\Enums\DeliveryPlatform\ADeliveryPlateforms;
use App\Enums\Orders\OrderOnTheWayStatusEnum;
use App\Enums\Orders\OrderActivityUserTypeEnum;

class FailedToAssign {


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
    public function __construct($deliveryClient, $deliveryOrder,$requestData) {

        $this->deliveryOrder=$deliveryOrder;
        $this->order=$deliveryOrder->order;
        $this->requestData=$requestData;
        $this->canUpdateOrder=$this->deliveryOrder->isLastUpdateBefore($this->requestData->getReceivedAt());
    }

    public function handle() {

        if($this->canUpdateOrder){
            $this->order->update([
                'order_status_id' => AHyperMarketOrderStatus::CANCELED
            ]);

            $this->deliveryOrder->update([
                "platform_order_status"=>$this->requestData->getStatus(),
                "platform_received_at"=>$this->requestData->getReceivedAt(),
                "platform_captain_id"=>$this->requestData->getCaptainId(),
                "platform_captain_mobile"=>$this->requestData->getCaptainMobile(),
                "platform_captain_name"=>$this->requestData->getCaptainName(),
                "tracking_url"=>$this->requestData->getTrackingUrl(),
            ]);

            OrderActivity::create([
                'order_id' => $this->order->id,
                'role' => OrderActivityUserTypeEnum::SYSTEM,
                'order_status_id' => AHyperMarketOrderStatus::CANCELED,
                'user_id' => General::SYSTEM_USER_ID,
            ]);
        }

    }
}
