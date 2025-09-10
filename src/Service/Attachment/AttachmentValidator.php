<?php

namespace App\Service\Attachment;

use App\Exception\InvalidAttachmentException;
use Symfony\Component\HttpFoundation\File\File;

readonly class AttachmentValidator
{
    public function validate(string $path): string
    {
        $file = new File($path);
        $extension = $file->getExtension();
        $mimeType = $file->getMimeType();

        if (!in_array($extension, ['pdf', 'jpg', 'png', 'gif'])) {
            throw new InvalidAttachmentException('Extension non autorisée.');
        }

        if (!in_array($mimeType, ['application/pdf', 'image/jpeg', 'image/png', 'image/gif'])) {
            throw new InvalidAttachmentException('Type MIME invalide.');
        }

        return $extension;
    }
}
