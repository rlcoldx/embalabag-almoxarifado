<?php

namespace Agencia\Close\Helpers\QrCode;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

/**
 * Gera QR Code em PNG via GD (chillerlan/php-qrcode).
 */
class QrCodeGenerator
{
    public static function toDataUri(string $conteudo, int $moduleSize = 6): string
    {
        return 'data:image/png;base64,' . base64_encode(self::toPng($conteudo, $moduleSize));
    }

    public static function toPng(string $conteudo, int $moduleSize = 6): string
    {
        if ($conteudo === '') {
            $conteudo = ' ';
        }

        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_M,
            'scale' => max(4, $moduleSize),
            'imageBase64' => false,
            'imageTransparent' => false,
            'addQuietzone' => true,
            'quietzoneSize' => 4,
            'dataModeOverride' => 'Byte',
        ]);

        return (new QRCode($options))->render($conteudo);
    }
}
