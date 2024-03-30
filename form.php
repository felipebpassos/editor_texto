<?php
// Inclui o arquivo de conexão
require_once 'Conexao.php';
require 'vendor/autoload.php';

function formatarNomeArquivo($url, $titulo)
{
    // Obtém o nome do arquivo da URL da imagem
    $nome_arquivo = basename($url);

    // Remove a extensão do arquivo
    $nome_arquivo_sem_extensao = pathinfo($nome_arquivo, PATHINFO_FILENAME);

    // Remove acentos e transforma em minúsculas
    $nome_arquivo_sem_acentos = mb_strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $nome_arquivo_sem_extensao));

    // Substitui espaços e hífens por underscores no título
    $titulo_sem_espacos = str_replace([' ', '-'], '_', $titulo);

    // Substitui espaços e hífens por underscores no nome do arquivo
    $nome_arquivo_sem_espacos = str_replace([' ', '-'], '_', $nome_arquivo_sem_acentos);

    // Obtém a extensão do arquivo
    $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);

    // Concatena o nome da imagem, título do post e extensão do arquivo
    $novo_nome_arquivo = $nome_arquivo_sem_espacos . '_' . $titulo_sem_espacos . '.' . $extensao;

    return $novo_nome_arquivo;
}

// Configuração do HTML Purifier
$config = HTMLPurifier_Config::createDefault();
// Lista de elementos proibidos
$forbiddenElements = ['script', 'body', 'header', 'nav'];
// Proíbe os elementos especificados
$config->set('HTML.ForbiddenElements', $forbiddenElements);
// Permite todos os outros elementos
$config->set('HTML.Allowed', '');
// Permite atributos específicos
$config->set('HTML.AllowedAttributes', 'src,href,title,alt');
// Instancia o HTML Purifier com a configuração especificada
$purifier = new HTMLPurifier($config);

// Verifica se os dados foram enviados via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Sanitize dos dados recebidos
    $titulo = htmlspecialchars($_POST["titulo"]);

    // Use HTML Purifier para limpar o conteúdo HTML
    $conteudo = htmlspecialchars($_POST["conteudo"]);

    // Encontra todas as URLs das imagens no conteúdo do post
    preg_match_all('/<img[^>]+src="([^">]+)"/', $conteudo, $matches);
    $imagens = $matches[1]; // Obtém todas as URLs das imagens

    // Itera sobre as imagens encontradas
    foreach ($imagens as $imagem) {
        // Obtém o novo nome do arquivo
        $novo_nome_arquivo = formatarNomeArquivo($imagem, $titulo);

        // Define o novo caminho da imagem após o upload (./uploads/nome_do_arquivo-nome_do_post.extensao)
        $novo_caminho_imagem = "./uploads/" . $novo_nome_arquivo;

        // Substitui o URL antigo pelo novo no conteúdo
        $conteudo = str_replace($imagem, $novo_caminho_imagem, $conteudo);
    }

    // Prepara a consulta SQL para inserir o post
    $stmt = $conn->prepare("INSERT INTO posts (titulo, conteudo) VALUES (?, ?)");
    $stmt->bind_param("ss", $titulo, $conteudo);

    // Executa a consulta para inserir o post
    if ($stmt->execute()) {
        // Obtém o ID do post recém-inserido
        $post_id = $stmt->insert_id;

        // Itera sobre as imagens encontradas e faz upload delas
        // Itera sobre as imagens encontradas
        foreach ($imagens as $imagem) {
            // Obtém o novo nome do arquivo
            $novo_nome_arquivo = formatarNomeArquivo($imagem, $titulo);

            // Define o caminho de destino para fazer upload (./uploads/nome_do_arquivo-nome_do_post.extensao)
            $caminho_destino = "./uploads/" . $novo_nome_arquivo;

            // Faz o download da imagem e salva no destino
            file_put_contents($caminho_destino, file_get_contents($imagem));

            // Insere a URL da imagem no banco de dados associada ao post ID
            $stmtImagens = $conn->prepare("INSERT INTO imagens (post_id, url) VALUES (?, ?)");
            $stmtImagens->bind_param("is", $post_id, $caminho_destino);
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

