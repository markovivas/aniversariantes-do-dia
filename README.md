# Aniversariantes do Dia

**Contributors:** Marco Antônio Vivas
**Tags:** birthday, users, widget, shortcode, aniversário, usuários  
**Requires at least:** 5.0  
**Tested up to:** 6.4  
**Stable tag:** 1.0.0  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

Adiciona um campo de data de nascimento aos perfis de usuário e exibe os aniversariantes do dia através de um shortcode, um widget e uma coluna na lista de usuários.

## Descrição

Este plugin aprimora a gestão de usuários no WordPress, permitindo que você armazene e visualize as datas de nascimento. É uma ótima ferramenta para comunidades, intranets de empresas ou qualquer site que queira celebrar os aniversários de seus membros.

### Funcionalidades

*   **Campo de Data de Nascimento:** Adiciona um campo "Data de Nascimento" fácil de usar nos perfis de usuário.
*   **Avatar Personalizado:** Permite que os usuários façam upload de uma foto de perfil personalizada, que substitui o Gravatar.
*   **Shortcode `[aniversariantes_do_dia]`:** Exibe uma grade com os avatares e nomes dos aniversariantes do dia.
*   **Widget "Aniversariantes do Dia":** Permite adicionar a lista de aniversariantes em qualquer área de widget do seu tema.
*   **Coluna de Admin:** Mostra rapidamente na lista de usuários quem está fazendo aniversário no dia.

## Instalação

1.  Faça o upload da pasta do plugin para o diretório `/wp-content/plugins/`.
2.  Ative o plugin através do menu 'Plugins' no painel administrativo do WordPress.

## Como Usar

### Editando o Perfil do Usuário

#### Método 1: Pelo Painel Administrativo (Manual)

**Para seu próprio perfil:**

1.  No painel, vá para **Usuários → Seu Perfil**.
2.  Role para baixo até encontrar a seção **"Data de Nascimento"**.
3.  Selecione a data e, se desejar, faça o upload de uma **Foto de Perfil Personalizada** na seção correspondente.
4.  Clique em **"Atualizar Perfil"** no final da página.

**Para outros usuários (requer permissão de administrador):**

1.  No painel, vá para **Usuários → Todos os Usuários**.
2.  Clique em **"Editar"** no usuário desejado.
3.  Role para baixo até a seção **"Data de Nascimento"**.
4.  Selecione a data e gerencie a foto de perfil.
5.  Clique em **"Atualizar Usuário"** no final da página.

### Exibindo os Aniversariantes

*   **Usando o Shortcode:**
    Adicione o shortcode em qualquer página, post ou editor de texto.
    *   `[aniversariantes_do_dia]` - Uso básico.
    *   `[aniversariantes_do_dia tamanho="100"]` - Altera o tamanho do avatar para 100x100 pixels.
    *   `[aniversariantes_do_dia mostrar_email="true"]` - Exibe o e-mail do usuário abaixo do nome.

*   **Usando o Widget:**
    1.  Vá para **Aparência → Widgets**.
    2.  Arraste o widget **"Aniversariantes do Dia"** para a barra lateral ou outra área de widget de sua escolha.
    3.  Você pode personalizar o título do widget diretamente nas opções dele.
