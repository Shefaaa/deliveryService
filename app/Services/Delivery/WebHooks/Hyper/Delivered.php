<?php

namespace App\Services\Delivery\WebHooks\Hyper;

use App\Jobs\PushNotifications;
use App\Mail\OrderShipped;
use App\Services\CorporateGroup\CorporateService;
use App\User;
use App\Services\Delivery\Data\DeliveryOrderCallback;
use App\Services\Delivery\Enums\DeliveryClientEnum;
use Integration\Models\DeliveryOrder;
use App\Order;
use App\Enums\MiniMarket\AHyperMarketOrderStatus;
use App\Services\Delivery\Enums\DeliveryCallBackStatusEnum;
use App\Enums\AEmployeeType;
use App\OrderActivity;
use App\Enums\DeliveryPlatform\ADeliveryPlateforms;
use App\Enums\Orders\OrderOnTheWayStatusEnum;
use App\Enums\Orders\OrderActivityUserTypeEnum;
use Illuminate\Support\Facades\Mail;
use App\Services\Order\FundUserWithWalletItem;

class Delivered {


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

    public function __construct($deliveryClient,$deliveryOrder,$requestData) {

        $this->deliveryOrder=$deliveryOrder;
        $this->order=$deliveryOrder->order;
        $this->requestData=$requestData;
        $this->deliveryClient=$deliveryClient;
        $this->canUpdateOrder=$this->deliveryOrder->isLastUpdateBefore($this->requestData->getReceivedAt());
    }

    public function handle() {

        if($this->canUpdateOrder){
            $this->order->update([
                'order_status_id' => AHyperMarketOrderStatus::DELIVERED,
                'on_the_way_status_id' => DeliveryCallBackStatusEnum::DELIVERED,
                'delivery_platform' => DeliveryClientEnum::getDeliveryPlatform($this->deliveryClient)
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
                'role' => OrderActivityUserTypeEnum::DRIVER,
                'order_status_id' => AHyperMarketOrderStatus::DELIVERED,
                'employee_type' => AEmployeeType::DRIVER,
                'order_driver_status' => OrderOnTheWayStatusEnum::SUCCESSFUL,
                'delivery_platform' => DeliveryClientEnum::getDeliveryPlatform($this->deliveryClient),
            ]);
        }


        $user = User::find($this->order->user_id);

        $fundUserWithWalletItem = new FundUserWithWalletItem();
        $fundUserWithWalletItem->handelFundDeservedAmountToUser($this->order);


        if ($user->corporate) {
            CorporateService::calculateConsumedAmount($user, $this->order);
        }

        if ($user->email) {
            Mail::to($user->email)->queue((new OrderShipped($user,$this->order))->onQueue('emailHandler'));
        }

        $this->order->sendStatusNotification('Delivered');

    }
}
