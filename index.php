<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Posts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css">
    <script src="./ckeditor5/ckeditor.js"></script>
</head>

<body>
    <div class="container mt-5">
        <h1>Editor de Posts</h1>
        <form action="form.php" method="post">
            <div class="mb-3">
                <label for="titulo" class="form-label">Título:</label>
                <input type="text" id="titulo" name="titulo" class="form-control">
            </div>
            <div class="mb-3">
                <label for="editor" class="form-label">Conteúdo:</label>
                <textarea id="editor" name="conteudo" class="form-control" rows="10"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>

        <hr>

        <h2>Posts:</h2>
        <ul>
            <?php
            // Inclui o arquivo de conexão
            require_once 'Conexao.php';

            // Verifica se a conexão com o banco de dados foi bem-sucedida
            if ($conn) {
                // Prepara a consulta SQL
                $sql = "SELECT id, titulo FROM posts";
                $result = $conn->query($sql);

                if (!$result) {
                    echo "Erro na consulta: " . mysqli_error($conn);
                    // Ou, se preferir, apenas:
                    // die("Erro na consulta: " . mysqli_error($conn));
                } else {
                    // Processar os resultados aqui
                    // Verifica se há resultados
                    if ($result->num_rows > 0) {
                        // Exibe os posts
                        echo "<ul>";
                        while ($row = $result->fetch_assoc()) {
                            echo "<li><a href='post.php?id=" . $row["id"] . "'>" . $row["titulo"] . "</a></li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p>Nenhum post encontrado.</p>";
                    }
                }

                // Fecha a conexão com o banco de dados
                $conn->close();
            } else {
                // Exibe uma mensagem de erro
                echo "<p>Erro ao conectar ao banco de dados.</p>";
            }
            ?>
        </ul>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#editor'), {
                ckfinder: {
                    uploadUrl: './ckfinder/core/connector/php/connector.php?type=files'
                }
            })
            .then(editor => {
                editor.editing.view.change(writer => {
                    writer.setStyle('min-height', '200px', editor.editing.view.document.getRoot());
                });
            })
            .catch(error => {
                console.error(error);
            });
    </script>
</body>

</html>