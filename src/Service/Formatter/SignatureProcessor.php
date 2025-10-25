<?php

namespace App\Service\Formatter;

use App\Exception\SignatureProcessingException;

readonly class SignatureProcessor
{
    public function process(string $signatureBase64): string
    {
        $binaryData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $signatureBase64));

        if ($binaryData === false || $binaryData === '') {
            throw new SignatureProcessingException('Les données Base64 de la signature sont invalides ou vides.');
        }

        $tmpSignaturePath = tempnam(sys_get_temp_dir() . '/timeboard/', 'signature_') . '.png';

        if (file_put_contents($tmpSignaturePath, $binaryData) === false) {
            throw new SignatureProcessingException('Échec de l\'écriture du fichier de signature temporaire.');
        }

        return $tmpSignaturePath;
    }
}
