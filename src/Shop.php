<?php declare(strict_types=1);

namespace K118\Receipt\REWE;

use K118\Receipt\Format\Models\Address;

class Shop implements \K118\Receipt\Format\Models\Shop {

    private ?string $name;
    private Address $address;

    public function __construct(
        string $name = null,
        string $street = null,
        string $postalCode = null,
        string $city = null
    ) {
        $this->name    = $name;
        $this->address = new \K118\Receipt\REWE\Address($street, $postalCode, $city);
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAddress(): Address {
        return $this->address;
    }

    public function getTaxNumber(): ?string {
        // TODO: Implement getTaxNumber() method.
        return null;
    }

    public function getPhone(): ?string {
        // TODO: Implement getPhone() method.
        return null;
    }

    public function getEmail(): ?string {
        // TODO: Implement getEmail() method.
        return null;
    }

    public function getFax(): ?string {
        // TODO: Implement getFax() method.
        return null;
    }
}