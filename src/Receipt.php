<?php declare(strict_types=1);

namespace K118\Receipt\REWE;

use Carbon\Carbon;
use K118\Receipt\Format\Exception\ReceiptParseException;
use K118\Receipt\REWE\Exception\PositionNotFoundException;
use K118\Receipt\Format\Models\Currency;

class Receipt implements \K118\Receipt\Format\Models\Receipt {

    private string $raw_receipt;
    private array  $expl_receipt;

    public function __construct(string $raw_receipt) {
        $this->raw_receipt  = $raw_receipt;
        $this->expl_receipt = explode("\n", $raw_receipt);
    }

    /**
     * @return float
     * @throws ReceiptParseException
     */
    public function getTotal(): float {
        if(preg_match('/SUMME *EUR *(-?\d+,\d{2})/', $this->raw_receipt, $match)) {
            return (float)str_replace(',', '.', $match[1]);
        }
        throw new ReceiptParseException();
    }

    /**
     * @return string
     * @throws ReceiptParseException
     */
    public function getId(): string {
        if(preg_match('/Bon-Nr.:(\d{1,4})/', $this->raw_receipt, $match)) {
            return $match[1];
        }
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getShopNr(): int {
        if(preg_match('/Markt:(\d{1,4})/', $this->raw_receipt, $match)) {
            return (int)$match[1];
        }
        throw new ReceiptParseException();
    }

    /**
     * @return Shop
     */
    public function getShop(): Shop {
        $rawPos       = explode("\n", $this->raw_receipt);
        $address_part = array_slice($rawPos, 0, 5);
        preg_match('/(\d{5}) (.*)/', implode("\n", $address_part), $zip_city);
        return new Shop(
            trim($address_part[0] ?? null),
            trim($address_part[1] ?? null),
            trim($zip_city[1] ?? null),
            trim($zip_city[2] ?? null),
        );
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getCashierNr(): int {
        if(preg_match('/Bed.: ?(\d{4,6})/', $this->raw_receipt, $match)) {
            return (int)$match[1];
        }
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    public function getCashregisterNr(): int {
        if(preg_match('/Kasse:(\d{1,6})/', $this->raw_receipt, $match)) {
            return (int)$match[1];
        }
        throw new ReceiptParseException();
    }

    /**
     * @return int
     */
    public function getEarnedPaybackPoints(): int {
        if(preg_match('/Sie erhalten (\d+) PAYBACK Punkt/', $this->raw_receipt, $match)) {
            return (int)$match[1];
        }
        return 0;
    }

    /**
     * It's possible to pay with multiple Payment methods at REWE, so this function will return an array.
     * You can for example pay with Cash, then with Coupon and with Card.
     *
     * @return array
     */
    public function getPayments(): array {
        $paymentMethods = [];
        foreach(explode("\n", $this->raw_receipt) as $line) {
            if(preg_match('/Geg. (.*) *EUR/', $line, $match)) {
                $paymentMethods[] = trim($match[1]);
            } elseif(preg_match('/BAR *EUR *-\d+,\d{2}/', $line, $match)) {
                $paymentMethods[] = "BAR";
            }
        }
        return $paymentMethods;
    }

    public function hasPayedCashless(): bool {
        return preg_match('/Kartenzahlung/', $this->raw_receipt) ||
               (preg_match('/Bezahlung/', $this->raw_receipt) && preg_match('/(visa|mastercard|american express)/', strtolower($this->raw_receipt)));
    }

    public function hasPayedContactless(): bool {
        return str_contains($this->raw_receipt, "Kontaktlos");
    }

    /**
     * @return Carbon
     */
    public function getTimestamp(): Carbon {
        $dateRaw = null;
        $timeRaw = null;

        if(preg_match('/(\d{2}\.\d{2}\.\d{4})/', $this->raw_receipt, $match)) {
            $dateRaw = $match[1];
        }

        if(preg_match('/(\d{2}:\d{2}:\d{2}) Uhr/', $this->raw_receipt, $match)) {
            $timeRaw = $match[1];
        } elseif(preg_match('/(\d{2}:\d{2})/', $this->raw_receipt, $match)) { //very unprecise...
            $timeRaw = $match[1];
        }
        return Carbon::parse($dateRaw . ' ' . $timeRaw);
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductStartLine(): int {
        foreach(explode("\n", $this->raw_receipt) as $line => $content) {
            if(trim($content) == "EUR") {
                return $line + 1;
            }
        }
        throw new ReceiptParseException();
    }

    /**
     * @return int
     * @throws ReceiptParseException
     */
    private function getProductEndLine(): int {
        foreach(explode("\n", $this->raw_receipt) as $line => $content) {
            if(substr(trim($content), 0, 5) == "-----") {
                return $line - 1;
            }
        }
        throw new ReceiptParseException();
    }

    /**
     * @param string $name
     *
     * @return Position
     * @throws PositionNotFoundException|ReceiptParseException
     */
    public function getPositionByName(string $name): Position {
        foreach($this->getPositions() as $position) {
            if($position->getName() == $name) {
                return $position;
            }
        }
        throw new PositionNotFoundException("Position '$name' not found");
    }

    /**
     * @return array
     * @throws ReceiptParseException
     */
    public function getPositions(): array {
        $positions    = [];
        $lastPosition = null;

        for($lineNr = $this->getProductStartLine(); $lineNr <= $this->getProductEndLine(); $lineNr++) {
            if($this->isProductLine($lineNr)) {
                if($lastPosition !== null) {
                    $positions[]  = $lastPosition;
                    $lastPosition = null;
                }

                if(preg_match('/(.*)  (-?\d+,\d{2}) (.{1})/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition = new Position(
                        receipt:    $this,
                        name:       explode('  ', trim($match[1]))[0],
                        priceTotal: (float)str_replace(',', '.', $match[2]),
                        taxCode:    $match[3]
                    );
                } elseif(preg_match('/     (\d{5,})/', $this->expl_receipt[$lineNr], $match)) {
                    //This is a line with a coupon code or something similar.
                    //Should be skipped.
                    continue;
                } else {
                    throw new ReceiptParseException("Error while parsing Product line");
                }

            } elseif($this->isAmountLine($lineNr)) {

                if(preg_match('/(-?\d+) Stk x *(-?\d+,\d{2})/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setQuantity((int)$match[1]);
                    $lastPosition->setPriceSingle((float)str_replace(',', '.', $match[2]));
                } else {
                    throw new ReceiptParseException("Error while parsing Amount line");
                }

            } elseif($this->isWeightLine($lineNr)) {

                if(preg_match('/(-?\d+,\d{3}) kg x *(-?\d+,\d{2}) EUR/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setQuantity((float)str_replace(',', '.', $match[1]));
                    $lastPosition->setPriceSingle((float)str_replace(',', '.', $match[2]));
                } elseif(preg_match('/Handeingabe E-Bon *(-?\d+,\d{3}) kg/', $this->expl_receipt[$lineNr], $match)) {
                    $lastPosition->setQuantity((float)str_replace(',', '.', $match[1]));
                } else {
                    throw new ReceiptParseException("Error while parsing Weight line");
                }

            } else {
                throw new ReceiptParseException("Error while parsing unknown receipt line");
            }
        }

        if($lastPosition !== null) {
            $positions[] = $lastPosition;
        }

        if(empty($positions)) {
            throw new ReceiptParseException("Cannot parse any products on receipt");
        }
        return $positions;
    }

    private function isWeightLine(int $lineNr): bool {
        return str_contains($this->expl_receipt[$lineNr], 'kg');
    }

    private function isAmountLine(int $lineNr): bool {
        return str_contains($this->expl_receipt[$lineNr], ' Stk x');
    }

    private function isProductLine(int $lineNr): bool {
        return !$this->isWeightLine($lineNr) && !$this->isAmountLine($lineNr);
    }

    public function getCurrency(): ?Currency {
        return Currency::EUR;
    }
}