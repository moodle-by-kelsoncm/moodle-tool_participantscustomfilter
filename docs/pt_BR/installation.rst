Instalação
==========

1. Baixe o ZIP da última release em
   https://github.com/moodle-by-kelsoncm/moodle-tool_participantscustomfilter/releases e instale em
   *Administração do site > Plugins > Instalar plugins*.

   Ou copie o repositório para ``admin/tool/participantscustomfilter``
   (``public/admin/tool/participantscustomfilter`` no Moodle 5.1+):

   .. code-block:: bash

      git clone https://github.com/moodle-by-kelsoncm/moodle-tool_participantscustomfilter.git admin/tool/participantscustomfilter

2. Acesse *Administração do site > Notificações* ou execute:

   .. code-block:: bash

      php admin/cli/upgrade.php --non-interactive

Atualizando a partir da 1.0.x
-----------------------------

A versão 1.1.0 remove a tabela ``tool_participantscustomfilter``, criada pelas versões anteriores e
nunca usada, e leva o filtro de ``/enrol/index.php`` para a página de participantes (``/user/index.php``).
