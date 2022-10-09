<?php

namespace Codestage\Netopia\Entities;

use Codestage\Netopia\Enums\AddressType;
use Illuminate\Contracts\Support\Jsonable;
use Netopia\Payment\Address as NetopiaAddress;

class Address implements Jsonable
{
    public AddressType $type;
    public string $firstName;
    public string $lastName;
    public string $address;
    public string $email;
    public string $phone;
    public string $city;
    public string $county;
    public string $country;
    public string $postCode;

    public function __construct(array $initial = [])
    {
        if (isset($initial['type'])) {
            if ($initial['type'] instanceof AddressType) {
                $this->type = $initial['type'];
            } else {
                $this->type = AddressType::from($initial['type']);
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

        if (isset($initial['phone'])) {
            $this->phone = $initial['phone'];
        }

        if (isset($initial['city'])) {
            $this->city = $initial['city'];
        }

        if (isset($initial['county'])) {
            $this->county = $initial['county'];
        }

        if (isset($initial['country'])) {
            $this->country = $initial['country'];
        }

        if (isset($initial['postCode'])) {
            $this->postCode = $initial['postCode'];
        }
    }

    /**
     * Convert this address to a SoapAddress object.
     *
     * @return SoapAddress
     */
    public function toSoapAddress(): SoapAddress
    {
        return new SoapAddress([
            'country' => $this->country,
            'county' => $this->county,
            'city' => $this->city,
            'address' => $this->address,
            'postal_code' => $this->postCode,
            'first_name' => $this->firstName,
            'last_name' => $this->lastName,
            'phone' => $this->phone,
            'email' => $this->email,
        ]);
    }

    /**
     * Convert this address to a NetopiaAddress object.
     *
     * @return NetopiaAddress
     */
    public function toPaymentAddress(): NetopiaAddress
    {
        $address = new NetopiaAddress();
        $address->type = $this->type;
        $address->firstName = $this->firstName;
        $address->lastName = $this->lastName;
        $address->address = join(', ', array_filter([
            $this->address,
            $this->city,
            $this->county,
            $this->country,
            $this->postCode
        ], fn (mixed $v) => $v && (!\is_string($v) || $v !== '')));
        $address->email = $this->email;
        $address->mobilePhone = $this->phone;

        return $address;
    }

    /**
     * Convert the object to its JSON representation.
     *
     * @param int $options
     * @return string
     */
    public function toJson($options = 0): string
    {
        return json_encode([
            'type' => $this->type,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'address' => $this->address,
            'email' => $this->email,
            'phone' => $this->phone,
            'city' => $this->city,
            'county' => $this->county,
            'country' => $this->country,
            'postCode' => $this->postCode,
        ], $options);
    }

    /**
     * Convert a JSON representation of this class to an actual object.
     *
     * @param string $json
     * @return Address|null
     */
    public static function fromJson(string $json): ?Address
    {
        $decoded = json_decode($json, true);

        if ($decoded) {
            return new self($decoded);
        } else {
            return null;
        }
    }
}
