USE editor_texto;

select * from imagens;

DROP TABLE posts;

-- Tabela para armazenar os posts
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(255) NOT NULL,
    conteudo TEXT NOT NULL
);

-- Tabela para armazenar as URLs das imagens associadas aos posts
CREATE TABLE imagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    url VARCHAR(255) NOT NULL,
    post_id INT NOT NULL,
    FOREIGN KEY (post_id) REFERENCES posts(id)
);
