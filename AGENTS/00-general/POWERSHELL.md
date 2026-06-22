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

- In PowerShell, do not pass raw JSON literals directly in command arguments.
- Use dedicated consistency controllers for supported file types:
  - Model views: `php run.php --do=core_test_view-consistency --entity=<Entity> --view_id=<type.name>`
  - Dashboard views: `php run.php --do=core_test_dashboard-consistency --entity=<Entity> --view_id=dashboard.<name>`
  - Model translations: `php run.php --do=core_test_translation-consistency --entity=<Entity> --lang=<lang>`
  - Menu definitions: `php run.php --do=core_test_menu-consistency --package=<package> --menu_id=<app.position>`
  - Package route files: `php run.php --do=core_test_route-consistency --package=<package> --file=<priority-name.json>`
- Use this pattern for direct schema validation with `core_json-validate`:
  - `$json = Get-Content -Raw -Encoding UTF8 path/to/file.json`
  - `php run.php --get=core_json-validate --json="$json" --schema_id=<schema> --package=<package> --strict=false`
- Use compact JSON for direct schema validation:
  - `$json = Get-Content -Raw -Encoding UTF8 path/to/file.json | ConvertFrom-Json | ConvertTo-Json -Depth 100 -Compress`
  - `php run.php --get=core_json-validate --json="$json" --schema_id=<schema> --package=<package> --strict=false`
- Use PowerShell's stop-parsing marker for inline PHP smoke tests that require exact nested quotes:
  - Place `--%` immediately after `php`.
  - PowerShell passes the rest of the line through literally.
  - `php --% -r "var_export($argv);" -- --json={"name":"Test","layout":{"groups":[]}}`
- Use normal `php run.php ...` commands for eQual controllers. Reserve `php --% -r ...` for inline PHP snippets and quote-preservation checks.

## Run eQual commands through the PHP entry point

- Documentation examples may use `./equal.run`.
- In PowerShell, prefer `php run.php` from the project root and keep the same flags and arguments.
- If explicitly testing the wrapper script itself, use the PowerShell-compatible wrapper path.

## Avoid PowerShell parsing surprises

- Use multiple `rg -e` flags instead of raw regex pipes (`|`) in search commands:
  - `rg -e '"section.invite"' -e '"section.minutes"' packages/realestate`
- Use `-LiteralPath` for paths supplied to PowerShell cmdlets:
  - `Get-Content -Raw -Encoding UTF8 -LiteralPath "path/to/file.json"`
  - `Remove-Item -LiteralPath "path/to/file.tmp"`
- Use `rg --files`, `rg -n`, and `rg -e` for repository searches:
  - `rg --files packages/core`
  - `rg -n -e "needle" packages/core`
- Use PowerShell-compatible cmdlet options only. Keep file inspection commands simple:
  - `Get-Content -TotalCount 40 -LiteralPath "path/to/file"`
  - `Get-Content -Tail 40 -LiteralPath "path/to/file"`
- Use `php --% -r ...` for inline PHP snippets that need literal quotes or braces:
  - `php --% -r "var_export($argv);" -- --json={"name":"Test"}`

## Git and output interpretation

- In this repo, run `git status` and `git diff` from the actual Git root, often `packages/`, not necessarily the workspace root.
- Treat unrelated dirty files as user changes; report them but do not revert them.
- If `git` reports index lock unlink warnings, do not delete lock files unless explicitly needed and approved by the existing safe rule.

## Validation commands

- For JSON syntax:
  - `Get-Content -Raw -Encoding UTF8 <file> | ConvertFrom-Json | Out-Null`
- For eQual schema validation, prefer the dedicated consistency controllers listed above.
- When no dedicated controller exists, use UTF-8 variables:
  - `$json = Get-Content -Raw -Encoding UTF8 <file>`
  - `php run.php --get=core_json-validate --json="$json" --schema_id=<schema> --package=<package> --strict=false`
- Record both exit code and visible output. An exit code `0` with no output is considered successful unless the project convention says otherwise.
