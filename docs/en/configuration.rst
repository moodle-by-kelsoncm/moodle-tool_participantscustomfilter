Configuration
=============

The plugin has no settings of its own. What it shows depends on the site configuration.

Profile fields
   Create the fields at *Site administration > Users > User profile fields*. The field setting
   *Who is this field visible to?* decides who can filter by it:

   - *Visible to everyone*: anyone who can view the participants.
   - *Visible to teachers*: users with ``moodle/site:viewuseridentity`` in the course.
   - *Visible to user* and *Not visible*: only users with ``moodle/user:viewalldetails`` at site level.

Identity columns
   The result table shows the fields selected in *Site administration > Users > Permissions >
   User policies > Show user identity*, for users with ``moodle/site:viewuseridentity``.

Capabilities
   The filter appears for users with ``moodle/course:viewparticipants`` in the course. In separate
   groups mode, users without ``moodle/site:accessallgroups`` only find members of their groups.
