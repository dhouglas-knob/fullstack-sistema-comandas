# Sistema de Gerenciamento de Comandas

Projeto universitário desenvolvido com HTML, CSS, PHP e MySQL, com o objetivo de facilitar o controle de comandas, produtos, usuários e vendas de um estabelecimento.

O sistema permite registrar comandas, adicionar produtos aos pedidos e calcular automaticamente o valor total com base nos produtos e suas respectivas quantidades. Além disso, possui autenticação de usuários e diferentes níveis de acesso, separando as funcionalidades disponíveis para gerentes e garçons.

## Funcionalidades

### Gerenciamento de comandas
- Criação e registro de comandas.
- Adição de produtos aos pedidos.
- Cálculo automático do valor total da comanda.
- Organização dos produtos e valores registrados.

### Gerenciamento de produtos
- Cadastro e consulta de produtos.
- Registro de nome, categoria e valor.
- Controle de disponibilidade dos produtos.

### Controle de usuários
- Cadastro e autenticação de usuários.
- Gerenciamento de sessões utilizando PHP.
- Controle de acesso de acordo com a função do usuário.
- Cadastro de novos gerentes e garçons pelo gerente.

### Controle de vendas

O sistema também possui funcionalidades voltadas ao acompanhamento financeiro do estabelecimento.

- Consulta ao registro de vendas.
- Visualização dos valores movimentados.
- Acompanhamento das saídas financeiras registradas.
- Consulta das informações das comandas para auxiliar no controle do estabelecimento.

### Níveis de acesso

O sistema possui dois tipos de usuários:

**Gerente**
- Acesso às funcionalidades de gerenciamento.
- Cadastro de novos usuários, podendo definir se serão gerentes ou garçons.
- Gerenciamento de produtos.
- Acesso ao registro de vendas.
- Consulta dos valores de saída e movimentações registradas.
- Acesso ao gerenciamento de comandas.

**Garçom**
- Acesso ao registro de comandas.
- Adição de produtos aos pedidos.
- Consulta das informações necessárias para realizar os pedidos.

O controle de permissões impede que garçons acessem funcionalidades administrativas, mantendo o gerenciamento dessas operações restrito aos gerentes.

## Tecnologias utilizadas

- **HTML5:** estrutura das páginas.
- **CSS3:** estilização e organização visual.
- **PHP:** processamento das requisições e regras de negócio.
- **MySQL:** armazenamento e gerenciamento dos dados.

## Conceitos aplicados

Durante o desenvolvimento, foram utilizados conceitos importantes para o desenvolvimento Web:

- Operações CRUD.
- Integração entre PHP e MySQL.
- Autenticação de usuários.
- Gerenciamento de sessões.
- Controle de acesso por função (gerente e garçom).
- Manipulação de requisições HTTP.
- Relacionamento entre tabelas no banco de dados.
- Cálculos e gerenciamento de valores das comandas.

## Objetivo

O projeto foi desenvolvido como parte da graduação em Ciência da Computação, com o propósito de colocar em prática conhecimentos de desenvolvimento Full Stack, trabalhando tanto na interface quanto na lógica do servidor e na integração com o banco de dados.

Mais do que desenvolver um sistema de cadastro, a proposta foi criar uma aplicação que simulasse algumas necessidades reais de gerenciamento de um estabelecimento.
