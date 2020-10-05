<?php

namespace REWEParser;

class Shop
{

    private $name;
    private $address;
    private $postalCode;
    private $city;

    public function __construct(string $name = null, string $address = null, string $postalCode = null, string $city = null)
    {
        $this->name = $name;
        $this->address = $address;
        $this->postalCode = $postalCode;
        $this->city = $city;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    public function getCity(): string
    {
        return $this->city;
    }
}