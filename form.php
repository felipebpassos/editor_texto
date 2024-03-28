<?php
// Inclui o arquivo de conexão
require_once 'Conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize dos dados recebidos
    $titulo = htmlspecialchars($_POST["titulo"]);
    $conteudo = htmlspecialchars($_POST["conteudo"]);
    
    // Verifica se foi enviado algum arquivo de imagem
    if(isset($_FILES['imagens'])){
        // Define o diretório de destino para o upload
        $upload_dir = 'uploads/';

        // Verifica se o diretório de destino existe e é gravável
        if (!is_dir($upload_dir) || !is_writable($upload_dir)) {
            echo "Erro: O diretório de upload não existe ou não é gravável.";
            exit;
        }

        // Array para armazenar as URLs das imagens
        $urlsImagens = array();

        // Processa cada imagem enviada
        foreach ($_FILES['imagens']['tmp_name'] as $key => $tmp_name) {
            // Verifica se ocorreu algum erro durante o upload
            if ($_FILES['imagens']['error'][$key] === UPLOAD_ERR_OK) {
                // Verifica se o arquivo é uma imagem PNG ou JPEG
                $allowed_types = array('image/png', 'image/jpeg');
                $file_type = $_FILES['imagens']['type'][$key];
                if (!in_array($file_type, $allowed_types)) {
                    echo "Erro: Somente arquivos PNG e JPEG são permitidos.";
                    exit;
                }

                // Verifica o tamanho do arquivo (limite de 3 MB)
                $file_size = $_FILES['imagens']['size'][$key];
                if ($file_size > 3 * 1024 * 1024) {
                    echo "Erro: O arquivo deve ter no máximo 3 MB.";
                    exit;
                }

                // Gera um nome único para o arquivo baseado no timestamp atual
                $file_name = time() . '_' . $_FILES['imagens']['name'][$key];

                // Move o arquivo para o diretório de destino
                if (move_uploaded_file($tmp_name, $upload_dir . $file_name)) {
                    // Adiciona a URL da imagem ao array
                    $urlsImagens[] = $upload_dir . $file_name;
                } else {
                    echo "Erro: Falha ao mover o arquivo para o diretório de upload.";
                    exit;
                }
            } else {
                echo "Erro: Ocorreu um erro durante o upload do arquivo.";
                exit;
            }
        }
    }

    // Prepara a consulta SQL para inserir o post
    $stmt = $conn->prepare("INSERT INTO posts (titulo, conteudo) VALUES (?, ?)");
    $stmt->bind_param("ss", $titulo, $conteudo);

    // Executa a consulta para inserir o post
    if ($stmt->execute()) {
        // Obtém o ID do post recém-inserido
        $post_id = $stmt->insert_id;

        // Insere as URLs das imagens na tabela de imagens, associadas ao ID do post
        foreach ($urlsImagens as $urlImagem) {
            $stmtImagens = $conn->prepare("INSERT INTO imagens (post_id, url) VALUES (?, ?)");
            $stmtImagens->bind_param("is", $post_id, $urlImagem);
            $stmtImagens->execute();
            $stmtImagens->close();
        }

        // Redireciona de volta para index.php após a inserção
        header("Location: index.php");
        exit();
    } else {
        echo "Erro ao inserir o post no banco de dados.";
    }

    // Fecha a declaração e a conexão com o banco de dados
    $stmt->close();
    $conn->close();
}
