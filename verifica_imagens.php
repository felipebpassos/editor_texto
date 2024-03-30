<?php

// Define o caminho para a pasta de imagens do CKFinder
$imageDirectory = './ckfinder/userfiles/images/';

// Define o caminho para o arquivo CSV
$csvFile = './imagens_validade.csv';

// Verifica se o arquivo CSV existe, caso contrário, cria um novo com cabeçalhos
if (!file_exists($csvFile)) {
    $csvHeader = ['Nome do Arquivo', 'Data de Validade'];
    $handle = fopen($csvFile, 'w');
    fputcsv($handle, $csvHeader);
    fclose($handle);
}

// Obtém a lista de arquivos na pasta de imagens
$imageFiles = scandir($imageDirectory);

// Remove . e ..
$imageFiles = array_diff($imageFiles, ['.', '..']);

// Inicializa arrays para armazenar novos arquivos e arquivos expirados
$newFiles = [];
$expiredFiles = [];

// Loop pelos arquivos na pasta
foreach ($imageFiles as $imageFile) {
    $filePath = $imageDirectory . $imageFile;
    
    // Verifica se o arquivo já está no CSV
    $csvData = array_map('str_getcsv', file($csvFile));
    $fileExistsInCSV = false;
    foreach ($csvData as $csvRow) {
        if ($csvRow[0] === $imageFile) {
            $fileExistsInCSV = true;
            break;
        }
    }
    
    // Se o arquivo não existe no CSV, adiciona à lista de novos arquivos
    if (!$fileExistsInCSV) {
        $newFiles[] = $imageFile;
    }
    
    // Verifica se o arquivo já expirou
    $fileModificationTime = filemtime($filePath);
    $expirationTime = strtotime('+24 hours', $fileModificationTime);
    if ($expirationTime < time()) {
        $expiredFiles[] = $imageFile;
    }
}

// Adiciona novos arquivos ao CSV com a data de validade de 24 horas
if (!empty($newFiles)) {
    $handle = fopen($csvFile, 'a');
    foreach ($newFiles as $newFile) {
        $expirationDate = date('Y-m-d H:i:s', strtotime('+24 hours'));
        fputcsv($handle, [$newFile, $expirationDate]);
    }
    fclose($handle);
}

// Remove arquivos expirados e seus registros do CSV
if (!empty($expiredFiles)) {
    foreach ($expiredFiles as $expiredFile) {
        $filePath = $imageDirectory . $expiredFile;
        unlink($filePath);
        
        // Remove registro do CSV
        $csvData = array_map('str_getcsv', file($csvFile));
        $updatedCSV = [];
        foreach ($csvData as $csvRow) {
            if ($csvRow[0] !== $expiredFile) {
                $updatedCSV[] = $csvRow;
            }
        }
        $handle = fopen($csvFile, 'w');
        foreach ($updatedCSV as $csvRow) {
            fputcsv($handle, $csvRow);
        }
        fclose($handle);
    }
}

echo "Script executado com sucesso.";