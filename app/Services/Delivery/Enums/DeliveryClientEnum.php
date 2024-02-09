<?php

namespace App\Services\Delivery\Enums;

use App\Enums\DeliveryPlatform\ADeliveryPlateforms;

abstract class DeliveryClientEnum {

    const BRISK = 1;
    const OWN_DELIVERY = 2;

    public static function getConstants()
    {
        $reflectionClass = new \ReflectionClass(self::class);
        return $reflectionClass->getConstants();
    }

    public static function isExists($value){
        return in_array($value,self::getConstants());
    }

    public static function isNotExists($value){
        return !self::isExists($value);
    }


    public static function getDeliveryPlatform($value) {
        if ($value == DeliveryClientEnum::BRISK) {
            return ADeliveryPlateforms::BRISK;
        } else {
            return ADeliveryPlateforms::OWN_DELIVERY;
        }
    }
}
