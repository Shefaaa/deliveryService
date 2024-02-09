<?php

namespace App\Services\Delivery\Enums;

abstract class DeliveryPaymentTypeEnum {

    const CASH = 1;
    const MADA = 2;
    const WALLET = 3;
    const APPLE_PAY = 5;
    const MADA_ONLINE = 11;
    const VISA = 555;

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

    public static function getCashTypes(){
        return [
          self::CASH,
        ];
    }

    public static function getOnlineTypes(){
        return [
            self::MADA,
            self::WALLET,
            self::APPLE_PAY,
            self::MADA_ONLINE,
            self::VISA,
        ];
    }
}
