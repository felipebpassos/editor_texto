<?php
// Configurações do banco de dados
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "editor_texto";

// Tenta criar uma nova conexão
$conn = @new mysqli($servername, $username, $password, $dbname);

// Verifica se houve erro na conexão
if ($conn->connect_error) {
    // Exibe uma mensagem genérica de erro
    echo '<script>alert("Ocorreu um erro ao conectar ao banco de dados.");</script>';
    exit(); // Encerra o script PHP imediatamente
}

