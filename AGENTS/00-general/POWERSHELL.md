# PowerShell Command Hygiene

These rules apply whenever the agent runs commands from PowerShell.

## Read JSON and translation files as UTF-8

- Always read JSON and translation files with explicit UTF-8 encoding:
  - `Get-Content -Raw -Encoding UTF8 path/to/file.json`
  - `Get-Content -Encoding UTF8 path/to/file.json`
- Do not trust default `Get-Content` output for accented French text; it may display mojibake if encoding is not explicit.
- Validate JSON syntax locally with PowerShell before schema validation:
  - `Get-Content -Raw -Encoding UTF8 path/to/file.json | ConvertFrom-Json | Out-Null`

## Avoid fragile JSON escaping in CLI args

- Avoid embedding large JSON directly in command arguments when possible.
- For model views, prefer `php run.php --do=core_test_view-consistency --entity=<Entity> --view_id=<type.name>` instead of passing the full view JSON to `core_json-validate`.
- Prefer assigning JSON to a variable first:
  - `$json = Get-Content -Raw -Encoding UTF8 path/to/file.json`
  - `php run.php --get=core_json-validate --json="$json" --schema_id=... --strict=false`
- If a command behaves strangely with pretty JSON, compact the JSON before passing it:
  - `$json = Get-Content -Raw -Encoding UTF8 path/to/file.json | ConvertFrom-Json | ConvertTo-Json -Depth 100 -Compress`

## Run eQual commands through the PHP entry point

- Documentation examples may use `./equal.run`.
- In PowerShell, prefer `php run.php` from the project root and keep the same flags and arguments.
- If explicitly testing the wrapper script itself, use the PowerShell-compatible wrapper path.

## Avoid PowerShell parsing surprises

- Avoid regex patterns containing raw pipes (`|`) in shell commands. Use multiple `rg -e` patterns instead:
  - Good: `rg -e '"section.invite"' -e '"section.minutes"' packages/realestate`
  - Avoid: `rg '"section.invite"|"section.minutes"' packages/realestate`
- Quote paths with spaces using `-LiteralPath` where available.
- Prefer `rg --files`, `rg -n`, and `rg -e` over complex PowerShell pipelines when searching.
- Do not rely on GNU/Linux options for PowerShell cmdlets. Example: older PowerShell versions may not support `Format-Hex -Count`; use simpler reads or version-compatible alternatives.

## Git and output interpretation

- In this repo, run `git status` and `git diff` from the actual Git root, often `packages/`, not necessarily the workspace root.
- Treat unrelated dirty files as user changes; report them but do not revert them.
- If `git` reports index lock unlink warnings, do not delete lock files unless explicitly needed and approved by the existing safe rule.

## Validation commands

- For JSON syntax:
  - `Get-Content -Raw -Encoding UTF8 <file> | ConvertFrom-Json | Out-Null`
- For eQual schema validation, use UTF-8 variables:
  - `$json = Get-Content -Raw -Encoding UTF8 <file>`
  - `php run.php --get=core_json-validate --json="$json" --schema_id=<schema> --package=<package> --strict=false`
- Record both exit code and visible output. An exit code `0` with no output is considered successful unless the project convention says otherwise.
