<?php
/*
 * CKFinder
 * ========
 * https://ckeditor.com/ckeditor-4/ckfinder/
 * Copyright (c) 2007-2023, CKSource Holding sp. z o.o. All rights reserved.
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 */

require_once __DIR__ . '/vendor/autoload.php';

use CKSource\CKFinder\CKFinder;

$ckfinder = new CKFinder(__DIR__ . '/../../../config.php');

$ckfinder->run();

// Verifica se foi enviado um arquivo
if (isset($_FILES['upload'])) {
    // Verifica se ocorreu algum erro durante o upload
    if ($_FILES['upload']['error'] === UPLOAD_ERR_OK) {
        // Verifica se o arquivo é uma imagem PNG ou JPEG
        $allowed_types = array('image/png', 'image/jpeg');
        $file_type = $_FILES['upload']['type'];
        if (!in_array($file_type, $allowed_types)) {
            echo json_encode(array('error' => 'Somente arquivos PNG e JPEG são permitidos.'));
            exit;
        }

        // Verifica o tamanho do arquivo (limite de 3 MB)
        $file_size = $_FILES['upload']['size'];
        if ($file_size > 3 * 1024 * 1024) {
            echo json_encode(array('error' => 'O arquivo deve ter no máximo 3 MB.'));
            exit;
        }

        // Define o diretório de destino para o upload
        $upload_dir = 'uploads/';

        // Verifica se o diretório de destino existe e é gravável
        if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
            echo json_encode(array('error' => 'O diretório de upload não existe ou não é gravável.'));
            exit;
        }

        // Gera um nome único para o arquivo baseado no timestamp atual
        $file_name = time() . '_' . $_FILES['upload']['name'];

        // Move o arquivo para o diretório de destino
        if (move_uploaded_file($_FILES['upload']['tmp_name'], $upload_dir . $file_name)) {
            // Retorna a URL do arquivo para o CKEditor
            echo json_encode(array('url' => $upload_dir . $file_name));
            exit;
        } else {
            echo json_encode(array('error' => 'Falha ao mover o arquivo para o diretório de upload.'));
            exit;
        }
    } else {
        echo json_encode(array('error' => 'Ocorreu um erro durante o upload do arquivo.'));
        exit;
    }
} else {
    echo json_encode(array('error' => 'Nenhum arquivo foi enviado.'));
    exit;
}

