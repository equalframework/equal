# Versioning Strategy

eQual uses a simple and declarative versioning model based on a `VERSION` file, optionally enriched with Git information when available.

## Core Principles

- The **official version** of eQual is defined in the root `VERSION` file.
- Each version corresponds to a **Git tag** following the convention:

```text
v{VERSION}
```

Example:

```text
VERSION = 2.0.0 → tag = v2.0.0
```

- No build step is required: a version is defined by the **state of the repository at a given moment**.
- Git is used as a **runtime source of truth** when available, but is not required.



# Installation Scenarios

The behavior of version detection depends on how eQual is installed.

## 1. Installation via Git (recommended)

Example:

```bash
git clone https://github.com/equalframework/equal
```

### Characteristics

- `.git` directory is present
- Full Git metadata is available

### Behavior

The system can expose:

- version (from `VERSION`)
- current branch
- commit hash
- commit date
- working tree state (clean/dirty)

Example:

```json
{
  "version": "2.0.0",
  "branch": "master",
  "commit": "ba572ed4",
  "date": "2026.04.28",
  "dirty": false,
  "mismatch": false,
  "source": "git"
}
```



## 2. Installation from a ZIP archive

Example:

```text
equal-2.0.0.zip
```

### Characteristics

- no `.git` directory
- no local commit information

### Behavior

The system:

- reads the `VERSION` file
- optionally resolves commit info via GitHub API (if network is available)

Example:

```json
{
  "version": "2.0.0",
  "commit": "ba572ed4",
  "date": "2026.04.28",
  "source": "github"
}
```

If no network is available:

```json
{
  "version": "2.0.0",
  "source": "version"
}
```



## 3. Modified or Development Environments

When working on a non-standard branch:

```text
feature/*
dev-*
custom branches
```

### Behavior

- `version` still reflects the `VERSION` file
- `branch` reflects the current working context
- `dirty` indicates uncommitted changes
- `mismatch` indicates divergence from expected version alignment

Example:

```json
{
  "version": "2.0.0",
  "branch": "dev-2.0",
  "commit": "ba572ed4",
  "date": "2026.04.28",
  "dirty": true,
  "mismatch": true,
  "source": "git"
}
```



# Version Alignment

eQual defines a simple consistency rule:

An installation is considered **aligned** if:

- the current branch is `master` (or `main`), OR
- the branch name matches the version exactly

```text
branch == "master"
OR
branch == VERSION
```

### Examples

| VERSION | branch    | aligned |
| ------- | --------- | ------- |
| 2.0.0   | master    | true    |
| 2.0.0   | 2.0.0     | true    |
| 2.0.0   | dev-2.0   | false   |
| 2.0.0   | feature/x | false   |

The `mismatch` flag is simply:

```text
mismatch = !aligned
```

------

# Retrieving the Current Version

You can retrieve version and runtime information using:

```bash
./equal.run --get=version
```

This command provides a **unified view of the current installation**, combining:

- declared version (`VERSION`)
- execution context (Git or not)
- runtime state (branch, commit, modifications)



## Example Output

```json
{
    "version": "2.0.0",
    "branch": "dev-2.0",
    "commit": "ba572ed4",
    "date": "2026.04.28",
    "dirty": true,
    "mismatch": true,
    "source": "git"
}
```



## Field Description

### `version`

- Source: `VERSION` file
- Meaning: **official declared version of eQual**
- Always present

------

### `branch`

- Source: Git (if available)
- Meaning: current Git branch
- Example: `master`, `2.0.0`, `dev-2.0`
- May be absent if Git is not available

------

### `commit`

- Source: Git or GitHub
- Meaning: short hash of the current commit
- Identifies the exact code state

------

### `date`

- Source: Git or GitHub
- Meaning: commit date (formatted as `YYYY.MM.DD`)
- Used to build a human-readable revision

------

### `dirty`

- Source: Git
- Meaning: indicates whether the working directory contains uncommitted changes

| value | meaning                     |
| ----- | --------------------------- |
| true  | local modifications present |
| false | working tree clean          |

------

### `mismatch`

- Meaning: indicates whether the current branch is aligned with the declared version

| value | meaning                                        |
| ----- | ---------------------------------------------- |
| true  | branch does not match expected version context |
| false | branch is consistent with version              |

------

### `source`

- Indicates how the information was resolved

| value     | meaning                  |
| --------- | ------------------------ |
| `git`     | local Git repository     |
| `github`  | resolved via GitHub API  |
| `version` | fallback to VERSION only |



## Interpretation Guidelines

### Production (expected)

```json
{
  "version": "2.0.0",
  "branch": "master",
  "dirty": false,
  "mismatch": false
}
```

- clean state
- aligned version
- safe to run

------

### Development

```json
{
  "version": "2.0.0",
  "branch": "dev-2.0",
  "dirty": true,
  "mismatch": true
}
```

- working on a development branch
- local changes present
- not aligned with release version

------

### Minimal environment (manual / zip)

```json
{
  "version": "2.0.0",
  "source": "version"
}
```

- minimal information
- still sufficient for identifying the release


Below is a concise documentation section you can add.

---

# Release Process

This section describes the standard procedure to create a new eQual release.

The process is intentionally simple and relies on Git conventions and the `VERSION` file.



## General Rules

* The version branch (`2.0.1`) allows:

  * hotfixes
  * maintenance of a specific release line
* The tag (`v2.0.1`) provides:

  * an immutable reference
  * compatibility with version resolution (GitHub API, etc.)
* The `VERSION` file is the **source of truth**
* Each release must correspond to a **Git tag**:

```text
v{VERSION}
```

Example:

```text
VERSION = 2.0.1 → tag = v2.0.1
```

* Each version should have a **dedicated branch** named after the version

---

## Standard Release Workflow

For any release (patch, minor, or major):

### 1. Create a version branch

```bash
git checkout -b 2.0.1
```

---

### 2. Update the VERSION file

Edit the `VERSION` file:

```text
2.0.1
```

---

### 3. Commit the change

```bash
git commit -a -m "Release 2.0.1"
```

---

### 4. Push the branch

```bash
git push origin 2.0.1
```

---

### 5. Create the release tag

```bash
git tag v2.0.1
git push origin v2.0.1
```

(Or create the release directly via GitHub using the same tag.)

---

## Main Release (Production)

For a main (official) release, an additional step is required:

### 6. Update the master branch

```bash
git checkout master
git merge 2.0.1
git push origin master
```

This ensures that:

* `master` always reflects the latest stable release
* new installations from `master` are aligned with the latest version

