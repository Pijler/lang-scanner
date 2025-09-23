# 📌 Lang Scanner

A **universal translation key scanner** designed for **Laravel projects**.

It scans your codebase for translation calls, generates/updates your JSON language files, and ensures consistency across all locales.

Although it’s a Laravel package, it’s flexible enough to scan translations from **any type of project** (PHP, Vue, React, etc.) by customizing the `extensions` and `methods`.

### ✨ Features

-   🔍 Scans translation calls in any file type (`.php`, `.js`, `.ts`, `.vue`, `.jsx`, …).
-   ⚙️ Flexible configuration for methods (`__`, `trans`, `trans_choice`, `t`, `i18n.t`, …).
-   📂 Supports multiple paths and modules.
-   🧩 Extensible via `extends` for modular configs.
-   ✅ `check` or `--check` checks if all language files have the same keys.
-   📑 `sort` automatically orders JSON keys globally (**enabled by default**).
-   🔗 `--dot` saves translations in dot notation.
-   🚫 `--no-update` skips updating JSON files when running `check` (verification only).
-   🔀 `merge` or `--merge` combines duplicate or related translations into a single entry.
-   📝 `--duplicate` lists all duplicate translations for review.
-   ❌ `--remove` removes duplicate translations, keeping only the main version, when running `duplicate`.

### 📦 Installation

```bash
    composer require pijler/lang-scanner --dev
```

### ⚙️ Configuration

Create a `scanner.json` file at the root of your Laravel project.

#### Example: Laravel project:

```json
{
    "scanner": [
        {
            "lang_path": "lang/",
            "paths": ["resources/"],
            "extensions": [".php"],
            "methods": ["__(*)", "trans(*)", "trans_choice(*)"]
        }
    ]
}
```

#### Example: Vue project inside Laravel:

```json
{
    "scanner": [
        {
            "lang_path": "resources/lang/",
            "paths": ["resources/js/"],
            "extensions": [".js", ".vue"],
            "methods": ["$t(*)", "i18n.t(*)"]
        }
    ]
}
```

#### Example: React project inside Laravel:

```json
{
    "scanner": [
        {
            "lang_path": "resources/lang/",
            "paths": ["resources/js/"],
            "extensions": [".jsx", ".tsx"],
            "methods": ["t(*)", "i18n.t(*)"]
        }
    ]
}
```

#### Example: Multi-module config:

```json
{
    "extends": [
        "/module1/scanner.json",
        "/module2/scanner.json"
    ]
}
```

#### Example with `check`:

```json
{
    "scanner": [
        {
            "check": true,
            "lang_path": "lang/"
        }
    ]
}
```

#### Example with `merge`:

```json
{
    "scanner": [
        {
            "merge": true,
            "lang_path": "lang/"
        }
    ]
}
```

### 📋 Usage

Run the scan:

```bash
    ./vendor/bin/scanner
```

The command will:

-   Parse files defined in `paths` with the configured `extensions`.
-   Detect translation calls based on the provided `methods`.
-   Create or update JSON files inside `lang_path`.

### ⚡ CLI Options

#### `--check`

Checks if that all language JSON files inside lang_path have the same keys.

Reports inconsistencies when a key exists in one locale but is missing in another.

```bash
    ./vendor/bin/scanner --check
```

#### `--sort`

Sorts all JSON keys alphabetically.
Enabled globally by default, can be disabled if needed:

```bash
    ./vendor/bin/scanner --sort=false
```

#### `--dot`

Saves JSON translations in **dot notation**:

```json
{
    "auth.failed": "These credentials do not match our records."
}
```

```bash
    ./vendor/bin/scanner --dot
```

#### `--empty`

When using check, you may want to ignore empty translations as finished translations.

A good option to use in CI/CD pipelines.

```bash
    ./vendor/bin/scanner --check --empty
```

#### `--no-update`

When using check, prevents updating JSON files even if sort or dot are enabled.

Useful for CI/CD validation pipelines.

```bash
    ./vendor/bin/scanner --check --no-update
```

#### `--merge`

Ensures that all language JSON files inside lang_path have the same keys.

```bash
    ./vendor/bin/scanner --merge
```

#### `--duplicate`

Checks for and lists all duplicate records, allowing you to identify inconsistencies without modifying anything yet.

```bash
    ./vendor/bin/scanner --duplicate
```

#### `--remove`

After identifying duplicate records, automatically removes the repeated entries, keeping only the main version.

```bash
    ./vendor/bin/scanner --duplicate --remove
```

### 🧩 Extensibility with extends

Use `extends` to reuse configs across modules:

```json
{
    "extends": [
        "/packages/core/scanner.json",
        "/packages/admin/scanner.json"
    ]
}
```

### 💡 Best practices

-   Configure `methods` according to your framework (`__(*)`/`trans(*)` for Laravel, `t(*)` for Vue/React).
-   Always run with `check` in multi-language projects.
-   Keep `sort` enabled for clean, ordered JSON files.
-   Use `--no-update` in pipelines when you want validation only.
-   Avoid duplicate keys, as you may forget and translate them differently.
-   Centralize shared configs with `extends`.

Any improvement or correction can open a PR or Issue.

### 📝 License

Open-source under the [MIT license](LICENSE).

## 🚀 Thanks!
