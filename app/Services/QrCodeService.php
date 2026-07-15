<?php

namespace App\Services;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\SvgWriter;

class QrCodeService
{
    /**
     * Build an inline SVG data URI for the given payload (e.g. a student's
     * qr_token). Suitable for <img src="..."> without any external requests.
     */
    public function dataUri(string $data, int $size = 200): string
    {
        return (new Builder(
            writer: new SvgWriter,
            data: $data,
            size: $size,
            margin: 4,
        ))->build()->getDataUri();
    }
}
