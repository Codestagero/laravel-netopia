<?php

namespace iRealWorlds\Netopia\Traits;

use iRealWorlds\Netopia\Enums\AddressType;
use Netopia\Payment\Address;

trait HasAddress
{
    /**
     * Get the Address Type used for Netopia.
     *
     * @return AddressType
     */
    public abstract function netopiaAddressType(): AddressType;

    /**
     * Get the first name used for Netopia.
     *
     * @return string
     */
    public abstract function netopiaAddressFirstName(): string;

    /**
     * Get the last name used for Netopia.
     *
     * @return string
     */
    public abstract function netopiaAddressLastName(): string;

    /**
     * Get the address text used for Netopia.
     *
     * @return string
     */
    public abstract function netopiaAddressText(): string;

    /**
     * Get the e-mail address used for Netopia.
     *
     * @return string
     */
    public abstract function netopiaEmail(): string;

    /**
     * Get the phone number used for Netopia.
     *
     * @return string
     */
    public abstract function netopiaPhone(): string;

    /**
     * Get this entity's address used for Netopia.
     *
     * @return Address
     */
    public function netopiaAddress(): Address
    {
        $address = new Address();
        $address->type = $this->netopiaAddressType()->value;
        $address->firstName = $this->netopiaAddressFirstName();
        $address->lastName = $this->netopiaAddressLastName();
        $address->address = $this->netopiaAddressText();
        $address->email = $this->netopiaEmail();
        $address->mobilePhone = $this->netopiaPhone();
        return $address;
    }
}
