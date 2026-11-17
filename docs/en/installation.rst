Installation
============

1. Download the ZIP of the latest release from
   https://github.com/moodle-by-kelsoncm/moodle-tool_participantscustomfilter/releases and install it at
   *Site administration > Plugins > Install plugins*.

   Or copy the repository to ``admin/tool/participantscustomfilter``
   (``public/admin/tool/participantscustomfilter`` on Moodle 5.1+):

   .. code-block:: bash

      git clone https://github.com/moodle-by-kelsoncm/moodle-tool_participantscustomfilter.git admin/tool/participantscustomfilter

2. Visit *Site administration > Notifications*, or run:

   .. code-block:: bash

      php admin/cli/upgrade.php --non-interactive

Upgrading from 1.0.x
--------------------

Version 1.1.0 drops the unused ``tool_participantscustomfilter`` table created by earlier versions
and moves the filter from ``/enrol/index.php`` to the participants page (``/user/index.php``).
