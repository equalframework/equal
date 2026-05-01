## Documentation eQual

eQual is a versatile, language-agnostic framework that helps manage the
interactions between front-end applications and business logic.  It aims to
provide reusable components, a self-documented API surface and a minimal
learning curve so developers can focus on delivering features quickly.

The official GIT repository of eQual documentation is: https://github.com/cedricfrancoys/equal-doc.git

```
/
├── docs
└── mkdocs.yml
```

* file mkdocs.yml holds all the configuration details along with the documentation structure
* main structure relates to folders in ./docs
* all files use markdown syntax
* filenames are lower-case with words separated by hyphens (no spaces)
* all images are be stored under ./docs/assets/img

### View documentation locally

Install the required dependencies and start a local server:

```bash
$ pip install mkdocs-material
$ mkdocs serve
```

The site will be available at <http://127.0.0.1:8000>.

### Key sections

* **Installation** – see `docs/getting-started/install/installation.md` for setup instructions.
* **Quick start** – see `docs/getting-started/quick-start.md` for an overview of the first commands to run.

Contributions can be made by forking the repository and submit one or more pull-requests to the main repository.
https://docs.github.com/en/pull-requests/collaborating-with-pull-requests/proposing-changes-to-your-work-with-pull-requests/creating-a-pull-request

### Additional support (markdown extensions):
Note: these are not supported in most standalone editors (e.g. Typora), but will be correctly displayed once converted to HTML (mkdoc service).

#### Tables
https://squidfunk.github.io/mkdocs-material/reference/data-tables/
Example:
```
| Method      | Description                          |
| ----------- | ------------------------------------ |
| `GET`       | :material-check:     Fetch resource  |
| `PUT`       | :material-check-all: Update resource |
| `DELETE`    | :material-close:     Delete resource |
```

#### Admonitions
https://squidfunk.github.io/mkdocs-material/reference/admonitions/
Example: 
```
!!! note

    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nulla et euismod
    nulla. Curabitur feugiat, tortor non consequat finibus, justo purus auctor
    massa, nec semper lorem quam in massa.
```

#### Task Lists
https://squidfunk.github.io/mkdocs-material/reference/lists/
```
- [x] Lorem ipsum dolor sit amet, consectetur adipiscing elit
- [ ] Vestibulum convallis sit amet nisi a tincidunt
    * [x] In hac habitasse platea dictumst
    * [x] In scelerisque nibh non dolor mollis congue sed et metus
    * [ ] Praesent sed risus massa
- [ ] Aenean pretium efficitur erat, donec pharetra, ligula non scelerisque
```
