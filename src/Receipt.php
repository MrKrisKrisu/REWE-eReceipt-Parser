<?php

namespace REWEParser;

use Carbon\Carbon;
use REWEParser\Exception\ReceiptParseException;

class Receipt
{
    private $raw_receipt;

    function __construct(string $raw_receipt)
    {
        $this->raw_receipt = $raw_receipt;
    }

    /**
     * @return float
     * @throws ReceiptParseException
     */
    public function getTotal(): float
    {
        if (preg_match('/SUMME *EUR *(-?[0-9]{1,},[0-9]{2})/', $this->raw_receipt, $match))
            return (float)str_replace(',', '.', $match[1]);
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getBonNr(): int
    {
        if (preg_match('/Bon-Nr.:([0-9]{1,4})/', $this->raw_receipt, $match))
            return (int)$match[1];
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getShopNr(): int
    {
        if (preg_match('/Markt:([0-9]{1,4})/', $this->raw_receipt, $match))
            return (int)$match[1];
        throw new ReceiptParseException();
    }

    /**
     * @return array
     */
    public function getShop(): array
    {
        $rawPos = explode("\n", $this->raw_receipt);
        $address_part = array_slice($rawPos, 0, 5);
        preg_match('/(\d{5}) (.*)/', implode("\n", $address_part), $zip_city);
        return [
            'name' => trim($address_part[0]),
            'address' => trim($address_part[1]),
            'zip' => trim($zip_city[1]),
            'city' => trim($zip_city[2])
        ];
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getCashierNr(): int
    {
        if (preg_match('/Bed.: ?([0-9]{4,6})/', $this->raw_receipt, $match))
            return (int)$match[1];
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getCashregisterNr(): int
    {
        if (preg_match('/Kasse:([0-9]{1,6})/', $this->raw_receipt, $match))
            return (int)$match[1];
        throw new ReceiptParseException();
    }

    /**
     * @return int
     */
    public function getEarnedPaybackPoints(): int
    {
        if (preg_match('/Sie erhalten ([0-9]{1,}) PAYBACK Punkt/', $this->raw_receipt, $match))
            return (int)$match[1];
        return 0;
    }

    /**
     * It's possible to pay with multiple Payment methods at REWE, so this function will return an array.
     * You can for example pay with Cash, then with Coupon and with Card.
     * @return array
     */
    public function getPaymentMethods(): array
    {
        $paymentMethods = [];
        foreach (explode("\n", $this->raw_receipt) as $line)
            if (preg_match('/Geg. (.*) *EUR/', $line, $match))
                $paymentMethods[] = trim($match[1]);
            else if (preg_match('/BAR *EUR *-\d{1,},\d{2}/', $line, $match))
                $paymentMethods[] = "BAR";
        return $paymentMethods;
    }

    /**
     * @return Carbon
     */
    public function getTimestamp(): Carbon
    {
        $dateRaw = NULL;
        $timeRaw = NULL;

        if (preg_match('/(\d{2}\.\d{2}\.\d{4})/', $this->raw_receipt, $match))
            $dateRaw = $match[1];

        if (preg_match('/(\d{2}:\d{2}:\d{2}) Uhr/', $this->raw_receipt, $match)) {
            $timeRaw = $match[1];
        } elseif (preg_match('/(\d{2}:\d{2})/', $this->raw_receipt, $match)) { //very unprecise...
            $timeRaw = $match[1];
        }
        return Carbon::parse($dateRaw . ' ' . $timeRaw);
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductStartLine(): int
    {
        foreach (explode("\n", $this->raw_receipt) as $line => $content)
            if (trim($content) == "EUR")
                return $line + 1;
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductEndLine(): int
    {
        foreach (explode("\n", $this->raw_receipt) as $line => $content)
            if (substr(trim($content), 0, 5) == "-----")
                return $line - 1;
        throw new ReceiptParseException();
    }

    /**
     * @return array
     * @throws ReceiptParseException
     */
    public function getPositions(): array
    {
        $positions = [];

        $startLine = $this->getProductStartLine();
        $endLine = $this->getProductEndLine();

        $rawPos = explode("\n", $this->raw_receipt);
        $lastPos = NULL;

        for ($lineNr = $startLine; $lineNr <= $endLine; $lineNr++) {
            $line = trim($rawPos[$lineNr]);

            if (strpos($line, ' Stk x') !== false && $lastPos != NULL) {

                if (preg_match('/(-?\d{1,}) Stk x *(-?\d{1,},\d{2})/', $line, $match)) {
                    $lastPos['amount'] = (int)$match[1];
                    $lastPos['price_single'] = (float)str_replace(',', '.', $match[2]);
                }

            } else if (strpos($line, 'kg') !== false && $lastPos != NULL) {

                if (preg_match('/(-?\d{1,},\d{3}) kg x *(-?\d{1,},\d{2}) EUR/', $line, $match)) {
                    $lastPos['weight'] = (float)str_replace(',', '.', $match[1]);
                    $lastPos['price_single'] = (float)str_replace(',', '.', $match[2]);
                } else if (preg_match('/Handeingabe E-Bon *(-?\d{1,},\d{3}) kg/', $line, $match)) {
                    $lastPos['weight'] = (float)str_replace(',', '.', $match[1]);
                }

            } else {
                if ($lastPos != NULL && isset($lastPos['name']) && isset($lastPos['price_total'])) {
                    if (!isset($lastPos['price_single']) && isset($lastPos['weight']))
                        $lastPos['price_single'] = $lastPos['price_total'] / $lastPos['weight'];
                    if (!isset($lastPos['price_single']) && isset($lastPos['amount']))
                        $lastPos['price_single'] = $lastPos['price_total'] / $lastPos['amount'];
                    if (!isset($lastPos['price_single'])) {
                        $lastPos['price_single'] = $lastPos['price_total'];
                        $lastPos['amount'] = 1;
                    }

                    $positions[] = $lastPos;
                    $lastPos = NULL;
                }


                if (preg_match('/(.*)  (-?\d{1,},\d{2}) (.{1})/', $line, $match)) {
                    $lastPos = [
                        'name' => trim($match[1]),
                        'price_total' => (float)str_replace(',', '.', $match[2]),
                        'tax_code' => $match[3]
                    ];
                }

            }
        }

        if ($lastPos != NULL && isset($lastPos['name']) && isset($lastPos['price_total'])) {
            if (!isset($lastPos['price_single']) && isset($lastPos['weight']))
                $lastPos['price_single'] = $lastPos['price_total'] / $lastPos['weight'];
            if (!isset($lastPos['price_single']) && isset($lastPos['amount']))
                $lastPos['price_single'] = $lastPos['price_total'] / $lastPos['amount'];
            if (!isset($lastPos['price_single'])) {
                $lastPos['price_single'] = $lastPos['price_total'];
                $lastPos['amount'] = 1;
            }
            $positions[] = $lastPos;
            $lastPos = NULL;
        }

        if (count($positions) == 0)
            throw new ReceiptParseException();

        return $positions;
    }
}