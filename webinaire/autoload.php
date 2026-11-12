<?php

spl_autoload_register(function (string $classeComplete): void {
    $mappings = [
        'App\\Models\\' => __DIR__ . '/backend/classes/',
        'App\\DAO\\' => __DIR__ . '/backend/dao/',
        'App\\Services\\' => __DIR__ . '/backend/services/',
        'App\\Exceptions\\' => __DIR__ . '/backend/Exceptions/',
    ];

    foreach ($mappings as $prefixe => $dossier) {
        if (strncmp($prefixe, $classeComplete, strlen($prefixe)) !== 0) {
            continue;
        }

        $classeRelative = substr($classeComplete, strlen($prefixe));
        $fichier = $dossier . str_replace('\\', '/', $classeRelative) . '.php';

        if (file_exists($fichier)) {
            require $fichier;
            return;
        }
    }
});
