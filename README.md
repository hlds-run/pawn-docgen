<center>
  <img width="2042" height="1578" alt="Pawn-docgen-logo" src="https://github.com/user-attachments/assets/c86d9d01-604b-4aed-851d-2f6832f93fc3" />
</center>

> [!TIP]
> Available at: https://docs.hlds.run/

# pawn-docgen (Docker setup)

Minimal Docker setup for running [alliedmodders/pawn-docgen](https://github.com/alliedmodders/pawn-docgen) with NGINX + PHP-FPM + MariaDB.

This repository provides:

- clean NGINX front controller configuration
- PHP-FPM entrypoint with `/docker-entrypoint.d` support (nginx-style)
- one-time database initialization via `generate/update.php`

## Requirements

- Docker 24+
- Docker Compose v2+

No local PHP, MySQL or NGINX installation required.

## Services

| Service | Image             | Purpose                       |
| ------- | ----------------- | ----------------------------- |
| nginx   | `nginx:1-alpine`  | HTTP frontend & reverse proxy |
| php-fpm | `php:8.5-fpm`     | Application runtime           |
| mariadb | `mariadb:12.1`    | Metadata storage              |
| og-gen  | `oven/bun:1-slim` | OG image generation service   |
| adminer | `adminer:5`       | DB web UI (optional)          |

## Volumes

| Path             | Description                                   |
| ---------------- | --------------------------------------------- |
| `./www`          | pawn-docgen web files                         |
| `./generate`     | documentation generator script                |
| `./include`      | pawn `.inc` includes                          |
| `./settings.php` | pawn-docgen and generate script configuration |

## Initialization

On first container start:

- `docker-entrypoint.sh` runs all scripts in `/docker-entrypoint.d`
- `10-init-database.sh` executes `php generate/update.php`
- a marker file prevents re-running on next starts

PHP-FPM continues running normally after initialization.

## Usage

```bash
docker compose up -d
```

## Access

- http://localhost:83/ — pawn-docgen documentation site
- http://localhost:83/amxmisc — specific include documentation
- http://localhost:3000/health — og-gen service health check (internal)

## Notes

- No domain configuration required (default server)
- Clean URLs are handled by NGINX
- No PHP code modifications (except fixes)
- Initialization logic follows official nginx/mysql image patterns
- OG image generation is handled by the `og-gen` service built with Bun and TypeScript
- See [docker/og-gen/README.md](docker/og-gen/README.md) for og-gen service documentation

## License

Upstream project: https://github.com/alliedmodders/pawn-docgen
This Docker setup follows upstream licensing.
