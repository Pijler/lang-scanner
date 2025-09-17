# 📌 Lang Scanner

A **universal translation key scanner** designed for **Laravel projects**.

It scans your codebase for translation calls, generates/updates your JSON language files, and ensures consistency across all locales.

Although it’s a Laravel package, it’s flexible enough to scan translations from **any type of project** (PHP, Vue, React, etc.) by customizing the `extensions` and `methods`.

### ✨ Features

-   🔍 Scans translation calls in any file type (`.php`, `.js`, `.ts`, `.vue`, `.jsx`, …).
-   ⚙️ Flexible configuration for methods (`__`, `trans`, `trans_choice`, `t`, `i18n.t`, …).
-   📂 Supports multiple paths and modules.
-   🧩 Extensible via `extends` for modular configs.
-   ✅ `check` ensures all language files have the same keys.
-   📑 `sort` keeps your JSON files ordered and consistent.
-   🚀 Integrates seamlessly into Laravel via Artisan commands.

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
            "methods": ["__", "trans", "trans_choice"]
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
            "methods": ["$t", "i18n.t"]
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
            "methods": ["t", "i18n.t"]
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

#### Example with `check` + `sort`:

```json
{
    "scanner": [
        {
            "sort": true,
            "check": true,
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
-   Optionally run `check` and `sort` if enabled.

#### 🔍 check – Ensure consistency

Checks that **all language JSON** files inside `lang_path` have the same keys.

Example:

-   lang/en.json contains "welcome".
-   lang/pt.json does not.

The scanner will report an inconsistency.

#### 📑 sort – Automatic ordering

-   Can only be used together with check.
-   Sorts all JSON keys alphabetically.
-   Keeps diffs and pull requests clean and predictable.

### 🧩 Extensibility with extends

Use `extends` to reuse configs across modules:

```json
{
    "extends": ["/packages/core/scanner.json", "/packages/admin/scanner.json"]
}
```

### 💡 Best practices

-   Configure `methods` according to your framework (`__`/`trans` for Laravel, `t` for Vue/React).
-   Always run with `check` in multi-language projects.
-   Enable `sort` for clean, ordered JSON files.
-   Centralize shared configs with `extends`.

### 📝 License

Open-source under the [MIT license](LICENSE).
