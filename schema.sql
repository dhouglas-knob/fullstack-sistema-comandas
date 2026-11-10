-- Alfetti - Sistema de Comandas
-- Script do banco (MySQL do XAMPP)
--
-- Pra usar: abre o phpMyAdmin, vai em "Importar" e escolhe esse arquivo.
-- CUIDADO: ele apaga as tabelas e cria tudo de novo, o que tinha antes some.

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


-- Contas de teste (sem elas ninguém entra, porque não tem cadastro público)
--   Gerente: gerente@alfetti.com
--   Garçom:  garcom@alfetti.com
--   Senha:   alfetti123  (a mesma nas duas)
INSERT INTO user (ativo, funcao, nome, email, senha) VALUES
(1, 'gerente', 'Gerente Alfetti', 'gerente@alfetti.com',
 '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6'),
(1, 'garcom', 'Garçom Alfetti', 'garcom@alfetti.com',
 '$2y$10$2Tzo9rENPxXN99dBdLSsKeAYPXLElDF/5KfFtnxUO8EB5VeA7r74S');
