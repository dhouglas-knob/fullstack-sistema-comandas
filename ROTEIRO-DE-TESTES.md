# Roteiro de testes

Para fazer com o banco recém-importado (`schema.sql`). As contas estão no [README](README.md).

## 1. Login e sessão

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 1.1 | Abrir `http://localhost/restaurante/pages/mesas.html` sem estar logado | Aviso de que não está logado e volta para o login |
| 1.2 | Tentar entrar com a senha errada | Mensagem "Email ou senha incorretos." |
| 1.3 | Entrar como gerente | Abre a tela de mesas com o nome e "Gerente" na barra |
| 1.4 | Clicar em **Sair** | Volta para o login, e a 1.1 volta a barrar |

## 2. Usuários (gerente)

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 2.1 | Usuários > Novo funcionário, cadastrar alguém | "Usuário cadastrado com sucesso!" e ele aparece na lista como Garçom |
| 2.2 | Cadastrar outro com o mesmo e-mail | "Este e-mail já está cadastrado." |
| 2.3 | Editar o funcionário e trocar o nome, deixando a senha em branco | Salva, e a senha antiga continua funcionando |
| 2.4 | Editar e preencher uma senha nova | O login passa a ser com a senha nova |
| 2.5 | Editar e mudar o status para Desativado | Esse funcionário não consegue mais entrar |
| 2.6 | Tentar excluir a própria conta | "Você não pode se Excluir." |
| 2.7 | Editar a própria conta e mudar para Garçom | "Você não pode se Desativar/Despromover." |
| 2.8 | Excluir um funcionário que nunca abriu comanda | Some da lista |

## 3. Produtos (gerente)

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 3.1 | Produtos > Novo produto, cadastrar 2 produtos | Aparecem na lista com o valor em reais |
| 3.2 | Editar um produto e mudar o valor | A lista mostra o valor novo |
| 3.3 | Editar um produto e deixar Indisponível | Ele não aparece mais para lançar na comanda (teste 4.3) |
| 3.4 | Excluir um produto que nunca foi lançado | Some da lista |

## 4. Mesas e comanda

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 4.1 | Na tela de mesas, clicar numa mesa livre e confirmar | Abre a comanda da mesa, sem itens e com total R$ 0,00 |
| 4.2 | Voltar para mesas | A mesa está cinza (ocupada), com o nome de quem abriu, e aparece em "Abertas agora" |
| 4.3 | Na comanda, abrir a lista de produtos | Só aparecem os produtos disponíveis |
| 4.4 | Lançar um produto com quantidade 2 | O item aparece na esquerda e o total é valor x 2 |
| 4.5 | Tentar excluir a comanda com item | "Não é possível excluir uma comanda que já tem itens." |
| 4.6 | Como gerente, tirar o item | O item some e o total volta a zero |
| 4.7 | Excluir a comanda vazia | Volta para mesas e a mesa está verde de novo |
| 4.8 | Abrir outra comanda, lançar itens e fechar a conta escolhendo o pagamento | A comanda aparece como Fechada, com hora e pagamento, e as ações somem |
| 4.9 | Voltar para mesas | A mesa está livre de novo |
| 4.10 | Abrir o Histórico | A comanda fechada está na lista, e o **Ver** abre ela sem as ações |

## 5. Permissões (entrar como garçom)

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 5.1 | Olhar o menu | Não aparece o link Usuários |
| 5.2 | Digitar `pages/usuarios.html` na barra de endereço | Aviso de sem permissão e volta para mesas |
| 5.3 | Abrir Produtos | Vê a lista, mas sem Novo, Editar e Excluir |
| 5.4 | Abrir uma comanda | Não aparece o bloco "Tirar item" |
| 5.5 | Abrir `http://localhost/restaurante/php/user.php` direto | JSON com erro de permissão (403), o PHP também barra |

## 6. Chaves estrangeiras

| # | O que fazer | O que tem que acontecer |
| - | ----------- | ----------------------- |
| 6.1 | Como gerente, excluir um produto que já foi lançado numa comanda | "Esse produto já foi lançado em comanda. Deixe ele indisponível em Editar." |
| 6.2 | Excluir um usuário que tem comanda no nome dele | "Esse usuário já tem comandas no sistema. Desative ele em Editar." |
| 6.3 | Abrir a mesma mesa em duas abas e confirmar nas duas | A segunda mostra que a mesa já está em uso |
