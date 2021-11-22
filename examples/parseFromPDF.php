<?php

use REWEParser\Exception\ReceiptParseException;
use REWEParser\Parser;
use Spatie\PdfToText\Exceptions\PdfNotFound;

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
