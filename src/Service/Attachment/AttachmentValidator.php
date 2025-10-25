<?php

namespace App\Service\Attachment;

use App\Exception\InvalidAttachmentException;
use App\Service\Formatter\MimeTypeExtensionMapper;
use Symfony\Component\HttpFoundation\File\File;

readonly class AttachmentValidator
{
    public function __construct(
       private MimeTypeExtensionMapper $mimeTypeExtensionMapper,
    )
    {}

    public function validate(string $path, array $allowedExtensions, array $allowedMimeTypes): array
    {
        $file = new File($path);
        $mimeType = $file->getMimeType();
        $extension = strtolower($file->guessExtension() ?? $file->getExtension() ?? '');

        if (!in_array($mimeType, $allowedMimeTypes)) {
            throw new InvalidAttachmentException(sprintf(
                'Type MIME "%s" invalide. Autorisés : %s',
                $mimeType,
                implode(', ', $allowedMimeTypes)
            ));
        }

        if ($extension && !in_array($extension, $allowedExtensions, true)) {
            throw new InvalidAttachmentException(sprintf(
                'Extension "%s" non autorisée. Autorisées : %s',
                $extension,
                implode(', ', $allowedExtensions)
            ));
        }

        if (!$extension) {
            $extension = $this->mimeTypeExtensionMapper->map($mimeType);
        }

        return [
            'extension' => $extension,
            'mimeType' => $mimeType,
        ];
    }
}
