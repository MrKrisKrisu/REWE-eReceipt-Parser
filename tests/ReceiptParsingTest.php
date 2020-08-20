<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ReceiptParsingTest extends TestCase
{
    public function testCanBeCreatedFromValidEmailAddress(): void
    {
        $this->assertInstanceOf(\REWEParser\Receipt::class, \REWEParser\Parser::parseFromText("xxx"));
    }

    /**
     * @return void
     * @throws \REWEParser\Exception\ReceiptParseException
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     */
    public function testNegativeTotalAmount(): void
    {
        $receipt = REWEParser\Parser::parseFromPDF(dirname(__FILE__) . '/receipts/negative_amount.pdf');
        $this->assertEquals(-0.25, $receipt->getTotal());
    }

    /**
     * @return void
     * @throws \REWEParser\Exception\ReceiptParseException
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     */
    public function testBonParsingWeight(): void
    {
        $receipt = REWEParser\Parser::parseFromPDF(dirname(__FILE__) . '/receipts/weight_eccash.pdf');

        $this->assertEquals(11.0, $receipt->getTotal());
        $this->assertEquals(1234, $receipt->getBonNr());
        $this->assertEquals(1234, $receipt->getShopNr());
        $this->assertEquals(252525, $receipt->getCashierNr());
        $this->assertEquals(2, $receipt->getCashregisterNr());
        $this->assertEquals(5, $receipt->getEarnedPaybackPoints());
        $this->assertContains("EC-Cash", $receipt->getPaymentMethods());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());

        $positions = [];
        foreach ($receipt->getPositions() as $position)
            $positions[$position['name']] = $position;

        $this->assertEquals(1, $positions['BROT']['price_single']);
        $this->assertEquals(0.5, $positions['AUFSCHNITT']['price_single']);
        $this->assertEquals(0.5, $positions['NATUR-JOGHURT']['price_single']);
        $this->assertEquals(0.01, $positions['ESSEN']['price_single']);
        $this->assertEquals(1.99, $positions['BANANE']['price_single']);
        $this->assertEquals(2.99, $positions['BANANE']['price_total']);
        $this->assertEquals(1.5, $positions['BANANE']['weight']);
        $this->assertEquals(1, $positions['EIER']['price_single']);
        $this->assertEquals(1, $positions['WEIZENMEHL']['price_single']);
        $this->assertEquals(1, $positions['WASSER']['price_single']);
        $this->assertEquals(1, $positions['SOFTDRINK']['price_single']);
        $this->assertEquals(1, $positions['MILCH']['price_single']);
        $this->assertEquals(1, $positions['EIS']['price_single']);

    }

    /**
     * @return void
     * @throws \REWEParser\Exception\ReceiptParseException
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     */
    public function testBonParsingPaymentMethods(): void
    {
        $receipt = REWEParser\Parser::parseFromPDF(dirname(__FILE__) . '/receipts/multipleProducts_multiplePaymentMethods_paybackCoupon.pdf');

        $this->assertEquals(8.62, $receipt->getTotal());
        $this->assertEquals(9999, $receipt->getBonNr());
        $this->assertEquals(51, $receipt->getShopNr());
        $this->assertEquals(123414, $receipt->getCashierNr());
        $this->assertEquals(14, $receipt->getCashregisterNr());
        $this->assertEquals(22, $receipt->getEarnedPaybackPoints());
        $this->assertContains("BAR", $receipt->getPaymentMethods());
        $this->assertContains("VISA", $receipt->getPaymentMethods());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());

        $positions = [];
        foreach ($receipt->getPositions() as $position)
            $positions[$position['name']] = $position;

        $this->assertEquals(0.25, $positions['LEERGUT']['price_single']);
        $this->assertEquals(2.99, $positions['KARTOFFELN']['price_single']);
        $this->assertEquals(1.49, $positions['NUDELN']['price_single']);
        $this->assertEquals(0.49, $positions['QUARK']['price_single']);
        $this->assertEquals(1.99, $positions['SÜßIGKEITEN']['price_single']);
        $this->assertEquals(0.69, $positions['SCHOKOLADE']['price_single']);
        $this->assertEquals(1.38, $positions['SCHOKOLADE']['price_total']);
        $this->assertEquals(0.53, $positions['SCHMAND 24%']['price_single']);
    }

    /**
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     */
    public function testShopParsing(): void
    {
        $receipt = REWEParser\Parser::parseFromPDF(dirname(__FILE__) . '/receipts/negative_amount.pdf');
        $shop = $receipt->getShop();

        $this->assertInstanceOf(\REWEParser\Shop::class, $shop);

        $this->assertEquals("REWE Mustermann oHG", $shop->getName());
        $this->assertEquals("Muster-Str. 1", $shop->getAddress());
        $this->assertEquals("12345", $shop->getPostalCode());
        $this->assertEquals("Musterstadt", $shop->getCity());
    }
}
