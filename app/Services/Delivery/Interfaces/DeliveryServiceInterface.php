<?php

namespace App\Services\Delivery\Interfaces;

use App\Services\Delivery\Data\DeliveryOrderCallback;
use App\Services\Delivery\Data\DeliveryOrderRequest;

interface DeliveryServiceInterface
{

    public function createDeliveryOrder(DeliveryOrderRequest $order);

    public function getDeliveryOrder($deliveryOrderId);

    public function cancelDeliveryOrder($deliveryOrderId);

    public function onCallback(DeliveryOrderCallback $data);
}

