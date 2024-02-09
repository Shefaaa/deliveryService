<?php


namespace App\Services\Delivery\Enums;

abstract class DeliveryCallBackStatusEnum
{
	const ASSIGNED = 6;
    const COLLECTING_ORDER = 1;
    const ON_THE_WAY = 2;
    const DELIVERED = 4;
    const CANCELLED = 5;
    const FAILED_TO_ASSIGN = 9;
}
