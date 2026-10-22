## Lang Scanner

This project uses `pijler/lang-scanner` to keep the JSON translation files in sync with the translation calls in the code. It is configured by the `scanner.json` file at the project root.

### Rules

- After adding, changing or removing translation calls (e.g. `__('...')`, `trans('...')`, `$t('...')`), run `vendor/bin/scanner` to update the JSON language files.
- Do not add or remove translation keys in the JSON language files by hand, let the scanner do it. Only fill in the translated values.
- Always pass static strings to translation calls. Keys built at runtime (e.g. `__("status.$status")`) can't be detected.
- Run `vendor/bin/scanner --check --no-update` to verify that every language file has the same keys.
- Before removing unused translations with `--prune`, run it with `--dry-run` to review what would be removed.

@verbatim
<code-snippet name="Common Lang Scanner commands" lang="bash">
vendor/bin/scanner
vendor/bin/scanner --dirty
vendor/bin/scanner --unused
vendor/bin/scanner --prune --dry-run
vendor/bin/scanner --check --no-update
</code-snippet>
@endverbatim
