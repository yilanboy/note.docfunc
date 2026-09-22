---
date: '2026-07-06'
tags: [zed]
---

# 我的 Zed 設定

一般設定。

```json
{
    // ── Editor behavior ──
    "vim_mode": true,
    "base_keymap": "JetBrains",
    "relative_line_numbers": "enabled",
    "vertical_scroll_margin": 10,
    "autosave": "on_focus_change",
    "format_on_save": "on",
    "ensure_final_newline_on_save": true,
    "diff_view_style": "split",
    "cli_default_open_behavior": "new_window",

    // ── Appearance ──
    "theme": {
        "mode": "system",
        "light": "One Light",
        "dark": "Catppuccin Frappé"
    },
    "icon_theme": {
        "mode": "system",
        "light": "Zed (Default)",
        "dark": "Catppuccin Mocha"
    },

    // ── Fonts & sizing ──
    "ui_font_family": "JetBrainsMono Nerd Font",
    "ui_font_size": 20.0,
    "buffer_font_family": "JetBrainsMono Nerd Font",
    "buffer_font_size": 20.0,
    "buffer_line_height": {
        "custom": 2
    },
    "agent_buffer_font_size": 20.5,
    "git_commit_buffer_font_size": 20.5,
    "terminal": {
        "font_size": 20
    },

    // ── Panels (dock placement) ──
    "project_panel": {
        "dock": "right"
    },
    "outline_panel": {
        "dock": "right"
    },
    "collaboration_panel": {
        "dock": "right"
    },
    "git_panel": {
        "dock": "right"
    },

    // ── Languages ──
    "languages": {
        "PHP": {
            "formatter": {
                "external": {
                    "command": "mago",
                    "arguments": [
                        "format",
                        "--stdin-input",
                        "--stdin-filepath",
                        "{buffer_path}"
                    ]
                }
            }
        }
    },

    // ── AI / agent ──
    "agent": {
        "single_file_review": false,
        "sandbox_permissions": {
            "allow_unsandboxed": true
        },
        "commit_message_instructions": "Write the commit message in the Conventional Commits format:\n\n<type>(<scope>): <description>\n\n- <change 1>.\n- <change 2>.\n- <change 3>.\n\nRules:\n- <type> is one of: feat, fix, refactor, perf, docs, test, build, ci, chore, style, revert.\n- <scope> is optional; when included, wrap it in parentheses and use the affected module, package, or area (e.g. lowercase, kebab-case).\n- <description> is a concise summary in the imperative mood, lowercase, no trailing period, and under 72 characters.\n- Leave one blank line between the subject and the body.\n- The body is a bullet list ('- ') where each bullet describes one change in the imperative mood and ends with a period.\n- Add a bullet only for changes that are actually present in the diff; do not invent or pad. Use as many bullets as there are distinct changes, but a single-change commit may have just one bullet.\n- Do not include ticket references, footers, or trailers unless they already appear in the staged changes.",
        "default_model": {
            "provider": "zed.dev",
            "model": "gpt-5.6-luna",
            "enable_thinking": true,
            "effort": "high"
        },
        "dock": "left",
        "favorite_models": [],
        "model_parameters": [],
        "commit_message_model": {
            "provider": "zed.dev",
            "model": "gemini-3.5-flash"
        }
    },
    "agent_servers": {},
    "edit_predictions": {
        "provider": "zed"
    }
}
```

Keymap 設定（包含 Vim 模式）：

```json
[
    {
        "context": "Workspace",
        "bindings": {
            // "shift shift": "file_finder::Toggle"
        }
    },
    {
        "context": "VimControl && !menu",
        "bindings": {
            // put key-bindings here if you want them to work in normal & visual mode
            "z h": "vim::StartOfLineDownward",
            "z l": "vim::EndOfLineDownward"
        }
    },
    {
        "context": "vim_mode == normal && !menu",
        "bindings": {
            // put key-bindings here if you want them to work only in normal mode
        }
    },
    {
        "context": "vim_mode == visual && !menu",
        "bindings": {
            // visual, visual line & visual block modes
            ">": "editor::Indent",
            "<": "editor::Outdent",
            "shift-s": "vim::PushAddSurrounds"
        }
    },
    {
        "context": "vim_mode == insert",
        "bindings": {
            // put key-bindings here if you want them to work in insert mode
        }
    },
    {
        "context": "AgentPanel",
        "bindings": {
            "cmd-?": "workspace::ToggleLeftDock"
        }
    }
]
```

## PHP 自動格式化（使用 Mago）

Zed 的外部 Formatter 機制會將 buffer 內容透過 `stdin` 傳入，並期望從 `stdout` 接收格式化後的結果。
這部分 Mago 有提供

此外，透過 Zed 傳入 `arguments: ["{buffer_path}"]`，腳本可以得知當前編輯檔案的原始路徑，
Mago 會自動根據專案中的 `mago.toml` 設定檔對 PHP 檔案進行排版。

### 全域安裝 Mago（MacOS）

```bash
brew install mago
```

初次使用可以使用 `mago init` 建立專案設定檔：

```bash
mago init
```

### Zed Formatter 設定

在 `~/.config/zed/settings.json` 加入 `languages`：

```json
{
    "languages": {
        "PHP": {
            "formatter": {
                "external": {
                    "command": "mago",
                    "arguments": [
                        "format",
                        "--stdin-input",
                        "--stdin-filepath",
                        "{buffer_path}"
                    ]
                }
            }
        }
    }
}
```

- **`{buffer_path}`**：Zed 內建巨集，會將當前正在編輯的檔案完整絕對路徑作為 `$1` 傳入腳本。
- 由於設定中已包含 `"format_on_save": "on"`，存檔時即會自動完成格式化。

## 透過 Oxc 擴充套件排版 Svelte 檔案

若專案使用 Svelte（例如搭配 Vite Plus / Oxc）並希望透過 Zed 的 **Oxc** 擴充套件（提供 `oxfmt` 語言伺服器）進行檔案格式化，可以在專案根目錄的 `.zed/settings.json` 中針對 `Svelte` 語言進行設定。

### 安裝 Zed 擴充套件

在 Zed 的 Extensions 管理器（`Cmd+Shift+X` / `Ctrl+Shift+X`）中安裝：

- **Svelte**
- **Oxc**

### 使用 Oxc 的專案設定

Oxc 擴充套件會在專案根目錄建立 `.zed/settings.json` 檔案，裡面有多種前端語言的排版設定。
預設沒有 Svelte 語言的設定，需要在 `languages` 中加入 `Svelte` 區段：

```json
{
    "languages": {
        "Svelte": {
            "format_on_save": "on",
            "prettier": {
                "allowed": false
            },
            "formatter": [
                {
                    "language_server": {
                        "name": "oxfmt"
                    }
                }
            ]
        }
    }
}
```

- **`format_on_save`**：設為 `"on"`，存檔時自動執行排版。
- **`prettier.allowed`**：設為 `false`，停用 Prettier 格式化，避免與 `oxfmt` 衝突或被搶先接管。
- **`formatter`**：指定由 Oxc 提供的 `oxfmt` 語言伺服器負責排版。
