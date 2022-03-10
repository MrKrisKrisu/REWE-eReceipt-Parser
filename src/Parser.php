<?php declare(strict_types=1);

namespace K118\Receipt\REWE;

use Spatie\PdfToText\Exceptions\PdfNotFound;
use Spatie\PdfToText\Pdf;

class Parser {

    /**
     * @param string      $path           The path to the pdf receipt that should be parsed
     * @param string|null $path_pdfToText Optionally the path to the pdftotext command if not default
     *
     * @return Receipt
     * @throws PdfNotFound
     */
    public static function parseFromPDF(string $path, string $path_pdfToText = null): Receipt {
        $pdf  = new Pdf($path_pdfToText);
        $text = $pdf->setPdf($path)->setOptions(['layout'])->text();
        return self::parseFromText($text);
    }

    /**
     * @param String $raw_receipt The raw receipt as parsed text
     *
     * @return Receipt
     */
    public static function parseFromText(string $raw_receipt): Receipt {
        return new Receipt($raw_receipt);
    }
}