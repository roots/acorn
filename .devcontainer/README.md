# Acorn Development Container

This dev container provides a full WordPress development environment for working on Acorn, Acorn packages, and testing Sage themes.

## Quick Start

### VS Code (Recommended)
1. Open in VS Code with Dev Containers extension
2. Wait for container build and WordPress installation
3. Access site at http://localhost:8080

### Command Line
```bash
# Install devcontainer CLI
npm install -g @devcontainers/cli

# Start dev container
devcontainer up --workspace-folder .

# Execute commands in container
devcontainer exec --workspace-folder . wp --info
```

## Services

| Service | URL/Port | Purpose |
|---------|----------|---------|
| WordPress | http://localhost:8080 | Main application |
| Mailpit | http://localhost:8025 | Email testing UI |
| Database | localhost:3306 | MariaDB 10 |
| Redis | localhost:6379 | Object caching |

## Default Credentials

- **WordPress Admin**: `admin` / `password`
- **Database**: `database_name` / `database_user` / `database_password`

## Common Commands

```bash
# WordPress CLI
wp plugin list
wp theme activate sage
wp acorn optimize:clear

# Composer (Acorn)
cd /roots/acorn
composer test

# Build tools (themes)
cd /roots/app/public/content/themes/sage
bun install
bun run build
bun run dev
```

## Project Structure

- `/roots/acorn` - Your local Acorn repository
- `/roots/app` - WordPress installation (Bedrock)
- `/roots/app/public/content/themes/` - WordPress themes
- `/roots/app/public/content/plugins/` - WordPress plugins

## Features

- **PHP 8.4** with Xdebug configured
- **Composer** and **WP-CLI** pre-installed
- **Volta** for Node.js management
- **Bun** for fast JavaScript builds
- Auto-links themes/plugins based on `composer.json` type
- Git dirty prompt indicator

## Customization

**WordPress Site Title**: Defaults to "Acorn Testing". Override with:
```bash
WP_SITE_TITLE="My Custom Site"
```

**WordPress Repository**: Clones Bedrock by default. To use a different setup:
```bash
REPOSITORY_URL=https://github.com/your/repo.git
```

## Troubleshooting

- **Port conflicts**: Change ports in `.env` (e.g., `FORWARD_WEB_PORT=8081`)
- **Rebuild container**: `Dev Containers: Rebuild Container` in VS Code
- **Database issues**: Container includes automatic database reset on setup
