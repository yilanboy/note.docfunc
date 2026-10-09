---
date: '2026-07-06'
updated: '2026-10-09'
tags: [zed]
---

# 我的 Zed 設定

我的 Zed 個人設定與快捷鍵配置。

## settings.json

完整 `~/.config/zed/settings.json`：

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
    "dark": "Catppuccin Frappé"
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

  // ── Languages & Formatters ──
  "languages": {
    "PHP": {
      "formatter": {
        "external": {
          "command": "mago",
          "arguments": ["format", "--stdin-input", "--stdin-filepath", "{buffer_path}"]
        }
      }
    },
    "CSS": {
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
    },
    "GraphQL": {
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
    },
    "Handlebars": {
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
    },
    "HTML": {
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
    },
    "JavaScript": {
      "format_on_save": "on",
      "prettier": {
        "allowed": false
      },
      "formatter": [
        {
          "language_server": {
            "name": "oxfmt"
          }
        },
        {
          "code_action": "source.fixAll.oxc"
        }
      ]
    },
    "JSON": {
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
    },
    "JSON5": {
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
    },
    "JSONC": {
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
    },
    "Less": {
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
    },
    "Markdown": {
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
    },
    "MDX": {
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
    },
    "SCSS": {
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
    },
    "TypeScript": {
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
    },
    "TSX": {
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
    },
    "Vue.js": {
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
    },
    "YAML": {
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
    },
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
  },

  // ── AI / Agent & ACP ──
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
  "agent_servers": {
    "antigravity-acp": {
      "default_config_options": {
        "model": "gemini-3.8-flash-high"
      },
      "type": "registry"
    }
  },
  "edit_predictions": {
    "provider": "zed"
  },

  // ── Language Servers (LSP) ──
  "lsp": {
    "oxlint": {
      "initialization_options": {
        "settings": {
          "configPath": null,
          "run": "onType",
          "disableNestedConfig": false,
          "fixKind": "safe_fix",
          "unusedDisableDirectives": "deny"
        }
      }
    },
    "oxfmt": {
      "initialization_options": {
        "settings": {
          "fmt.configPath": null,
          "run": "onSave"
        }
      }
    }
  }
}
```

## keymap.json

完整 `~/.config/zed/keymap.json`：

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
      "ctrl-?": "workspace::ToggleLeftDock"
    }
  }
]
```

## PHP 自動格式化（使用 Mago）

Zed 的外部 Formatter 機制會將 buffer 內容透過 `stdin` 傳入，並期望從 `stdout` 接收格式化後的結果。
這部分 Mago 有提供支援。

此外，透過 Zed 傳入 `arguments: ["{buffer_path}"]`，可得知當前編輯檔案的原始路徑，
Mago 會自動根據專案中的 `mago.toml` 設定檔對 PHP 檔案進行排版。

### 安裝 Mago（macOS / Homebrew）

```bash
brew install mago
```

初次使用可以使用 `mago init` 建立專案設定檔：

```bash
mago init
```

### Zed Formatter 設定

在 `~/.config/zed/settings.json` 中配置：

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

- **`{buffer_path}`**：Zed 內建巨集，會將當前正在編輯的檔案完整絕對路徑傳入。
- 搭配全域 `"format_on_save": "on"`，存檔時即會自動完成格式化。

## 前端與設定檔排版及 Lint（使用 Oxc: oxfmt / oxlint）

透過 Rust 開發的 **Oxc**（The JavaScript Oxidation Compiler）工具鏈取代傳統 Prettier 與 ESLint，能獲得極致的排版速度與低延遲反饋。

### 安裝 Zed 擴充套件

在 Zed 的 Extensions 管理器（`Cmd+Shift+X` / `Ctrl+Shift+X`）中安裝：

- **Oxc**：提供 `oxlint` 與 `oxfmt` 語言伺服器。
- **Svelte**、**Vue** 等對應語法擴充套件。

### 排版與 Lint 設定說明

在 `settings.json` 中，為所有支援的前端與設定檔語言設定以 `oxfmt` 排版：

1. **全面停用 Prettier**：設定 `"prettier": { "allowed": false }`，防止 Prettier 攔截或與 `oxfmt` 衝突。
2. **多語言支援**：包含 `JavaScript`、`TypeScript`、`TSX`、`Svelte`、`Vue.js`、`HTML`、`CSS`、`SCSS`、`Less`、`JSON`、`JSONC`、`JSON5`、`Markdown`、`MDX`、`YAML`、`GraphQL`、`Handlebars` 等 17 種語言格式。
3. **自動修復（Code Action）**：在 `JavaScript` 語言設定中加入 `source.fixAll.oxc`，存檔時自動執行 safe fix：
   ```json
   "formatter": [
     {
       "language_server": {
         "name": "oxfmt"
       }
     },
     {
       "code_action": "source.fixAll.oxc"
     }
   ]
   ```
4. **LSP 伺服器初始化參數**：
   - `oxlint`：設定 `"run": "onType"`（打字時即時檢查）、`"fixKind": "safe_fix"`、`"unusedDisableDirectives": "deny"`。
   - `oxfmt`：設定 `"run": "onSave"`，在檔案儲存時進行排版。

## AI / Agent 與 ACP（Agent Client Protocol）

Zed 內建強大的 AI Assistant 與 ACP 伺服器整合功能：

### 內建 Agent 與模型設定

- **主對話模型**：使用 `gpt-5.6-luna`，並啟用思考模式（`enable_thinking: true`, `effort: "high"`）。
- **Git Commit 訊息生成模型**：使用輕量快速的 `gemini-3.5-flash`，並配置專屬的 `commit_message_instructions` 指令，遵循 **Conventional Commits** 規範生成乾淨條理的 commit 訊息。
- **編輯預測**：`edit_predictions.provider` 設為 `zed`。
- **面板停靠**：Agent 面板預設停靠於左側（`dock: "left"`），其他面板（Project、Outline、Git）則停靠於右側。

### ACP 代理伺服器（Agent Server）

Zed 支援透過 ACP（Agent Client Protocol）介接外部 AI 代理工具：

```json
"agent_servers": {
  "antigravity-acp": {
    "default_config_options": {
      "model": "gemini-3.8-flash-high"
    },
    "type": "registry"
  }
}
```

配置 `antigravity-acp` 並選用 `gemini-3.8-flash-high` 模型，可讓 Antigravity 等 AI Agent 與 Zed 深度整合執行高階代理任務。

### 快捷鍵綁定

在 `keymap.json` 中配置：

```json
{
  "context": "AgentPanel",
  "bindings": {
    "ctrl-?": "workspace::ToggleLeftDock"
  }
}
```

在 Agent 面板中按下 `Ctrl + ?` 即可快速切換收合左側 Dock 面板。
