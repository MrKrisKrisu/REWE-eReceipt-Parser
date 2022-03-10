<?php declare(strict_types=1);

namespace K118\Receipt\REWE;

class Address implements \K118\Receipt\Format\Models\Address {

    private ?string $street;
    private ?string $postalCode;
    private ?string $city;
    private ?string $country;

    public function __construct(string $street = null, string $postalCode = null, string $city = null, string $country = null) {
        $this->street     = $street;
        $this->postalCode = $postalCode;
        $this->city       = $city;
        $this->country    = $country;
    }

    public function getStreet(): ?string {
        return $this->street;
    }

    public function getPostalCode(): ?string {
        return $this->postalCode;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function getCountry(): ?string {
        return $this->country;
    }
}