# moodle-tool_participantscustomfilter

[English](#english) · [Português (Brasil)](#português-brasil)

Documentation / Documentação: <https://moodle-by-kelsoncm.github.io/moodle-tool_participantscustomfilter/>

## English

Admin tool that adds a filter by custom user profile fields to the course participants page (`/user/index.php`). Pick a field, type part of the value and see the participants with an active enrolment whose field contains it. Field visibility, separate groups and the site identity fields are respected. Supports Moodle 4.5 LTS and 5.3 LTS.

Install: copy this repository to `admin/tool/participantscustomfilter` (`public/admin/tool/participantscustomfilter` on Moodle 5.1+) and visit *Site administration > Notifications*.

## Português (Brasil)

Ferramenta administrativa que acrescenta um filtro por campos personalizados do perfil à página de participantes do curso (`/user/index.php`). Escolha um campo, digite parte do valor e veja os participantes com inscrição ativa cujo campo o contém. A visibilidade dos campos, os grupos separados e os campos de identidade do site são respeitados. Suporta Moodle 4.5 LTS e 5.3 LTS.

Instalação: copie este repositório para `admin/tool/participantscustomfilter` (`public/admin/tool/participantscustomfilter` no Moodle 5.1+) e acesse *Administração do site > Notificações*.

## Development / Desenvolvimento

- JavaScript sources are in `amd/src`; rebuild `amd/build` with `npx grunt amd --root=public/admin/tool/participantscustomfilter` in a Moodle checkout.
- CI (`.github/workflows/ci.yml`) runs `moodle-plugin-ci` on Moodle 4.5 and 5.3 with PostgreSQL and MariaDB; run it locally with [`act`](https://github.com/nektos/act) before pushing.

## Support and privacy / Suporte e privacidade

- Issue tracker / Rastreador de issues: <https://github.com/moodle-by-kelsoncm/moodle-tool_participantscustomfilter/issues>
- Privacy / Privacidade: the plugin stores no personal data / o plugin não guarda dados pessoais.
- Translations / Traduções: only English is shipped; other languages go through AMOS / o pacote traz só inglês; outros idiomas via AMOS.

## License / Licença

GNU GPL v3 or later. See [LICENSE](LICENSE).
