<?php


namespace App\Services\Delivery\Enums;

abstract class DeliveryTaskTypeEnum
{

    const PICK_UP = 1;
    const DELIVERY = 2;
    const ON_DEMAND_PICK_UP = 3;


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
}
