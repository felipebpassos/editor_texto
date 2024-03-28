<?php
// Inclui o arquivo de conexão
require_once 'Conexao.php';

function replaceOembedWithIframe($content) {
    // Expressão regular para encontrar <oembed> com um URL do YouTube
    $pattern = '/<oembed[^>]*url="(https?:\/\/(?:www\.)?youtube\.com\/watch\?v=([^"]+))"[^>]*><\/oembed>/';
    
    // Substitui <oembed> por <iframe> com o link de incorporação do vídeo do YouTube
    $replacement = '<iframe width="560" height="315" src="https://www.youtube.com/embed/$2" frameborder="0" allowfullscreen></iframe>';
    
    // Retorna o conteúdo com a substituição feita
    return preg_replace($pattern, $replacement, $content);
}

// Verifica se o ID do post foi passado via GET
if (isset($_GET['id'])) {

    // Sanitize o ID do post
    $id = htmlspecialchars($_GET['id']);

    // Prepara a consulta SQL
    $stmt = $conn->prepare("SELECT titulo, conteudo FROM posts WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Executa a consulta
    $stmt->execute();

    // Obtém os resultados da consulta
    $stmt->bind_result($titulo, $conteudo);
    $stmt->fetch();

    // Fecha a declaração e a conexão com o banco de dados
    $stmt->close();
    $conn->close();
} else {
    // Redireciona de volta para index.php se nenhum ID de post for fornecido
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Inclui a biblioteca Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <a href="index.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Voltar</a>
        <h1><?php echo $titulo; ?></h1>
        <p><?php echo replaceOembedWithIframe(htmlspecialchars_decode($conteudo)); ?></p>
    </div>
</body>
</html>
