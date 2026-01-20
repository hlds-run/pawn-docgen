# Contributing to pawn-docgen

This repository provides a minimal Docker setup for running [alliedmodders/pawn-docgen](https://github.com/alliedmodders/pawn-docgen) with NGINX + PHP-FPM + MariaDB. The core parsing and generation logic is in `generate/update.php`; application configuration is in `settings.php`. The deployment environment is defined in `docker-compose.yml` and `docker/Dockerfile`.

---

## Project Overview

- **Purpose**: Run pawn-docgen in containers (NGINX + PHP-FPM + MariaDB) with automatic include-file downloads and one-time database initialization.
- **Web interface & parser**: `www/` and `generate/`
- **Documentation**: [README.md](README.md)

---

## Environment Requirements (Actual)

- Docker 24+
- Docker Compose v2+
- PHP runtime in container: `php:8.5-fpm` (see [docker/Dockerfile](docker/Dockerfile))
- MariaDB: `mariadb:12.1` (see [docker-compose.yml](docker-compose.yml))
- No local PHP, NGINX, or MySQL required — everything runs in Docker

**Note**: The `.env` file is referenced in [docker-compose.yml](docker-compose.yml) but not committed to the repository. Create it locally with the necessary variables.

---

## Local Setup (Actual Working Path)

1. **Prepare `.env`** file in the repository root with required environment variables:

   ```
   MYSQL_CONNECTION_STRING=mysql:host=mysql_db;dbname=pawn
   MYSQL_USER=<your_user>
   MYSQL_PASSWORD=<your_password>
   ```

2. **Start the services**:

   ```bash
   docker compose up -d
   ```

3. **Access the site**: http://localhost:83/ (see `docker-compose.yml`)

4. **Database initialization**: Runs automatically on first container start via entrypoint scripts in `docker/php/entrypoint/docker-entrypoint.d/`. The marker file `.db-initialized` prevents re-running on subsequent starts.

To manually trigger the generator (inside the `php-fpm` container):

```bash
docker compose exec php-fpm php /srv/pawn-docgen/generate/update.php
```

---

## Repository Structure (Facts)

```
docker/                          # Dockerfile and entrypoint scripts
  └─ php/entrypoint/
     ├─ docker-entrypoint.sh
     └─ docker-entrypoint.d/     # Init scripts (05-get-includes.sh, 10-init-database.sh)

docker-compose.yml               # Service definitions (php-fpm, nginx, mysql_db)
generate/                        # Documentation generator
  └─ update.php                  # Main parser and DB loader script
www/                             # Web frontend
  ├─ index.php                   # Router and main logic
  ├─ script.js
  ├─ style.css
  └─ template/                   # PHP templates (header, footer, function.php, etc.)

scripts/                         # Shell helper scripts
  ├─ get_amxmodx_includes.sh
  ├─ get_easyhttp_includes.sh
  └─ get_reapi_includes.sh

sql/                             # Database initialization schema
  └─ database_schema.sql

settings.php                     # Configuration (database connection, table names)
docker-compose.yml               # Docker Compose configuration
README.md                        # Project documentation
```

---

## Code Style & Conventions (Actual Practices)

- **Language**: Procedural PHP (no frameworks), often with global variables and functions
- **Array syntax**: `Array()` (older style) preferred in existing code
- **Naming**: Often PascalCase/UpperCamelCase (e.g., `$BigListOfFunctions`, `ParseCommentBlock()`)
- **Templates** (`www/template/*.php`): Plain PHP with echo/require, minimal OOP
- **Database access**: PDO with exceptions (`PDO::ERRMODE_EXCEPTION`)
- **Initialization & utilities**: Shell scripts in `docker/php/entrypoint/docker-entrypoint.d/` and `scripts/`

When contributing, **follow the existing style in the relevant file** rather than introducing modern PHP conventions without explicit request.

---

## File & Variable Naming Conventions (Extracted Facts)

- **Include files**: `.inc` extension
- **Configuration**: Central in `settings.php` (database table names via `$Columns` array)
- **Functions & variables** in code: `PascalCase` (e.g., `GetFunctionName()`, `$BigListOfFunctions`)
- **PHP classes/templates**: None prominent; mostly procedural

No separate style guide is tracked in the repository — use existing code as reference.

---

## Branch Naming (Actual Patterns)

Based on observable history:

- `feat/...` or `feature/...` — New features
- `fix/...` — Bug fixes
- `refactor/...` — Refactoring
- `chore/...` — Maintenance, tooling updates

Examples: `feat/bootstrap5-migration`, `fix/generate-transaction-rollback`, `chore/update-docker-images`.

---

## Commit Message Format (Recommended, Following Observed Style)

Recent history follows a Conventional Commits–like format. Continue this practice:

**Format**: `type(scope): brief description`

**Types**: `feat`, `fix`, `chore`, `refactor`, `docs`

**Examples** (from history):

- `feat(www): accessibility improvements (#15)`
- `fix(generate): fix transaction rollback errors, implicit commit (#3)`
- `refactor!: migrate to Bootstrap 5 and upgrade JavaScript libraries (#8)`

**Notes**:

- Use `!` after type (e.g., `refactor!:`) to mark breaking changes
- Keep the first line under 72 characters
- Include PR number if applicable (e.g., `#15`)

---

## How to Contribute

### 1. Prepare Your Branch

```bash
git checkout -b feat/your-feature-name
# or: fix/your-bug-name, chore/your-update, etc.
```

### 2. Make Changes

- Follow the existing code style (procedural PHP, `Array()` syntax, PascalCase)
- Minimal, focused changes per PR — one logical task per branch
- Test locally via Docker:
  ```bash
  docker compose up -d
  docker compose exec php-fpm php /srv/pawn-docgen/generate/update.php
  ```

### 3. Commit Your Changes

```bash
git add .
git commit -m "type(scope): brief description"
```

### 4. Open a Pull Request

- Create PR to `master` branch
- Title format: Same as commit message (`feat(scope): ...`)
- Description:
  - What: Brief summary of changes
  - Why: Motivation or bug report
  - How to test: Local commands or steps
  - **Breaking changes**: Explicitly mention if applicable

### 5. Review & Merge

- Maintainers will review and provide feedback
- No automated CI found — manual testing and code review are required
- Once approved, squash, rebase, or merge (maintainer's choice)

---

## What NOT to Do (Compatibility & Safety)

- **Do NOT** perform large architectural refactors (e.g., migrate to a framework) without discussing with maintainers first
- **Do NOT** change PHP version without consensus (currently `php:8.5-fpm`)
- **Do NOT** mass-replace code style (Array → [], PascalCase → camelCase) in a single commit — this breaks history and review
- **Do NOT** introduce a package manager (Composer, npm) without explicit approval
- **Do NOT** modify the database schema without migrations and maintainer agreement — DB init is built into the workflow
- **Do NOT** ignore backwards compatibility for breaking changes — always discuss and document

---

## Current State of Tools & Testing

| Tool                          | Status        |
| ----------------------------- | ------------- |
| CI (GitHub Actions, etc.)     | **Not found** |
| Linters (phpcs, eslint, etc.) | **Not found** |
| Unit/integration tests        | **Not found** |
| Code formatter                | **Not found** |

**If you add linting or CI**: Create a separate PR and document the intent clearly. Ensure backwards compatibility and don't break existing workflows.

---

## Pull Request Checklist

Before submitting your PR, verify:

- [ ] Branch name follows `type/description` pattern (e.g., `fix/bug-name`)
- [ ] Commit message(s) follow `type(scope): description` format
- [ ] Changes follow existing code style in the respective file(s)
- [ ] All changes work locally: `docker compose up -d` + manual testing
- [ ] PR title and description clearly explain _what_ and _why_
- [ ] No unrelated or "nice-to-have" changes mixed into one PR
- [ ] Database changes (if any) include migration plan
- [ ] No new dependencies added without approval

---

## Questions or Issues?

- Check [README.md](README.md) for setup and usage
- Open an issue with a clear description of the problem
- For sensitive security issues, please reach out to maintainers privately

---

Thank you for contributing!
