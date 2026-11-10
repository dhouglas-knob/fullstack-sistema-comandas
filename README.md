# Alfetti - Sistema de Comandas

Projeto universitário da PUC-PR (TDE 2). É um sistema web para a equipe de um restaurante controlar o atendimento das mesas: abrir a comanda, lançar os pedidos, acompanhar o total e fechar a conta.

**Dupla:** Marco (front-end) e Dhouglas (back-end).

## Tecnologias

- Front-end: HTML, CSS e JavaScript puro
- Back-end: PHP com `mysqli` e prepared statements
- Banco: MySQL (XAMPP)
- O front conversa com o PHP só por `fetch` com JSON
- Login com sessão do PHP

## Como instalar

1. Instale o [XAMPP](https://www.apachefriends.org/) e ligue o **Apache** e o **MySQL** no painel dele.
2. Coloque a pasta do projeto dentro de `C:\xampp\htdocs` com o nome `restaurante`. Tem que ficar assim: `C:\xampp\htdocs\restaurante\index.html`.
3. Abra `http://localhost/phpmyadmin`, clique em **Importar**, escolha o arquivo `schema.sql` que está na pasta do projeto e clique em **Importar** no fim da página. O script já cria o banco `restaurantedb`, as tabelas e as contas de teste.
4. Confira o arquivo `php/conexao.php`. Ele vem com o usuário `root` e a senha vazia, que é o padrão do XAMPP. Se o seu MySQL tiver senha, troque na variável `$password`.
5. Abra `http://localhost/restaurante/` no navegador.

> Tem que abrir pelo `localhost`. Abrir o arquivo direto ou pelo Live Preview do VS Code não funciona, porque o PHP não roda.

## Contas de teste

| Perfil  | E-mail              | Senha      |
| ------- | ------------------- | ---------- |
| Gerente | gerente@alfetti.com | alfetti123 |
| Garçom  | garcom@alfetti.com  | alfetti123 |

Não existe cadastro público. Só o gerente cadastra funcionário (menu **Usuários**), e todo funcionário novo entra como garçom.

O banco vem sem produtos. Entre como gerente e cadastre alguns em **Produtos > Novo produto** antes de lançar itens.

## Como usar

1. Entre com e-mail e senha.
2. Na tela de **Mesas** aparecem as 12 mesas. Verde é livre e cinza é ocupada.
3. Clique numa mesa livre para abrir uma comanda, ou numa ocupada para ver a comanda dela.
4. Na comanda, a esquerda mostra os itens e o total. Na direita dá para lançar item, fechar a conta e excluir a comanda. Tirar item só aparece para o gerente.
5. Depois de fechada, a comanda vai para o **Histórico** e não muda mais.

### O que cada perfil faz

- **Garçom:** abre comanda, lança item, fecha a conta, exclui comanda vazia, vê produtos e histórico.
- **Gerente:** tudo do garçom, e também tira item da comanda, cadastra/edita/exclui produtos e usuários.

## Regras do sistema

- Cada mesa só pode ter uma comanda aberta por vez.
- Só dá para lançar ou tirar item em comanda aberta.
- Produto indisponível não aparece para lançar.
- Produto que já foi lançado em alguma comanda não pode ser excluído, só fica indisponível.
- Usuário que tem comanda não pode ser excluído, só desativado. O gerente não pode excluir a própria conta.
- Só dá para excluir comanda aberta e sem itens.
- As permissões são conferidas no front e de novo no PHP.

## Banco de dados

São 4 tabelas (o script está em `schema.sql`):

- `user`: funcionários (garçom ou gerente)
- `produto`: cardápio
- `comanda`: uma por atendimento de mesa. Tem chave estrangeira para `user`
- `produto_comanda`: itens da comanda. Tem chave estrangeira para `comanda` e para `produto`

Não existe tabela de mesas: são 12 mesas fixas e a mesa é só um número na comanda.

## Pastas

```
restaurante/
├── index.html          tela de login
├── schema.sql          script do banco
├── pages/              as telas de dentro do sistema (HTML)
├── api/                o JavaScript de cada tela (faz os fetch)
├── assets/             CSS, fontes e imagens
└── php/                a API em PHP (devolve JSON)
```

| Tela                         | JavaScript                              | PHP que ela chama                               |
| ---------------------------- | --------------------------------------- | ----------------------------------------------- |
| `index.html`                 | `api/login.js`                          | `login.php`                                     |
| `pages/mesas.html`           | `api/mesas.js`                          | `comanda.php`                                   |
| `pages/comanda-detalhe.html` | `api/comanda.js`                        | `comanda.php`, `produto_comanda.php`, `produto.php` |
| `pages/historico.html`       | `api/historico.js`                      | `comanda.php`                                   |
| `pages/produtos.html`        | `api/produtos.js`                       | `produto.php`                                   |
| `pages/cadastro-produto.html` e `editar-produto.html` | `api/cadastro-produto.js` e `editar-produto.js` | `produto.php`                |
| `pages/usuarios.html`        | `api/users.js`                          | `user.php`                                      |
| `pages/cadastro.html` e `editar.html` | `api/cadastro.js` e `editar.js` | `user.php`                                      |

Toda página de dentro também carrega o `api/auth.js`, que confere a sessão no `sessao.php`, mostra quem está logado e faz o logout.

O roteiro de testes está em [ROTEIRO-DE-TESTES.md](ROTEIRO-DE-TESTES.md).
