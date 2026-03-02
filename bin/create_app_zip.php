<?php
declare(strict_types=1);

use ZipArchive;

/*
|--------------------------------------------------------------------------
| Sécurité : exécution uniquement en CLI
|--------------------------------------------------------------------------
*/
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Access denied');
}

/*
|--------------------------------------------------------------------------
| Configuration
|--------------------------------------------------------------------------
*/
$root = realpath(__DIR__ . '/..');
$archiveDir = $root . '/public/downloads';
$archivePath = $archiveDir . '/app.zip';
$hashPath = $archiveDir . '/app.zip.sha256';
$ignoreFile = $root . '/.archiveignore';

/*
|--------------------------------------------------------------------------
| Création du dossier downloads si nécessaire
|--------------------------------------------------------------------------
*/
if (!is_dir($archiveDir) && !mkdir($archiveDir, 0755, true) && !is_dir($archiveDir)) {
    fwrite(STDERR, "Impossible de créer le dossier downloads\n");
    exit(1);
}

/*
|--------------------------------------------------------------------------
| Chargement des règles d'exclusion
|--------------------------------------------------------------------------
*/
$defaultExcludes = [
    '/vendor/',
    '/var/',
    '/node_modules/',
    '/public/downloads/',
    '/.git/',
    '/config/secrets/',
];

$defaultFilePatterns = [
    '#\.env#',
    '#id_rsa#',
    '#\.ssh#',
    '#\.pem#',
    '#\.key#',
];

/*
|--------------------------------------------------------------------------
| Lecture optionnelle du fichier .archiveignore
|--------------------------------------------------------------------------
*/
$userExcludes = [];

if (file_exists($ignoreFile)) {
    $lines = file($ignoreFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line !== '' && $line[0] !== '#') {
            $userExcludes[] = $line;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Initialisation de l'archive
|--------------------------------------------------------------------------
*/
$zip = new ZipArchive();

if ($zip->open($archivePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    fwrite(STDERR, "Impossible d'ouvrir l'archive\n");
    exit(1);
}

/*
|--------------------------------------------------------------------------
| Filtrage intelligent (performance)
|--------------------------------------------------------------------------
*/
$directory = new RecursiveDirectoryIterator($root, RecursiveDirectoryIterator::SKIP_DOTS);

$filter = new RecursiveCallbackFilterIterator(
    $directory,
    function ($current) use ($root, $defaultExcludes, $defaultFilePatterns, $userExcludes) {
        $path = $current->getPathname();
        $relativePath = substr($path, strlen($root));

        // Exclusion des dossiers par défaut
        foreach ($defaultExcludes as $exclude) {
            if (strpos($relativePath, $exclude) !== false) {
                return false;
            }
        }

        // Exclusion personnalisée (.archiveignore)
        foreach ($userExcludes as $exclude) {
            if (strpos($relativePath, $exclude) !== false) {
                return false;
            }
        }

        // Exclusion des fichiers sensibles
        foreach ($defaultFilePatterns as $pattern) {
            if (preg_match($pattern, $relativePath)) {
                return false;
            }
        }

        return true;
    }
);

$iterator = new RecursiveIteratorIterator($filter);

/*
|--------------------------------------------------------------------------
| Ajout des fichiers dans l'archive
|--------------------------------------------------------------------------
*/
$fileCount = 0;
$totalSize = 0;

foreach ($iterator as $fileInfo) {
    if ($fileInfo->isDir()) {
        continue;
    }

    $filePath = $fileInfo->getRealPath();
    $relativePath = substr($filePath, strlen($root) + 1);

    if (!$zip->addFile($filePath, $relativePath)) {
        fwrite(STDERR, "Erreur ajout fichier : $relativePath\n");
        continue;
    }

    $fileCount++;
    $totalSize += $fileInfo->getSize();
}

$zip->close();

/*
|--------------------------------------------------------------------------
| Génération du hash SHA256
|--------------------------------------------------------------------------
*/
$hash = hash_file('sha256', $archivePath);
file_put_contents($hashPath, $hash);

/*
|--------------------------------------------------------------------------
| Résumé
|--------------------------------------------------------------------------
*/
echo "---------------------------------------\n";
echo "Archive générée : $archivePath\n";
echo "Nombre de fichiers : $fileCount\n";
echo "Taille totale : " . round($totalSize / 1024 / 1024, 2) . " MB\n";
echo "SHA256 : $hash\n";
echo "---------------------------------------\n";