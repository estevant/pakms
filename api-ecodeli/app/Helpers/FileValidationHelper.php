<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;

class FileValidationHelper
{
    public static function validateFileExtension(UploadedFile $file, array $allowedExtensions): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $allowedExtensions);
    }

    public static function validateFilesExtensions(array $files, array $allowedExtensions): array
    {
        $errors = [];
        
        foreach ($files as $index => $file) {
            if (!self::validateFileExtension($file, $allowedExtensions)) {
                $errors[] = "Le fichier #" . ($index + 1) . " a un format non autorisé. Extensions autorisées : " . implode(', ', $allowedExtensions);
            }
        }
        
        return $errors;
    }

    public static function getExtensionErrorMessage(array $allowedExtensions): string
    {
        return 'Format de fichier non autorisé. Seuls les fichiers ' . strtoupper(implode(', ', $allowedExtensions)) . ' sont acceptés.';
    }
} 