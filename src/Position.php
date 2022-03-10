<?php declare(strict_types=1);

namespace K118\Receipt\REWE;

use K118\Receipt\Format\Exception\ReceiptParseException;
use K118\Receipt\Format\Models\Receipt;

class Position implements \K118\Receipt\Format\Models\Position {

    private Receipt $receipt;

    private string  $name;
    private ?float  $priceTotal;
    private ?float  $priceSingle;
    private ?string $taxCode;
    private ?float  $quantity;

    public function __construct(Receipt $receipt, string $name = null, float $priceTotal = null, float $priceSingle = null, string $taxCode = null, float $quantity = null) {
        $this->receipt     = $receipt;
        $this->name        = $name;
        $this->priceTotal  = $priceTotal;
        $this->priceSingle = $priceSingle;
        $this->taxCode     = $taxCode;
        $this->quantity    = $quantity;
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * The Tax Code of the position (e.g. "A" or "B")
     * @return string|NULL
     * @todo support tax amount
     */
    public function getTaxCode(): ?string {
        return $this->taxCode;
    }

    public function getReceipt(): Receipt {
        return $this->receipt;
    }

    /**
     * @throws ReceiptParseException
     */
    public function getSinglePrice(): float {
        if($this->priceSingle !== null) {
            return $this->priceSingle;
        }
        if($this->priceTotal !== null && $this->quantity !== null) {
            return $this->priceTotal / $this->quantity;
        }
        if($this->priceTotal !== null) {
            return $this->priceTotal;
        }
        throw new ReceiptParseException();
    }

    public function getQuantity(): float {
        return $this->quantity ?? 1.0;
    }

    /**
     * @throws ReceiptParseException
     */
    public function getTotalPrice(): float {
        if($this->priceTotal !== null) {
            return $this->priceTotal;
        }
        if($this->priceSingle !== null && $this->quantity !== null) {
            return $this->priceSingle * $this->quantity;
        }
        throw new ReceiptParseException();
    }

    public function getTax(): float {
        // TODO: Implement getTax() method.
        return -1;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setPriceTotal(float $priceTotal): void {
        $this->priceTotal = $priceTotal;
    }

    public function setPriceSingle(float $priceSingle): void {
        $this->priceSingle = $priceSingle;
    }

    public function setTaxCode(string $taxCode): void {
        $this->taxCode = $taxCode;
    }

    public function setQuantity(float $quantity): void {
        $this->quantity = $quantity;
    }
}