-- Alfetti - Sistema de Comandas
-- Banco completo: cria as tabelas E ja coloca dados de exemplo
-- (o schema.sql é o mesmo banco, só que vazio)
--
-- Pra usar: abre o phpMyAdmin, vai em "Importar" e escolhe esse arquivo.
-- CUIDADO: ele apaga as tabelas e cria tudo de novo, o que tinha antes some.

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS restaurantedb
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurantedb;

-- tem que apagar primeiro as tabelas que apontam pras outras,
-- senão a chave estrangeira não deixa
DROP TABLE IF EXISTS produto_comanda;
DROP TABLE IF EXISTS comanda;
DROP TABLE IF EXISTS produto;
DROP TABLE IF EXISTS user;


-- =========================
-- PARTE 1: AS TABELAS
-- =========================

-- Funcionários (garçom ou gerente)
CREATE TABLE user (
    id      INT          NOT NULL AUTO_INCREMENT,
    ativo   TINYINT(1)   NOT NULL DEFAULT 1,    -- 0 = desativado, não consegue logar
    funcao  VARCHAR(30)  NOT NULL,              -- 'garcom' ou 'gerente'
    nome    VARCHAR(150) NOT NULL,
    email   VARCHAR(150) NOT NULL,
    senha   VARCHAR(255) NOT NULL,              -- vai o hash do password_hash(), nunca a senha pura
    PRIMARY KEY (id),
    UNIQUE (email)                              -- não deixa repetir e-mail
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Produtos do cardápio
CREATE TABLE produto (
    id          INT           NOT NULL AUTO_INCREMENT,
    ativo       TINYINT(1)    NOT NULL DEFAULT 1,      -- 0 = indisponível, não aparece pra lançar
    nome        VARCHAR(150)  NOT NULL,
    categoria   VARCHAR(150)  NOT NULL,
    valor       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descricao   VARCHAR(255)  DEFAULT NULL,            -- opcional
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comandas (não tem tabela de mesa, a mesa é só o número de 1 a 12)
CREATE TABLE comanda (
    id                  INT           NOT NULL AUTO_INCREMENT,
    user_id             INT           NOT NULL,              -- quem abriu, e depois de fechada quem fechou
    mesa                INT           NOT NULL,
    status              VARCHAR(30)   NOT NULL,              -- 'aberta' ou 'concluida'
    valor_total         DECIMAL(10,2) NOT NULL DEFAULT 0.00, -- o php soma de novo toda vez que mexe nos itens
    forma_pagamento     VARCHAR(50)   DEFAULT NULL,          -- só tem depois de fechar
    abertura_comanda    DATETIME      NOT NULL,
    fechamento_comanda  DATETIME      DEFAULT NULL,
    PRIMARY KEY (id),
    -- por causa dessa chave não dá pra excluir usuário que tem comanda
    FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Itens da comanda (é a tabela que liga a comanda com o produto)
CREATE TABLE produto_comanda (
    id                  INT           NOT NULL AUTO_INCREMENT,
    comanda_id          INT           NOT NULL,
    produto_id          INT           NOT NULL,
    quantidade_produto  INT           NOT NULL,
    PRIMARY KEY (id),
    -- comanda com item não pode ser excluída
    FOREIGN KEY (comanda_id) REFERENCES comanda (id),
    -- produto que já foi lançado não pode ser excluído, só fica indisponível
    FOREIGN KEY (produto_id) REFERENCES produto (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =========================
-- PARTE 2: OS DADOS
-- =========================
-- os ids vão escritos na mão pra ficar fácil de ver quem aponta pra quem

-- USUÁRIOS
-- a senha de todo mundo é: alfetti123
-- (o texto comprido é o hash dela, por isso é igual em todas as linhas)
INSERT INTO user (id, ativo, funcao, nome, email, senha) VALUES
(1, 1, 'gerente', 'Gerente Alfetti',   'gerente@alfetti.com', '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6'),
(2, 1, 'garcom',  'Garçom Alfetti',    'garcom@alfetti.com',  '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6'),
(3, 1, 'garcom',  'Ana Paula Ribeiro', 'ana@alfetti.com',     '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6'),
(4, 1, 'garcom',  'Lucas Ferreira',    'lucas@alfetti.com',   '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6'),
(5, 1, 'garcom',  'Camila Souza',      'camila@alfetti.com',  '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6');

-- PRODUTOS
-- o Cannoli (id 12) ta com ativo = 0 pra mostrar que indisponível não aparece pra lançar
INSERT INTO produto (id, ativo, nome, categoria, valor, descricao) VALUES
(1,  1, 'Bruschetta de tomate',  'Entrada',         24.00, 'Pão italiano tostado com tomate, alho e manjericão. 4 unidades.'),
(2,  1, 'Carpaccio',             'Entrada',         38.00, 'Lâminas de carne com molho de mostarda, alcaparras e parmesão.'),
(3,  1, 'Salada caprese',        'Entrada',         32.00, 'Tomate, muçarela de búfala e manjericão.'),
(4,  1, 'Espaguete à carbonara', 'Prato principal', 49.00, 'Massa com pancetta, ovo e queijo pecorino.'),
(5,  1, 'Lasanha à bolonhesa',   'Prato principal', 52.00, 'Massa fresca, molho bolonhesa e bechamel. Serve 1 pessoa.'),
(6,  1, 'Risoto de funghi',      'Prato principal', 58.00, 'Arroz arbóreo com cogumelos e parmesão.'),
(7,  1, 'Nhoque ao sugo',        'Prato principal', 44.00, 'Nhoque de batata com molho de tomate da casa.'),
(8,  1, 'Filé à parmegiana',     'Prato principal', 68.00, 'Acompanha arroz e fritas.'),
(9,  1, 'Pizza margherita',      'Prato principal', 62.00, 'Molho de tomate, muçarela e manjericão. 8 fatias.'),
(10, 1, 'Tiramisù',              'Sobremesa',       26.00, 'Biscoito, café, mascarpone e cacau.'),
(11, 1, 'Panna cotta',           'Sobremesa',       22.00, 'Com calda de frutas vermelhas.'),
(12, 0, 'Cannoli',               'Sobremesa',       18.00, 'Em falta no momento.'),
(13, 1, 'Água mineral',          'Bebida',           6.00, 'Com ou sem gás. 500 ml.'),
(14, 1, 'Refrigerante lata',     'Bebida',           7.00, '350 ml.'),
(15, 1, 'Suco de laranja',       'Bebida',          12.00, 'Natural. 400 ml.'),
(16, 1, 'Taça de vinho tinto',   'Bebida',          28.00, 'Vinho da casa. 150 ml.'),
(17, 1, 'Café espresso',         'Bebida',           6.50, NULL);

-- COMANDAS FECHADAS (aparecem no Histórico)
-- user_id: 1 = Gerente, 3 = Ana, 4 = Lucas, 5 = Camila
-- o valor_total é a soma dos itens dela (valor do produto x quantidade), que estão mais abaixo
INSERT INTO comanda (id, user_id, mesa, status, valor_total, forma_pagamento, abertura_comanda, fechamento_comanda) VALUES
(1,  3, 4,  'concluida', 162.00, 'Pix',               '2026-11-07 12:10:00', '2026-11-07 13:05:00'),
(2,  4, 9,  'concluida', 264.00, 'Cartão de crédito', '2026-11-07 19:40:00', '2026-11-07 21:20:00'),
(3,  5, 2,  'concluida',  83.00, 'Dinheiro',          '2026-11-07 20:15:00', '2026-11-07 21:02:00'),
(4,  3, 6,  'concluida', 141.00, 'Cartão de débito',  '2026-11-08 12:30:00', '2026-11-08 13:25:00'),
(5,  1, 12, 'concluida', 234.00, 'Pix',               '2026-11-08 13:05:00', '2026-11-08 14:10:00'),
(6,  4, 5,  'concluida', 512.00, 'Cartão de crédito', '2026-11-08 20:00:00', '2026-11-08 22:15:00'),
(7,  5, 10, 'concluida',  55.00, 'Pix',               '2026-11-09 12:45:00', '2026-11-09 13:20:00'),
(8,  3, 8,  'concluida', 201.00, 'Dinheiro',          '2026-11-09 19:30:00', '2026-11-09 20:50:00'),
(9,  4, 3,  'concluida', 152.00, 'Cartão de débito',  '2026-11-10 12:20:00', '2026-11-10 13:10:00'),
(10, 5, 11, 'concluida', 483.00, 'Cartão de crédito', '2026-11-10 20:10:00', '2026-11-10 22:00:00');

-- COMANDAS ABERTAS (são as mesas que aparecem ocupadas: 3, 5, 8, 9 e 11)
-- o NOW() coloca a hora em que o arquivo foi importado
-- a da mesa 11 fica sem item, pra dar pra testar o Excluir comanda
INSERT INTO comanda (id, user_id, mesa, status, valor_total, forma_pagamento, abertura_comanda, fechamento_comanda) VALUES
(11, 3, 3,  'aberta', 181.00, NULL, NOW(), NULL),
(12, 4, 5,  'aberta',  90.00, NULL, NOW(), NULL),
(13, 5, 8,  'aberta', 246.00, NULL, NOW(), NULL),
(14, 4, 9,  'aberta', 100.00, NULL, NOW(), NULL),
(15, 3, 11, 'aberta',   0.00, NULL, NOW(), NULL);

-- ITENS DAS COMANDAS
-- cada linha é: (comanda, produto, quantidade)
INSERT INTO produto_comanda (comanda_id, produto_id, quantidade_produto) VALUES
-- comanda 1
(1, 1, 1), (1, 4, 2), (1, 14, 2), (1, 10, 1),
-- comanda 2
(2, 2, 1), (2, 6, 1), (2, 8, 1), (2, 16, 2), (2, 11, 2),
-- comanda 3
(3, 9, 1), (3, 14, 3),
-- comanda 4
(4, 5, 2), (4, 15, 2), (4, 17, 2),
-- comanda 5
(5, 3, 1), (5, 7, 3), (5, 13, 3), (5, 10, 2),
-- comanda 6
(6, 1, 2), (6, 8, 2), (6, 6, 2), (6, 16, 4), (6, 11, 1), (6, 10, 3),
-- comanda 7
(7, 4, 1), (7, 13, 1),
-- comanda 8
(8, 9, 2), (8, 15, 1), (8, 14, 3), (8, 11, 2),
-- comanda 9
(9, 3, 1), (9, 5, 1), (9, 7, 1), (9, 15, 2),
-- comanda 10
(10, 2, 2), (10, 6, 2), (10, 8, 1), (10, 4, 1), (10, 16, 3), (10, 13, 2), (10, 10, 2), (10, 17, 4),
-- comanda 11 (aberta, mesa 3)
(11, 1, 1), (11, 4, 1), (11, 5, 1), (11, 16, 2),
-- comanda 12 (aberta, mesa 5)
(12, 9, 1), (12, 14, 4),
-- comanda 13 (aberta, mesa 8)
(13, 2, 1), (13, 6, 2), (13, 8, 1), (13, 15, 1), (13, 13, 2),
-- comanda 14 (aberta, mesa 9)
(14, 7, 2), (14, 13, 2);
