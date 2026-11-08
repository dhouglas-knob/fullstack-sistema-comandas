-- =============================================================
-- Alfetti - Sistema de Comandas
-- Script do banco de dados (MySQL / MariaDB do XAMPP)
--
-- Como usar: no phpMyAdmin, aba "Importar", escolha este arquivo.
-- ATENÇÃO: o script apaga e recria as tabelas. Os dados antigos somem.
-- =============================================================

CREATE DATABASE IF NOT EXISTS restaurantedb
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE restaurantedb;

-- Apaga na ordem inversa das chaves estrangeiras
-- (primeiro quem aponta, depois quem é apontado)
DROP TABLE IF EXISTS produto_comanda;
DROP TABLE IF EXISTS comanda;
DROP TABLE IF EXISTS produto;
DROP TABLE IF EXISTS user;


-- -------------------------------------------------------------
-- Funcionários (garçom ou gerente)
-- -------------------------------------------------------------
CREATE TABLE user (
    id      INT          NOT NULL AUTO_INCREMENT,
    ativo   TINYINT(1)   NOT NULL DEFAULT 1,
    funcao  VARCHAR(30)  NOT NULL,              -- 'garcom' ou 'gerente'
    nome    VARCHAR(150) NOT NULL,
    email   VARCHAR(150) NOT NULL,
    senha   VARCHAR(255) NOT NULL,              -- hash do password_hash()
    PRIMARY KEY (id),
    UNIQUE (email)                              -- um e-mail por funcionário
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- Produtos do cardápio
-- -------------------------------------------------------------
CREATE TABLE produto (
    id          INT           NOT NULL AUTO_INCREMENT,
    ativo       TINYINT(1)    NOT NULL DEFAULT 1,      -- 0 = indisponível (RN05)
    nome        VARCHAR(150)  NOT NULL,
    categoria   VARCHAR(150)  NOT NULL,
    valor       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    descricao        VARCHAR(255)  DEFAULT NULL,            -- descrição do produto
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- Comandas (uma por mesa enquanto estiver aberta - RN01)
-- -------------------------------------------------------------
CREATE TABLE comanda (
    id                  INT           NOT NULL AUTO_INCREMENT,
    user_id             INT           NOT NULL,              -- garçom que abriu
    mesa                INT           NOT NULL,
    status              VARCHAR(30)   NOT NULL,              -- 'aberta' ou fechada
    valor_total         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    forma_pagamento     VARCHAR(50)   DEFAULT NULL,          -- só preenchida ao fechar
    abertura_comanda    DATETIME      NOT NULL,
    fechamento_comanda  DATETIME      DEFAULT NULL,
    PRIMARY KEY (id),
    -- RN07: usuário que já abriu comandas não pode ser excluído
    FOREIGN KEY (user_id) REFERENCES user (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- Itens da comanda (liga comanda e produto)
-- -------------------------------------------------------------
CREATE TABLE produto_comanda (
    id                  INT           NOT NULL AUTO_INCREMENT,
    comanda_id          INT           NOT NULL,
    produto_id          INT           NOT NULL,
    quantidade_produto  INT           NOT NULL,
    valor               DECIMAL(10,2) NOT NULL DEFAULT 0.00,  -- preço do produto no momento do lançamento (RN04)
    PRIMARY KEY (id),
    -- RN11: comanda com itens não pode ser excluída
    FOREIGN KEY (comanda_id) REFERENCES comanda (id),
    -- RN06: produto já lançado não pode ser excluído
    FOREIGN KEY (produto_id) REFERENCES produto (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- -------------------------------------------------------------
-- Conta inicial (sem ela ninguém entra, porque não há cadastro público)
-- E-mail: gerente@alfetti.com
-- Senha:  alfetti123
-- -------------------------------------------------------------
INSERT INTO user (ativo, funcao, nome, email, senha) VALUES
(1, 'gerente', 'Gerente Alfetti', 'gerente@alfetti.com',
 '$2y$10$gmyC6zlorvSjv5rl/b2fBOKD7IrryEWJpHdKYu8KHJ9ybsxA/UWe6');
