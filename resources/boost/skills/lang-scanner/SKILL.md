---
name: lang-scanner
description: Scan translation calls and keep JSON language files in sync with Lang Scanner, including scanner.json configuration, checking, merging, duplicates and unused keys.
---

# Lang Scanner

## When to use this skill

Use this skill when adding or changing translations, editing the `scanner.json` file, or checking, merging, deduplicating or pruning the JSON language files.

## How it works

`vendor/bin/scanner` reads the `scanner.json` file at the project root, scans the configured `paths` for files with the configured `extensions`, extracts the keys passed to the configured `methods`, and creates or updates the JSON files inside `lang_path`.

## Configuration

Each entry of `scanner` is a config. Configs that share the same `lang_path` are processed together.

```json
{
    "scanner": [
        {
            "lang_path": "lang/",
            "paths": ["resources/", "app/"],
            "extensions": [".php"],
            "methods": ["__(*)", "trans(*)", "trans_choice(*)"]
        },
        {
            "lang_path": "lang/",
            "paths": ["resources/js/"],
            "extensions": [".js", ".ts", ".vue"],
            "methods": ["$t(*)", "i18n.t(*)"]
        }
    ]
}
```

- Each method must contain exactly one `*` placeholder, which marks where the key is.
- `check: true` can be set per config instead of passing the flag. The other modes are only enabled by their flags.
- `extends` loads other configs, which is useful for modules: `{"extends": ["/modules/admin/scanner.json"]}`.
- The file is validated before any change. Unknown keys, wrong types and missing options fail with a clear message.

Options required by each mode:

| Mode        | Required options                              |
| ----------- | --------------------------------------------- |
| `update`    | `lang_path`, `paths`, `extensions`, `methods` |
| `unused`    | `lang_path`, `paths`, `extensions`, `methods` |
| `duplicate` | `lang_path`, `methods`                        |
| `check`     | `lang_path`                                   |
| `merge`     | `lang_path`                                   |

## Commands

| Command                                  | Description                                                     |
| ---------------------------------------- | --------------------------------------------------------------- |
| `vendor/bin/scanner`                     | Scans the code and adds the missing keys to the language files. |
| `vendor/bin/scanner --dirty`             | Scans only the files with uncommitted changes.                  |
| `vendor/bin/scanner --diff=main`         | Scans only the files changed since branching off from `main`.   |
| `vendor/bin/scanner --check`             | Checks if all language files have the same keys.                |
| `vendor/bin/scanner --check --no-update` | Checks without changing any file (good for CI).                 |
| `vendor/bin/scanner --check --no-empty`  | Also reports empty translations as missing.                     |
| `vendor/bin/scanner --merge`             | Adds the keys of each language file to all the others.          |
| `vendor/bin/scanner --duplicate`         | Lists keys repeated across configs (e.g. modules).              |
| `vendor/bin/scanner --duplicate --remove` | Removes the repeated keys, keeping only the first config's.   |
| `vendor/bin/scanner --unused`            | Lists keys that are not used in the code (fails if any).        |
| `vendor/bin/scanner --prune`             | Removes the unused keys from every language file.               |
| `vendor/bin/scanner --dot`               | Saves the translations in dot notation.                         |
| `vendor/bin/scanner --sort=false`        | Keeps the keys in their current order.                          |
| `vendor/bin/scanner --config=path.json`  | Uses another configuration file.                                |

Add `--dry-run` to any command to see what would change without changing any file.

## Best practices

- Always run `vendor/bin/scanner --prune --dry-run` before `--prune`, and review the keys that would be removed.
- `--unused` and `--prune` scan the whole project, so they can't be combined with `--dirty`, `--diff` or paths.
- Keys built at runtime (e.g. `__("status.$status")`) are reported as unused. Prefer static keys, or keep those keys in mind before pruning.
- Using a key as a group (e.g. `__('menu')`) keeps all of its nested keys.
- Don't add or remove keys in the JSON files by hand. Change the code and run the scanner, then fill in the translated values.
