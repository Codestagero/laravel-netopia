<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Enums\AddressType;
use Illuminate\Contracts\Support\Jsonable;
use JetBrains\PhpStorm\ArrayShape;
use JsonSerializable;
use Netopia\Payment\Address as NetopiaAddress;
use function is_array;
use function is_string;

class Address implements Jsonable, JsonSerializable
{
    /**
     * This address' type.
     *
     * @var AddressType|null
     */
    public AddressType|null $type = null;

    /**
     * This first name of the entity this address is for.
     *
     * @var string|null
     */
    public string|null $firstName = null;

    /**
     * This last name of the entity this address is for.
     *
     * @var string|null
     */
    public string|null $lastName = null;

    /**
     * This address' text directions.
     *
     * @var string|null
     */
    public string|null $address = null;

    /**
     * This e-mail address of the entity this address is for.
     *
     * @var string|null
     */
    public string|null $email = null;

    /**
     * This mobile phone number of the entity this address is for.
     *
     * @var string|null
     */
    public string|null $phoneNumber = null;

    /**
     * Address constructor method.
     *
     * @param array $initial Initial data that is to be assigned to the new entity.
     */
    public function __construct(
        #[ArrayShape(['type' => '\Codestage\Netopia\Enums\AddressType|string|null', 'firstName' => 'string|null', 'lastName' => 'string|null', 'address' => 'string|null', 'email' => 'string|null', 'phoneNumber' => 'string|null'])]
        array $initial = []
    ) {
        if (isset($initial['type'])) {
            if (is_string($initial['type'])) {
                $this->type = AddressType::tryFrom($initial['type']);
            } else {
                $this->type = $initial['type'];
            }
        }

        if (isset($initial['firstName'])) {
            $this->firstName = $initial['firstName'];
        }

        if (isset($initial['lastName'])) {
            $this->lastName = $initial['lastName'];
        }

        if (isset($initial['address'])) {
            $this->address = $initial['address'];
        }

        if (isset($initial['email'])) {
            $this->email = $initial['email'];
        }

        if (isset($initial['phoneNumber'])) {
            $this->phoneNumber = $initial['phoneNumber'];
        }
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>, which is a value of any type other than a resource.
     */
    #[ArrayShape(['type' => 'mixed', 'firstName' => 'mixed', 'lastName' => 'mixed', 'address' => 'mixed', 'email' => 'mixed', 'phoneNumber' => 'mixed'])]
    public function jsonSerialize(): array
    {
        return [
            'type' => $this->type,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'address' => $this->address,
            'email' => $this->email,
            'phoneNumber' => $this->phoneNumber,
        ];
    }

    /**
     * Specify data which should be deserialized from JSON.
     *
     * @return Address|null data which can be deserialized from the JSON representation.
     */
    #[ArrayShape(['type' => 'mixed', 'firstName' => 'mixed', 'lastName' => 'mixed', 'address' => 'mixed', 'email' => 'mixed', 'phoneNumber' => 'mixed'])]
    public static function jsonDeserialize(string $json): self|null
    {
        $decoded = json_decode($json, true);

        if (is_array($decoded)) {
            return new self($decoded);
        } else {
            return null;
        }
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode($this, $options);
    }

    /**
     * Convert the object to its Netopia representation.
     *
     * @return NetopiaAddress
     */
    public function toNetopia(): NetopiaAddress
    {
        $address = new NetopiaAddress();
        $address->type = $this->type;
        $address->firstName = $this->firstName;
        $address->lastName = $this->lastName;
        $address->address = $this->address;
        $address->email = $this->email;
        $address->mobilePhone = $this->phoneNumber;

        return $address;
    }
}
