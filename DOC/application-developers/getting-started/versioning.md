# Versioning Strategy

eQual uses a strict and declarative versioning model centered around the `VERSION` file.

The versioning system is designed to provide:

- strong installation traceability
- deterministic maintenance branches
- reproducible deployments
- simplified support workflows
- controlled upgrade paths
- strict compatibility tracking between installations

The model intentionally avoids build-based versioning and relies directly on repository state.



# Core Principles

## VERSION as Source of Truth

The official version of an eQual installation is defined exclusively by the root `VERSION` file.

Example:

```text
2.0.1
```

The `VERSION` file defines:

- the official semantic version
- the expected release lineage
- the expected Git branch
- the expected release tag

The `VERSION` file is the canonical reference for all runtime version information.



# Branch Types

eQual uses two categories of branches.



## Development Branches

Development branches are temporary integration branches used to prepare future releases.

Convention:

```text
dev-x.y
```

Examples:

```text
dev-2.1
dev-3.0
```

Development branches are used for:

- feature development
- refactoring
- architecture changes
- integration work

Development branches are temporary and are deleted once the corresponding stable release is created.



## Stable Release Branches

Stable branches represent maintained release lineages.

Convention:

```text
x.y.z
```

Examples:

```text
2.0.1
2.1.0
3.0.0
```

Stable branches are used for:

- production deployments
- maintenance
- bugfixes
- security patches
- client support

Each stable branch corresponds to the initial release of a maintained lineage.



# Release Tags

Each official release must have an immutable Git tag.

Convention:

```text
v{VERSION}
```

Examples:

```text
v2.0.1
v2.0.2
v2.1.0
```

Tags provide:

- exact reproducibility
- deployment traceability
- deterministic rollback capability
- GitHub release compatibility

Tags are immutable snapshots of official releases.



# Master Branch

The `master` branch always points to the latest recommended stable release.

Example:

```text
master -> 2.1.0
```

This ensures that:

- cloning `master` provides the latest stable version
- new installations are aligned with the current recommended release
- support alignment remains simple



# Release Lifecycle

A typical release lifecycle follows this structure:

```text
feature/*
    ↓
dev-2.1
    ↓
2.1.0
    ↓
v2.1.0
    ↓
master
```

After release:

- `dev-2.1` is deleted
- `2.1.0` becomes the maintained stable branch
- future development resumes on:

```text
dev-2.2
```



# Stable Branch Lifecycle

A stable branch represents a maintained release lineage.

Example:

```text
2.1.0
```

This branch may later produce:

```text
v2.1.0
v2.1.1
v2.1.2
```

The branch itself remains unchanged:

```text
2.1.0
```

while the `VERSION` file evolves to reflect the latest maintained release state.



# Version Alignment

eQual defines a strict consistency model between:

- the `VERSION` file
- the active Git branch
- the release tag

An installation is considered aligned when:

```text
branch == VERSION
```

or when the installation runs directly from:

```text
master
```

with a matching release state.

Examples:

| VERSION | branch | aligned |
|----------|--------|----------|
| 2.0.1 | 2.0.1 | true |
| 2.0.3 | 2.0.3 | true |
| 2.0.3 | master | true |
| 2.0.3 | 2.0.1 | false |
| 2.0.3 | dev-2.1 | false |
| 2.0.3 | feature/x | false |

Example of an aligned installation:

```text
branch  = 2.0.3
VERSION = 2.0.3
tag     = v2.0.3
```

Example of a mismatch:

```text
branch  = 2.0.1
VERSION = 2.0.3
tag     = v2.0.3
```

This situation may still function technically, but it indicates that the repository context does not match the declared release lineage and is therefore not considered a recommended or supported state.

The `mismatch` flag reflects this condition:

```text
mismatch = (
    branch != VERSION
    AND
    branch != master
)
```



# Installation Scenarios

The behavior of version detection depends on how eQual is installed.



# 1. Installation via Git (recommended)

Example:

```bash
git clone https://github.com/equalframework/equal
```

## Characteristics

- `.git` directory is present
- full Git metadata is available

## Behavior

The runtime can expose:

- declared version
- active branch
- release tag
- commit hash
- commit date
- working tree state

Example:

```json
{
  "version": "2.0.3",
  "tag": "v2.0.3",
  "branch": "2.0.3",
  "commit": "81303632",
  "date": "2026.05.06",
  "dirty": false,
  "mismatch": false,
  "source": "git"
}
```



# 2. Installation from ZIP Archive

Example:

```text
equal-2.0.3.zip
```

## Characteristics

- no `.git` directory
- no local branch information

## Behavior

The system:

- reads the `VERSION` file
- optionally resolves metadata using GitHub API

Example:

```json
{
  "version": "2.0.3",
  "tag": "v2.0.3",
  "commit": "81303632",
  "date": "2026.05.06",
  "source": "github"
}
```

If no network is available:

```json
{
  "version": "2.0.3",
  "source": "version"
}
```



# 3. Development Environments

Examples:

```text
dev-2.1
feature/*
custom branches
```

## Behavior

The runtime reflects:

- the declared target version
- the active development context
- local repository modifications

Example:

```json
{
  "version": "2.1.0-dev",
  "branch": "dev-2.1",
  "commit": "ba572ed4",
  "date": "2026.05.06",
  "dirty": true,
  "mismatch": true,
  "source": "git"
}
```



# Retrieving Runtime Version Information

You can retrieve runtime version information using:

```bash
./equal.run --get=version
```

This command provides a unified view of:

- declared release version
- Git execution context
- runtime repository state



# Example Output

```json
{
  "version": "2.0.3",
  "tag": "v2.0.3",
  "branch": "2.0.3",
  "commit": "81303632",
  "date": "2026.05.06",
  "dirty": false,
  "mismatch": false,
  "source": "git"
}
```



# Field Description



## `version`

Source:
- `VERSION` file

Meaning:
- official declared version of the installation



## `tag`

Source:
- Git or GitHub

Meaning:
- immutable release identifier

Convention:

```text
v{VERSION}
```



## `branch`

Source:
- Git

Meaning:
- active release lineage or development context

Examples:

```text
2.0.3
dev-2.1
feature/orm-refactor
```



## `commit`

Source:
- Git or GitHub

Meaning:
- exact repository revision



## `date`

Source:
- Git or GitHub

Meaning:
- commit date

Format:

```text
YYYY.MM.DD
```



## `dirty`

Source:
- Git

Meaning:
- indicates whether uncommitted local modifications exist

| value | meaning |
|--------|----------|
| true | local modifications present |
| false | working tree clean |



## `mismatch`

Meaning:
- indicates whether the runtime Git context matches the declared release version

| value | meaning |
|--------|----------|
| true | inconsistent runtime context |
| false | aligned installation |



## `source`

Indicates how metadata was resolved.

| value | meaning |
|--------|----------|
| `git` | local Git repository |
| `github` | resolved via GitHub API |
| `version` | VERSION file only |



# Interpretation Guidelines



## Stable Production Installation

```json
{
  "version": "2.0.3",
  "tag": "v2.0.3",
  "branch": "2.0.3",
  "dirty": false,
  "mismatch": false
}
```

This indicates:

- aligned release lineage
- clean repository state
- supported production installation



## Development Environment

```json
{
  "version": "2.1.0-dev",
  "branch": "dev-2.1",
  "dirty": true,
  "mismatch": true
}
```

This indicates:

- active development context
- non-release state
- local modifications



## Minimal ZIP Installation

```json
{
  "version": "2.0.3",
  "source": "version"
}
```

This provides:

- minimal but sufficient release identification



# Support Policy

eQual intentionally limits the number of supported release lines.

At any given time:

| State | Support Level |
|--------|----------------|
| N | full support |
| N-1 | maintenance support |
| N-2 | limited support |
| older | unsupported |

This policy ensures:

- manageable maintenance effort
- predictable upgrade paths
- rapid client realignment

Clients are expected to upgrade regularly.

The target model is to maintain installations reasonably close to the latest stable release line.



# Release Process

The release process is intentionally deterministic and relies entirely on:

- Git branches
- Git tags
- the `VERSION` file



# General Rules

- `VERSION` is the source of truth
- each official release must have:
  - a stable branch
  - a matching Git tag
- `master` always reflects the latest recommended stable release
- development never occurs directly on stable branches
- development branches are temporary



# Standard Release Workflow

Example: preparing release `2.1.0`.



## 1. Create development branch

```bash
git checkout -b dev-2.1
```

Development work occurs on:

- features
- refactors
- architecture changes
- integration work



## 2. Freeze development branch

Once stabilization begins:

- no new features are merged
- the branch becomes frozen for release preparation



## 3. Create stable release branch

Create the stable branch:

```bash
git checkout -b 2.1.0
```

Update `VERSION`:

```text
2.1.0
```



## 4. Commit release state

```bash
git commit -a -m "Release 2.1.0"
```



## 5. Create release tag

```bash
git tag v2.1.0
git push origin v2.1.0
```

The tag represents the immutable official release snapshot.



## 6. Publish stable branch

```bash
git push origin 2.1.0
```

The branch now becomes the maintained stable lineage.



## 7. Update master

```bash
git checkout master
git merge 2.1.0
git push origin master
```

This ensures that:

- `master` remains the recommended production reference
- new installations align automatically with the latest stable version



## 8. Remove development branch

```bash
git branch -d dev-2.1
```

Future work resumes on:

```bash
git checkout -b dev-2.2
```



# Patch Release Workflow

Patch releases occur directly on the maintained stable branch.

Example: releasing `2.1.1` from branch `2.1.0`.



## 1. Checkout stable branch

```bash
git checkout 2.1.0
```



## 2. Apply fixes

Apply:

- bug fixes
- hotfixes
- security patches
- maintenance updates



## 3. Update VERSION

```text
2.1.1
```



## 4. Commit patch release

```bash
git commit -a -m "Release 2.1.1"
```



## 5. Create patch tag

```bash
git tag v2.1.1
git push origin v2.1.1
```



## 6. Push updated branch

```bash
git push origin 2.1.0
```

The branch remains:

```text
2.1.0
```

while the `VERSION` file reflects the latest release state.

Future patch releases continue on the same stable branch:

```text
v2.1.2
v2.1.3
```

