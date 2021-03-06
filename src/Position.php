<?php

namespace REWEParser;

use REWEParser\Exception\ReceiptParseException;

class Position
{

    private $name;
    private $priceTotal;
    private $priceSingle;
    private $taxCode;

    private $weight;
    private $amount;

    /**
     * The name of the product.
     *
     * @return string|NULL
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The total sum of the position
     *
     * @return float
     * @throws ReceiptParseException
     */
    public function getPriceTotal()
    {
        if ($this->priceTotal !== null)
            return $this->priceTotal;
        if ($this->priceSingle !== null && $this->amount !== null)
            return $this->priceSingle * $this->amount;
        if ($this->priceSingle !== null && $this->weight !== null)
            return $this->priceSingle * $this->weight;
        throw new ReceiptParseException();
    }

    /**
     * The single value for one unit of the product
     *
     * @return float
     * @throws ReceiptParseException
     */
    public function getPriceSingle()
    {
        if ($this->priceSingle !== null)
            return $this->priceSingle;
        if ($this->priceTotal !== null && $this->amount !== null)
            return $this->priceTotal / $this->amount;
        if ($this->priceTotal !== null && $this->weight !== null)
            return $this->priceTotal / $this->weight;
        if ($this->priceTotal !== null)
            return $this->priceTotal;
        throw new ReceiptParseException();
    }

    /**
     * The Tax Code of the position (e.g. "A" or "B")
     *
     * @return string|NULL
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * The weight of the position (if the product is weightable)
     *
     * @return float|NULL
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * The amount of the position (if the product is countable)
     *
     * @return int|NULL
     */
    public function getAmount()
    {
        if ($this->amount === null && $this->weight === null)
            return 1;
        return $this->amount;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setPriceTotal(float $priceTotal)
    {
        $this->priceTotal = $priceTotal;
    }

    public function setPriceSingle(float $priceSingle)
    {
        $this->priceSingle = $priceSingle;
    }

    public function setTaxCode(string $taxCode)
    {
        $this->taxCode = $taxCode;
    }

    public function setWeight(float $weight)
    {
        $this->weight = $weight;
    }

    public function setAmount(int $amount)
    {
        $this->amount = $amount;
    }

}