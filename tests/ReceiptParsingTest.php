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
     * @throws \REWEParser\Exception\PositionNotFoundException
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
        $this->assertTrue( $receipt->hasPayedCashless());
        $this->assertTrue( $receipt->hasPayedContactless());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());
        $this->assertEquals(1, $receipt->getPositionByName('BROT')->getPriceSingle());
        $this->assertEquals(0.5, $receipt->getPositionByName('AUFSCHNITT')->getPriceSingle());
        $this->assertEquals(0.5, $receipt->getPositionByName('NATUR-JOGHURT')->getPriceSingle());
        $this->assertEquals(0.01, $receipt->getPositionByName('ESSEN')->getPriceSingle());
        $this->assertEquals(1.99, $receipt->getPositionByName('BANANE')->getPriceSingle());
        $this->assertEquals(2.99, $receipt->getPositionByName('BANANE')->getPriceTotal());
        $this->assertEquals(1.5, $receipt->getPositionByName('BANANE')->getWeight());
        $this->assertEquals(1, $receipt->getPositionByName('EIER')->getPriceSingle());
        $this->assertEquals(1, $receipt->getPositionByName('WEIZENMEHL')->getPriceSingle());
        $this->assertEquals(1, $receipt->getPositionByName('WASSER')->getPriceSingle());
        $this->assertEquals(1, $receipt->getPositionByName('SOFTDRINK')->getPriceSingle());
        $this->assertEquals(1, $receipt->getPositionByName('MILCH')->getPriceSingle());
        $this->assertEquals(1, $receipt->getPositionByName('EIS')->getPriceSingle());

    }

    /**
     * @return void
     * @throws \REWEParser\Exception\ReceiptParseException
     * @throws \Spatie\PdfToText\Exceptions\PdfNotFound
     * @throws \REWEParser\Exception\PositionNotFoundException
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
        $this->assertTrue($receipt->hasPayedCashless());
        $this->assertFalse($receipt->hasPayedContactless());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());

        $this->assertEquals(0.25, $receipt->getPositionByName('LEERGUT')->getPriceSingle());
        $this->assertEquals(2.99, $receipt->getPositionByName('KARTOFFELN')->getPriceSingle());
        $this->assertEquals(1.49, $receipt->getPositionByName('NUDELN')->getPriceSingle());
        $this->assertEquals(0.49, $receipt->getPositionByName('QUARK')->getPriceSingle());
        $this->assertEquals(1.99, $receipt->getPositionByName('SÜßIGKEITEN')->getPriceSingle());
        $this->assertEquals(2, $receipt->getPositionByName('SCHOKOLADE')->getAmount());
        $this->assertEquals(0.69, $receipt->getPositionByName('SCHOKOLADE')->getPriceSingle());
        $this->assertEquals(1.38, $receipt->getPositionByName('SCHOKOLADE')->getPriceTotal());
        $this->assertEquals(0.53, $receipt->getPositionByName('SCHMAND 24%')->getPriceSingle());
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
