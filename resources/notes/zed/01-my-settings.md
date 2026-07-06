# 我的 Zed 設定

一般設定。

```json
// Zed settings
//
// For information on how to configure Zed, see the Zed
// documentation: https://zed.dev/docs/configuring-zed
//
// To see all of Zed's default settings without changing your
// custom settings, run `zed: open default settings` from the
// command palette (cmd-shift-p / ctrl-shift-p)
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
        "dark": "One Dark"
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
        "dock": "left"
    },
    "outline_panel": {
        "dock": "left"
    },
    "collaboration_panel": {
        "dock": "left"
    },
    "git_panel": {
        "dock": "left"
    },

    // ── AI / agent ──
    "agent": {
        "commit_message_instructions": "Write the commit message in the Conventional Commits format:\n\n<type>(<scope>): <description>\n\n- <change 1>.\n- <change 2>.\n- <change 3>.\n\nRules:\n- <type> is one of: feat, fix, refactor, perf, docs, test, build, ci, chore, style, revert.\n- <scope> is optional; when included, wrap it in parentheses and use the affected module, package, or area (e.g. lowercase, kebab-case).\n- <description> is a concise summary in the imperative mood, lowercase, no trailing period, and under 72 characters.\n- Leave one blank line between the subject and the body.\n- The body is a bullet list ('- ') where each bullet describes one change in the imperative mood and ends with a period.\n- Add a bullet only for changes that are actually present in the diff; do not invent or pad. Use as many bullets as there are distinct changes, but a single-change commit may have just one bullet.\n- Do not include ticket references, footers, or trailers unless they already appear in the staged changes.",
        "default_model": {
            "provider": "zed.dev",
            "model": "gemini-3.5-flash",
            "enable_thinking": true,
            "effort": "MEDIUM"
        },
        "dock": "right",
        "favorite_models": [],
        "model_parameters": [],
        "commit_message_model": {
            "provider": "zed.dev",
            "model": "claude-sonnet-4-5"
        }
    },
    "agent_servers": {
        "claude-acp": {
            "default_config_options": {
                "mode": "auto"
            },
            "type": "registry"
        }
    },
    "edit_predictions": {
        "provider": "zed"
    }
}
```

Keymap 設定（包含 Vim 模式）：

```json
// Zed keymap
//
// For information on binding keys, see the Zed
// documentation: https://zed.dev/docs/key-bindings
//
// To see the default key bindings run `zed: open default keymap`
// from the command palette.
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
            "cmd-?": "workspace::ToggleRightDock"
        }
    }
]
```
