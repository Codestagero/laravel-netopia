<?php

namespace Codestage\Netopia;

class Netopia
{
    /**
     * The default customer model class name.
     *
     * @var class-string
     */
    public static string $customerModel = 'App\\Models\\User';

    /**
     * Set the customer model class name.
     *
     * @param  class-string  $customerModel
     * @return void
     */
    public static function useCustomerModel(string $customerModel): void
    {
        static::$customerModel = $customerModel;
    }
}
