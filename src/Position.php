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
     * @return string|NULL
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * The total sum of the position
     * @return float
     * @throws ReceiptParseException
     */
    public function getPriceTotal()
    {
        if ($this->priceTotal !== NULL)
            return $this->priceTotal;
        if ($this->priceSingle !== NULL && $this->amount !== NULL)
            return $this->priceSingle * $this->amount;
        if ($this->priceSingle !== NULL && $this->weight !== NULL)
            return $this->priceSingle * $this->weight;
        throw new ReceiptParseException();
    }

    /**
     * The single value for one unit of the product
     * @return float
     * @throws ReceiptParseException
     */
    public function getPriceSingle()
    {
        if ($this->priceSingle !== NULL)
            return $this->priceSingle;
        if ($this->priceTotal !== NULL && $this->amount !== NULL)
            return $this->priceTotal / $this->amount;
        if ($this->priceTotal !== NULL && $this->weight !== NULL)
            return $this->priceTotal / $this->weight;
        if ($this->priceTotal !== NULL)
            return $this->priceTotal;
        throw new ReceiptParseException();
    }

    /**
     * The Tax Code of the position (e.g. "A" or "B")
     * @return string|NULL
     */
    public function getTaxCode()
    {
        return $this->taxCode;
    }

    /**
     * The weight of the position (if the product is weightable)
     * @return float|NULL
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * The amount of the position (if the product is countable)
     * @return int|NULL
     */
    public function getAmount()
    {
        return $this->amount;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function setPriceTotal(string $priceTotal)
    {
        $this->priceTotal = $priceTotal;
    }

    public function setPriceSingle(string $priceSingle)
    {
        $this->priceSingle = $priceSingle;
    }

    public function setTaxCode(string $taxCode)
    {
        $this->taxCode = $taxCode;
    }

    public function setWeight(string $weight)
    {
        $this->weight = $weight;
    }

    public function setAmount(string $amount)
    {
        $this->amount = $amount;
    }

}