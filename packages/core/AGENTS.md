# Agent Instructions — eQual Package

This package is part of an eQual-based system.

## Context

- This package follows the eQual framework conventions.
- Entities, views, actions and translations are tightly coupled.
- Changes must be consistent across:
  - classes/
  - views/
  - i18n/
  - actions/
  - data/

## Instructions

If a global AGENTS.md exists at repository root:
→ follow it

Otherwise:
→ apply standard eQual development rules

## Mandatory rules

- Work within this package only
- Do not modify `core`
- Do not assume a single-file change is sufficient
- Always consider impact on:
  - classes
  - views
  - i18n
  - actions
  - data

## Task execution

If playbooks are available:
→ use them

Otherwise:
→ follow standard workflow:
1. Identify impacted entity
2. Apply change in class
3. Update views
4. Update translations
5. Validate consistency

## Notes

This package is designed to be used within an eQual environment.