<?php declare(strict_types=1);

use Spatie\PdfToText\Exceptions\PdfNotFound;
use K118\Receipt\REWE\Parser;
use K118\Receipt\Format\Exception\ReceiptParseException;

require_once '../vendor/autoload.php';

try {
    $receipt = Parser::parseFromPDF('../tests/receipts/multipleProducts_multiplePaymentMethods_paybackCoupon.pdf');

    echo "The receipt was made " . $receipt->getTimestamp()->diffForHumans() . ". \n";
    echo "You've bought " . count($receipt->getPositions()) . " Products for a total of " . $receipt->getTotal() . "€. \n";

} catch(ReceiptParseException $e) {
    echo "There is something weird with the receipt... Maybe it's not compatible?\n";
    echo "Error: " . $e->getMessage();
} catch(PdfNotFound $e) {
    echo "The given PDF File cannot be opened... " . $e->getMessage();
}
