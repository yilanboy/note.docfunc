<p align="center">
  <img src="public/favicon.svg" alt="note.docfunc icon" width="96" height="96">
</p>

# note.docfunc

[![Laravel Framework](https://img.shields.io/badge/Laravel-13-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![Inertia.js](https://img.shields.io/badge/Inertia.js-v3-9553E9?style=flat-square)](https://inertiajs.com)
[![Svelte](https://img.shields.io/badge/Svelte-5-FF3E00?style=flat-square&logo=svelte)](https://svelte.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-4-06B6D4?style=flat-square&logo=tailwindcss)](https://tailwindcss.com)
[![Pest Testing](https://img.shields.io/badge/Pest-4-01C1EE?style=flat-square)](https://pestphp.com)

A high-performance, beautiful, read-only markdown note and documentation site. This project serves as a single source of truth for personal technical learning notes, compiling markdown files server-side and rendering them client-side in a premium, responsive layout.

---

## ⚡ Key Features

- **File-based Note Architecture**: Stored directly in `resources/notes/`. The directory structure represents your categories and documents.
- **Dynamic Wildcard Routing**: Automatic clean URLs. Numeric sorting prefixes are automatically stripped (e.g. `resources/notes/php/01-swoole-confuse.md` maps to `/php/swoole-confuse`).
- **High-Performance Caching**: Dynamic note structures (`NoteRepository::tree`) and converted HTML output are aggressively cached using file modification time (`filemtime`) fingerprints, ensuring sub-millisecond response times without stale content.
- **On-Demand Client-Side Syntax Highlighting**: Pre-rendered HTML is highlighted client-side with **Shiki**, dynamically loading language grammars and themes (One Light / One Dark Pro) only when a code block is detected.
- **Premium Design & Aesthetics**: Built with Tailwind CSS 4 and custom fonts (Inter for English, Noto Sans TC for Traditional Chinese, and JetBrains Mono for code blocks). Features a responsive navigation sidebar and a customizable dark mode.

---

## 🛠️ Tech Stack

### Backend

- **PHP**: `8.4+` (utilizes strict types, property promotion, constructor promotion)
- **Laravel Framework**: `13`
- **Inertia Laravel**: `v3`
- **Markdown Converter**: Standard CommonMark via Laravel's built-in `Str::markdown()`

### Frontend

- **Svelte**: `5` (Runes, snippets, and clean state management)
- **Inertia Svelte**: `v3`
- **Tailwind CSS**: `4` (with `@tailwindcss/vite` and `@tailwindcss/typography`)
- **Shiki**: Dual-theme client-side syntax highlighting
- **Icons**: `@lucide/svelte`

### Quality Assurance & Linting

- **Pest PHP**: `4` (with Pest Laravel & Pest Browser integration)
- **Laravel Pint**: PHP code styler
- **Oxfmt**: Ultra-fast JS/TS formatter
- **Svelte Check**: Static type checker for Svelte files

---

## 📂 Project Structure

```text
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ShowHomeController.php      # Render / (loads resources/notes/README.md)
│   │   │   ├── ShowCategoryController.php  # Render /{category} (scans category README and note lists)
│   │   │   └── ShowNoteController.php      # Render /{category}/{note}
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php   # Shares global navigation tree
│   └── Services/
│       ├── MarkdownConverter.php           # Server-side HTML generation & caching
│       └── NoteRepository.php              # Scans, parses, and retrieves note structures
├── config/
│   └── notes.php                           # Paths and overrides for category display names
├── resources/
│   ├── css/
│   │   └── app.css                         # Tailwind CSS imports, fonts (Inter/Noto/JetBrains) & overrides
│   ├── js/
│   │   ├── components/                     # Sidebar, Header, Layout components
│   │   ├── pages/                          # Svelte page entries
│   │   ├── shared/                         # Highlight helper & sidebar state
│   │   └── ssr.ts                          # Server-side rendering entry point
│   └── notes/                              # Markdown files (Single Source of Truth)
└── tests/                                  # Unit, Feature, and E2E Browser tests
```

---

## 🚀 Getting Started

### 1. Prerequisites

- **PHP 8.4+**
- **Composer**
- **Node.js 22+ & pnpm >= 11**

### 2. Installation

Clone the repository, copy the environment file, and install backend and frontend dependencies:

```bash
# Clone the repository
git clone https://github.com/yilanboy/note.docfunc.git
cd note.docfunc

# Setup local environment configurations
cp .env.example .env

# Install PHP dependencies
composer install

# Install JS/TS dependencies
pnpm install

# Generate application key & set up database.sqlite skeleton
php artisan key:generate
touch database/database.sqlite
php artisan migrate
```

### 3. Local Development

Run the concurrent server runner which starts the Artisan server, queue listener, Pail log tailer, and Vite server under a single command:

```bash
composer run dev
```

Visit the application at `http://127.0.0.1:8000`.

---

## ✍️ Writing & Organizing Notes

The notes directory is located in `resources/notes/`.

### Folder and File Naming Rules

1. **Categories**: Create a subdirectory inside `resources/notes/` (e.g. `resources/notes/kubernetes`).
2. **Category Names**: By default, category folder names are capitalized and formatted into headlines using `Str::headline()`. To override the title representation (e.g. for acronyms like AWS, K8s, API), configure them in [config/notes.php](file:///Users/allenjiang/code/php/note.docfunc/config/notes.php):
    ```php
    'display_names' => [
        'k8s' => 'K8s',
        'aws' => 'AWS',
        'tailwind-css' => 'Tailwind CSS',
    ]
    ```
3. **Documents**: Create markdown files inside categories. Use numeric prefixes for sorting order (e.g., `01-install.md`, `02-architecture.md`).
4. **URL Slugs**: The system automatically strips the sorting prefix to keep URLs clean:
    - File path: `resources/notes/php/07-property-hook.md`
    - URL: `/php/property-hook`
5. **Document Titles**: Note titles are resolved by parsing the first `# H1` tag inside the markdown file. If no H1 tag is found, it falls back to the headline-case representation of the slug.
6. **Images**: Do not put images in the repository. Standard practice is to upload images to an external object store (e.g. S3) and reference them via absolute URLs.

---

## 🧪 Testing & Code Styling

### Running Tests

Execute the Pest suite (which includes E2E browser smoke tests powered by Playwright/Pest-Browser):

```bash
php artisan test
```

### PHP Code Formatting

Align PHP code with the project's formatting standard:

```bash
vendor/bin/pint --format agent
```

### Svelte & TypeScript Code Formatting

Verify types and format JS/Svelte code:

```bash
# Format JS/TS/Svelte files
pnpm run fmt

# Run Svelte compiler diagnostic checks
pnpm run check
```
