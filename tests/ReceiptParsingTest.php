<?php declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use K118\Receipt\REWE\Parser;

final class ReceiptParsingTest extends TestCase {

    public function testNegativeTotalAmount(): void {
        $receipt = Parser::parseFromPDF(__DIR__ . '/receipts/negative_amount.pdf');
        $this->assertEquals(-0.25, $receipt->getTotal());
    }

    public function testReceiptWithCouponLine(): void {
        $receipt = Parser::parseFromText(file_get_contents(__DIR__ . '/receipts/coupon.txt'));
        $this->assertCount(1, $receipt->getPositions());
        $this->assertEquals('GUTSCHEINW50', $receipt->getPositions()[0]->getName());
    }

    public function testBonParsingWeight(): void {
        $receipt = Parser::parseFromPDF(__DIR__ . '/receipts/weight_eccash.pdf');

        $this->assertEquals(11.0, $receipt->getTotal());
        $this->assertEquals(1234, $receipt->getId());
        $this->assertEquals(1234, $receipt->getShopNr());
        $this->assertEquals(252525, $receipt->getCashierNr());
        $this->assertEquals(2, $receipt->getCashregisterNr());
        $this->assertEquals(5, $receipt->getEarnedPaybackPoints());
        $this->assertContains("EC-Cash", $receipt->getPayments());
        $this->assertTrue($receipt->hasPayedCashless());
        $this->assertTrue($receipt->hasPayedContactless());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());

        $this->assertEquals(1, $receipt->getPositionByName('BROT')->getQuantity());
        $this->assertEquals(1, $receipt->getPositionByName('BROT')->getSinglePrice());
        $this->assertEquals(0.5, $receipt->getPositionByName('AUFSCHNITT')->getSinglePrice());
        $this->assertEquals(0.5, $receipt->getPositionByName('NATUR-JOGHURT')->getSinglePrice());
        $this->assertEquals(0.01, $receipt->getPositionByName('ESSEN')->getSinglePrice());
        $this->assertEquals(1.99, $receipt->getPositionByName('BANANE')->getSinglePrice());
        $this->assertEquals(2.99, $receipt->getPositionByName('BANANE')->getTotalPrice());
        $this->assertEquals(1.5, $receipt->getPositionByName('BANANE')->getQuantity());
        $this->assertEquals(1, $receipt->getPositionByName('EIER')->getSinglePrice());
        $this->assertEquals(1, $receipt->getPositionByName('WEIZENMEHL')->getSinglePrice());
        $this->assertEquals(1, $receipt->getPositionByName('WASSER')->getSinglePrice());
        $this->assertEquals(1, $receipt->getPositionByName('SOFTDRINK')->getSinglePrice());
        $this->assertEquals(1, $receipt->getPositionByName('MILCH')->getSinglePrice());
        $this->assertEquals(1, $receipt->getPositionByName('EIS')->getSinglePrice());
    }

    public function testBonParsingPaymentMethods(): void {
        $receipt = Parser::parseFromPDF(__DIR__ . '/receipts/multipleProducts_multiplePaymentMethods_paybackCoupon.pdf');

        $this->assertEquals(8.62, $receipt->getTotal());
        $this->assertEquals(9999, $receipt->getId());
        $this->assertEquals(51, $receipt->getShopNr());
        $this->assertEquals(123414, $receipt->getCashierNr());
        $this->assertEquals(14, $receipt->getCashregisterNr());
        $this->assertEquals(22, $receipt->getEarnedPaybackPoints());
        $this->assertContains("BAR", $receipt->getPayments());
        $this->assertContains("VISA", $receipt->getPayments());
        $this->assertTrue($receipt->hasPayedCashless());
        $this->assertFalse($receipt->hasPayedContactless());
        $this->assertEquals(1577880000, $receipt->getTimestamp()->getTimestamp());

        $this->assertEquals(0.25, $receipt->getPositionByName('LEERGUT')->getSinglePrice());
        $this->assertEquals(2.99, $receipt->getPositionByName('KARTOFFELN')->getSinglePrice());
        $this->assertEquals(1.49, $receipt->getPositionByName('NUDELN')->getSinglePrice());
        $this->assertEquals(0.49, $receipt->getPositionByName('QUARK')->getSinglePrice());
        $this->assertEquals(1.99, $receipt->getPositionByName('SÜßIGKEITEN')->getSinglePrice());
        $this->assertEquals(2, $receipt->getPositionByName('SCHOKOLADE')->getQuantity());
        $this->assertEquals(0.69, $receipt->getPositionByName('SCHOKOLADE')->getSinglePrice());
        $this->assertEquals(1.38, $receipt->getPositionByName('SCHOKOLADE')->getTotalPrice());
        $this->assertEquals(0.53, $receipt->getPositionByName('SCHMAND 24%')->getSinglePrice());
    }

    public function testShopParsing(): void {
        $receipt = Parser::parseFromPDF(__DIR__ . '/receipts/negative_amount.pdf');
        $shop    = $receipt->getShop();

        $this->assertEquals("REWE Mustermann oHG", $shop->getName());
        $this->assertEquals("Muster-Str. 1", $shop->getAddress()->getStreet());
        $this->assertEquals("12345", $shop->getAddress()->getPostalCode());
        $this->assertEquals("Musterstadt", $shop->getAddress()->getCity());
    }
}
