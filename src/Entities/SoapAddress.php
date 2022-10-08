<?php

namespace Codestage\Netopia\Entities;

class SoapAddress
{
    public string $country;
    public string $county;
    public string $city;
    public string $address;
    public string $postal_code;
    public string $first_name;
    public string $last_name;
    public string $phone;
    public string $email;

    public function __construct(array $initial = [])
    {
        if (isset($initial['country'])) {
            $this->country = $initial['country'];
        }

        if (isset($initial['county'])) {
            $this->county = $initial['county'];
        }

        if (isset($initial['city'])) {
            $this->city = $initial['city'];
        }

        if (isset($initial['address'])) {
            $this->address = $initial['address'];
        }

        if (isset($initial['postal_code'])) {
            $this->postal_code = $initial['postal_code'];
        }

        if (isset($initial['first_name'])) {
            $this->first_name = $initial['first_name'];
        }

        if (isset($initial['last_name'])) {
            $this->last_name = $initial['last_name'];
        }

        if (isset($initial['phone'])) {
            $this->phone = $initial['phone'];
        }

        if (isset($initial['email'])) {
            $this->email = $initial['email'];
        }
    }
}
