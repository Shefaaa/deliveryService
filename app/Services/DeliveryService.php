<?php

namespace App\Services\Delivery;

use App\Services\Delivery\Clients\BriskDeliveryClient;
use App\Services\Delivery\Enums\DeliveryClientEnum;
use App\Services\Delivery\Exceptions\ClientException;
use App\Services\Delivery\Interfaces\DeliveryServiceInterface;
use App\Services\Delivery\Clients\OwnDeliveryClient;

class DeliveryService {

    public function __construct() {

    }

    public static function getInstance(int $client): ?DeliveryServiceInterface {

        if(DeliveryClientEnum::isNotExists($client)){
            throw new ClientException('Client are not valid.');
        }

        switch ($client) {
            case DeliveryClientEnum::BRISK:
                return BriskDeliveryClient::instance();
                break;
            default:
                return OwnDeliveryClient::instance();
                break;
        }

    }

}
