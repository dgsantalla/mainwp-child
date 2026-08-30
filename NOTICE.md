# TutorWP Conector — Derivative Notice

This plugin, **TutorWP Conector**, is a derivative work of **MainWP Child**
(https://github.com/mainwp/mainwp-child), licensed under the
**GNU General Public License v3.0 or later** (see `LICENSE.txt`).

The original copyright and license headers in the source files have been
preserved unmodified. Only the plugin's public identity (name, description,
author, plugin URI, version number, and update channel) was changed; the
internal PHP namespace (`MainWP\Child`), class names, database option names,
and hooks were left as in the original.

## Base version

`mainwp-child` **6.1.8** (https://github.com/mainwp/mainwp-child, tag `v6.1.8`).

## Changes made by TutorWP on top of the base version

- Added a new callable action, `maintenance_counts`, and the methods
  `maintenance_get_counts()` / `maintenance_get_reclaimable_bytes()` in
  `class/class-mainwp-child-maintenance.php`, so the TutorWP dashboard can show
  how many database items are pending cleanup — and how much table space is
  reclaimable — without deleting anything. Registered in
  `class/class-mainwp-child-callable.php`.
- Fixed a pre-existing upstream bug in `maintenance_db()`: the "unused tags"
  cleanup queried the `category` taxonomy but deleted terms from `post_tag`,
  so nothing was ever actually deleted.
- Fixed the same class of bug for the site's default category: WordPress
  never allows `wp_delete_term()` to delete a taxonomy's default term, so it
  must be excluded from both the pending-cleanup count and the deletion,
  or the count never reaches zero.
- Renamed the plugin's public identity to distribute it under TutorWP's own
  automatic-update channel (`Update URI`) instead of relying on
  wordpress.org, and fixed the plugin's self-referential hardcoded slug
  (`plugin_action_links_...`, the branding/update-nag helpers, and the
  system-report plugin list) to use the actual file path dynamically instead
  of the old hardcoded name.
- Bundled [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
  5.7 (`libs/plugin-update-checker/`, MIT license — see its own `license.txt`)
  to deliver updates from TutorWP's own server instead of wordpress.org. It
  is the sole mechanism that actually delivers updates; the `Update URI`
  header only opts the plugin out of wordpress.org's own check.

Full history of these changes, including the reasoning behind each one, is
kept in the TutorWP project repository
(`docs/HISTORIAL-IMPLEMENTACION.md` and the specs under
`docs/superpowers/specs/`).
