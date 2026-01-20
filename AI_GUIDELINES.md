# AI Guidelines for pawn-docgen

This document provides guidance for AI agents (GitHub Copilot, GPT-4, Claude, etc.) when contributing to or working with the pawn-docgen repository. Adhere to these guidelines to maintain compatibility, consistency, and quality.

---

## Core Principles

1. **Preserve existing style**: The codebase is procedural PHP with established conventions. Do not impose modern practices (strict typing, OOP, PSR-12) without explicit request.
2. **Respect architecture**: Database schema, Docker setup, and entrypoint logic are stable and intentional. Changes require discussion.
3. **Minimal scope**: Each PR/task should address one logical concern. Do not combine unrelated changes.
4. **Backwards compatibility**: Legacy code paths must remain functional unless breaking changes are explicitly approved.

---

## PHP Code Generation

### Preferred Style

```php
<?php
    // Use Array() instead of []
    $MyArray = Array(
        'key1' => 'value1',
        'key2' => 'value2'
    );

    // Use PascalCase for functions and variables
    function ParseCommentBlock( $Comment )
    {
        // Implementation
    }

    // Use descriptive variable names
    $BigListOfFunctions = Array();
    $StatementInsertFile = $Database->prepare( '...' );

    // Use procedural style, not OOP (unless already present in the file)
    $Result = $Database->query( 'SELECT ...' )->fetchAll();

    // Use PDO for database access
    $STH = $Database->prepare( 'SELECT ... WHERE id = :id' );
    $STH->bindValue( ':id', $id, PDO::PARAM_INT );
    $STH->execute();
```

### What to Avoid

```php
// ❌ Do not use [] syntax
$myArray = [];

// ❌ Do not use camelCase for functions and variables
function parseCommentBlock( $comment ) { }
$bigListOfFunctions = array();

// ❌ Do not add type hints or strict_types=1
function myFunction( string $name ): string { }

// ❌ Do not introduce OOP/inheritance unless the file already uses it
class DocumentationGenerator extends Parser { }

// ❌ Do not use modern PHP 8+ syntax (match, nullsafe operator) without consensus
$value = $obj?->method();
```

### File Modifications

- When editing existing files: Match the style of that specific file
- When creating new files: Follow the style of similar existing files
- If the file has mixed styles: Maintain the predominant pattern

---

## Templates & Web Frontend

### Guidelines for `www/template/*.php`

- Keep template logic simple: primarily echo/print HTML
- Use `<?php ?>` tags sparingly; prefer separate `require` blocks
- Pass data via variables set in `index.php`
- Do not introduce template engines (Twig, Blade) without approval

**Example**:
```php
<?php
    require __DIR__ . '/header.php';
?>
<h1><?php echo htmlspecialchars( $Title ); ?></h1>
<?php
    foreach( $Items as $Item ) {
        echo '<li>' . htmlspecialchars( $Item['name'] ) . '</li>';
    }
?>
<?php
    require __DIR__ . '/footer.php';
?>
```

### Do Not

- Replace template files with modern frameworks (Vue, React)
- Extract complex logic into separate template engines
- Introduce client-side build steps (webpack, vite) without discussion

---

## Database & SQL

### Rules

- Database schema changes must be backwards compatible or include a migration plan
- Use PDO prepared statements with named placeholders (`:param_name`)
- Use transactions for multi-statement operations
- Always handle `PDOException` and rollback failed transactions

**Example**:
```php
$Database->beginTransaction();
try {
    $stmt = $Database->prepare( 'INSERT INTO table VALUES (?, ?)' );
    $stmt->execute( Array( $value1, $value2 ) );
    $Database->commit();
} catch( PDOException $e ) {
    if ( $Database->inTransaction() ) {
        $Database->rollback();
    }
    throw new Exception( 'DB Error: ' . $e->getMessage() );
}
```

### Configuration

- All table names are configured in `settings.php` via the `$Columns` array
- Connection credentials come from `.env` (via `getenv()`)
- Do not hardcode table names or credentials in application code

---

## Docker & Deployment

### Do NOT Modify Without Approval

- Base image versions (currently `php:8.5-fpm-alpine`)
- Service versions (`nginx:1-alpine`, `mariadb:12.1`)
- Volume mount paths or structure
- Entrypoint logic (`docker/php/entrypoint/docker-entrypoint.sh`)

### If Changes Are Needed

1. Justify the change in the PR description (e.g., "security vulnerability", "performance fix")
2. Provide clear testing instructions
3. Mention any compatibility implications

### Adding Initialization Steps

If new initialization is required:
1. Add a shell script to `docker/php/entrypoint/docker-entrypoint.d/` (name format: `NN-descriptive-name.sh`)
2. Make it executable
3. Use the naming pattern already in place (`05-get-includes.sh`, `10-init-database.sh`)
4. The framework will auto-execute scripts in alphabetical order

---

## Commit & Branch Guidelines for AI

### Commit Message Format

```
type(scope): brief imperative description

Optional: longer explanation if needed. Keep lines ≤ 72 characters.
Related to: #issue-number (if applicable)
```

### Recommended Types

- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation only
- `chore`: Maintenance, dependencies, tooling
- `refactor`: Code restructuring (non-breaking)
- `refactor!`: Breaking refactor (use rarely, document clearly)

### Branch Naming

- Use lowercase with hyphens: `feat/add-dark-mode`, `fix/db-connection-timeout`
- Include issue reference if applicable: `fix/issue-42-connection-timeout`
- Keep branch names under 50 characters total

### Example PR Flow

```bash
# 1. Create branch
git checkout -b fix/generate-transaction-rollback

# 2. Make focused changes (one concern only)
# ... edit files ...

# 3. Commit with conventional format
git commit -m "fix(generate): prevent implicit transaction commits on error"

# 4. Push
git push origin fix/generate-transaction-rollback

# 5. Open PR
# Title: fix(generate): prevent implicit transaction commits on error
# Description: [See template below]
```

### PR Description Template (for AI)

```markdown
## What
Brief description of the change.

## Why
Motivation: bug fix, performance improvement, feature request, etc.

## How to Test
1. docker compose up -d
2. docker compose exec php-fpm php /srv/pawn-docgen/generate/update.php
3. [specific manual steps or observations]

## Related Issues
Closes #123 (if applicable)

## Breaking Changes
None / Yes (describe)
```

---

## File Organization & Naming

### When Creating New Files

- **PHP generators/utilities**: Place in `generate/` or `scripts/`
- **Web templates**: Place in `www/template/` (follow naming: `noun.php`)
- **Shell scripts**: Place in `scripts/` or `docker/php/entrypoint/docker-entrypoint.d/`
- **SQL**: Place in `sql/`

### Naming Conventions

- PHP files: lowercase, words separated by underscore: `get_includes.sh`, `update.php`
- Constants: UPPERCASE: `DB_TIMEOUT`, `MAX_FILE_SIZE`
- Functions: PascalCase: `ParseCommentBlock()`, `GetFunctionName()`
- Variables: PascalCase: `$MyVariable`, `$ParsedData`
- CSS classes: lowercase with hyphens: `.breadcrumb-item`, `.table-responsive`

---

## Testing & Validation

### Local Testing (Required)

Always verify changes locally before committing:

```bash
# Start services
docker compose up -d

# Check logs
docker compose logs -f php-fpm

# Run generator (if applicable)
docker compose exec php-fpm php /srv/pawn-docgen/generate/update.php

# Visit http://localhost:83/ and test manually
```

### Verification Checklist

- [ ] Docker services start without errors
- [ ] No database errors in logs
- [ ] Web interface loads at http://localhost:83/
- [ ] Changes work as intended (specific steps in PR)
- [ ] No new warnings/errors in container logs

### No Automated Tests Found

There are currently no unit tests, integration tests, or CI pipelines. Your manual verification is the validation.

---

## Documentation & Comments

### When to Comment

- Explain *why*, not *what* (code should be self-documenting)
- Document workarounds or non-obvious logic
- Explain complex parsing logic in `generate/update.php`

### Example Good Comments

```php
// Use goto here to prevent unnecessary redirects 
// (matches legacy SourceMod docgen behavior)
goto KidsNeverUseGotosPlease_ShowFunctions;

// Normalize line endings for cross-platform parsing
$File = str_replace( "\r\n", "\n", $File );
$File = str_replace( "\r", "\n", $File );
```

### Example Bad Comments

```php
// Increment count
$Count++;

// Loop through the array
foreach( $Array as $Item ) {
```

---

## Dependencies & Third-Party Code

### Current Dependencies

- **PHP extensions**: `pdo`, `pdo_mysql` (built into Docker image)
- **No Composer, npm, or other package managers are used**
- **No JavaScript frameworks** (vanilla JS only in `www/script.js`)
- **No CSS frameworks** (custom CSS + Bootstrap classes in newer commits)

### Adding Dependencies

**Do NOT** add dependencies without approval. If absolutely necessary:

1. Justify in the issue/PR (performance, security, feature)
2. Choose minimal, well-maintained packages
3. Ensure backwards compatibility
4. Update Docker image or create a `composer.json` / `package.json` with full documentation
5. Include installation steps in README

---

## Backwards Compatibility

### Breaking Changes

If you must introduce a breaking change:

1. Use `type!:` in commit message (e.g., `refactor!:`)
2. Clearly document what breaks and why
3. Provide migration path for users
4. Consider feature flags or gradual deprecation first

### Database Migrations

When schema changes are needed:

1. Do NOT drop tables or columns without migration scripts
2. Add migration logic to `docker-entrypoint.d/` or `generate/` as appropriate
3. Ensure `docker compose up -d` works for both fresh installs and upgrades
4. Test with existing data

---

## What AI Agents Should NOT Do

### Absolutely Forbidden

- ❌ Automatically merge PRs or force-push to `master`
- ❌ Delete branches or modify commit history without user approval
- ❌ Add credentials, secrets, or `.env` contents to code
- ❌ Modify `.gitignore` to track generated files or build artifacts
- ❌ Replace entire files without explaining changes

### Strong Discouraged (Requires Discussion)

- ❌ Upgrade major PHP version without testing
- ❌ Switch Docker images (nginx, MariaDB versions)
- ❌ Change database structure
- ❌ Refactor core logic (parsing in `generate/update.php`) without consensus
- ❌ Introduce package managers, linters, or CI without explicit request

### Use Caution (Test Thoroughly)

- ⚠️ Modifying request routing logic in `www/index.php`
- ⚠️ Changing template inheritance or structure
- ⚠️ Altering shell scripts in `docker/php/entrypoint/`
- ⚠️ Modifying SQL initialization scripts

---

## Handoff to Maintainers

When an AI completes a task:

1. **Create a draft PR** (if allowed by platform)
2. **Include detailed description**:
   - What was changed and why
   - Any assumptions made
   - Local testing performed
   - Potential risks or alternatives considered
3. **Link to issue** (if applicable)
4. **Wait for human review** — do not auto-merge

---

## Example: Proper AI Contribution Flow

```
Task: "Fix transaction rollback errors in generate/update.php"

1. Read the file and understand current logic
2. Identify the bug: implicit commits on error
3. Create branch: git checkout -b fix/generate-transaction-rollback
4. Write fix following existing PHP style (procedural, PDO, error handling)
5. Test locally:
   - docker compose up -d
   - docker compose exec php-fpm php /srv/pawn-docgen/generate/update.php
   - Verify database state
6. Commit: git commit -m "fix(generate): prevent implicit transaction commits on error"
7. Create PR with detailed description and testing steps
8. Wait for human approval before merge
```

---

## Questions or Clarifications

If an AI agent is uncertain about:

- Code style in a particular file → examine 5-10 similar examples in that file
- Database changes → ask in the issue/PR comment, do not assume
- Docker/deployment → test locally first, document assumptions
- Backwards compatibility → err on the side of caution, document impact clearly

---

## References

- [Contributing Guide](CONTRIBUTING.md)
- [README](README.md)
- [alliedmodders/pawn-docgen upstream](https://github.com/alliedmodders/pawn-docgen)

---

**Last Updated**: January 2026  
**Applies To**: pawn-docgen (Docker setup fork)
