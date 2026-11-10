Configuração
============

O plugin não tem configurações próprias. O que ele mostra depende da configuração do site.

Campos do perfil
   Crie os campos em *Administração do site > Usuários > Campos de perfil do usuário*. A opção
   *Este campo é visível para* define quem pode filtrar por ele:

   - *Visível para todos*: quem pode ver os participantes.
   - *Visível para professores*: usuários com ``moodle/site:viewuseridentity`` no curso.
   - *Visível para o usuário* e *Não visível*: só usuários com ``moodle/user:viewalldetails`` no site.

Colunas de identidade
   A tabela de resultados mostra os campos escolhidos em *Administração do site > Usuários >
   Permissões > Políticas do usuário > Mostrar identidade do usuário*, para usuários com
   ``moodle/site:viewuseridentity``.

Capacidades
   O filtro aparece para usuários com ``moodle/course:viewparticipants`` no curso. No modo de grupos
   separados, quem não tem ``moodle/site:accessallgroups`` só encontra membros dos seus grupos.
