<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileUploader
{
    private string $targetDirectory;

    public function __construct(string $targetDirectory)
    {
        $this->targetDirectory = $targetDirectory;
    }

    public function upload(UploadedFile $file): string
    {
        $newFilename = uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->targetDirectory, $newFilename);
        } catch (FileException $e) {
            throw new \Exception('Erreur upload fichier');
        }

        return $newFilename;
    }
}