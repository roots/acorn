# Acorn Development Container

This dev container provides a full WordPress development environment for working on Acorn, Acorn packages, and testing Sage themes.

## Quick Start

### VS Code (Recommended)

1. Install the [Dev Containers](https://marketplace.visualstudio.com/items?itemName=ms-vscode-remote.remote-containers) extension
2. Open the repo in VS Code and click "Reopen in Container" when prompted
3. Wait for the container build, Bedrock clone, Composer install, and WordPress setup
4. Access the site at http://localhost:8080

### Command Line

```bash
# Copy the default .env if you don't have one
cp .devcontainer/config/app/.env.example .devcontainer/.env

# Start the dev container (builds image, runs install.sh and setup.sh)
devcontainer up --workspace-folder .

# Open a shell inside the container
devcontainer exec --workspace-folder . bash
```

## What Happens During Setup

The devcontainer runs two scripts automatically:

1. **`install.sh`** — Clones Bedrock into `/roots/app`, installs Composer dependencies, links your local Acorn repo as a path repository, and installs WP-CLI packages
2. **`setup.sh`** — Installs Node.js/Bun via Volta, resets the database, installs WordPress, activates the Sage theme, and builds frontend assets

Your local Acorn checkout is mounted at `/roots/acorn` and added to Bedrock's `composer.json` as a [path repository](https://getcomposer.org/doc/05-repositories.md#path), so changes you make locally are immediately reflected.

## Running Tests

### Unit tests

```bash
cd /roots/acorn
composer test -- --exclude-group=integration
```

This runs the Pest suite without integration tests. This is what the **Main** CI workflow runs (across PHP 8.2–8.5).

> **Note:** `composer test` without the exclude flag runs *all* tests including integration tests, which will fail if the `web` container isn't running.

### Integration tests (requires running services)

The integration tests make HTTP requests to the WordPress site through the `web` (nginx) container. All services must be running:

```bash
cd /roots/acorn
composer run-script test tests/Integration/Routing
```

This is what the **Integration** CI workflow does. If the tests fail with `Could not resolve host: web`, the nginx container isn't running — see [Troubleshooting](#troubleshooting).

### Linting

```bash
cd /roots/acorn
composer run-script lint
```

### Running it all (matching CI)

CI runs two separate jobs. To replicate both locally inside the devcontainer:

```bash
cd /roots/acorn

# Main CI job: lint + unit tests
composer run-script lint
composer test -- --exclude-group=integration

# Integration CI job: routing tests (requires web container)
composer run-script test tests/Integration/Routing
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

## Project Structure

| Path | Description |
|------|-------------|
| `/roots/acorn` | Your local Acorn repository (mounted from host) |
| `/roots/app` | WordPress installation (Bedrock) |
| `/roots/app/public/content/themes/` | WordPress themes |
| `/roots/app/public/content/plugins/` | WordPress plugins |
| `/roots/app/.env` | Application environment config |

## Common Commands

```bash
# WordPress CLI
wp plugin list
wp theme activate sage
wp acorn optimize:clear

# Composer (Acorn)
cd /roots/acorn
composer test -- --exclude-group=integration

# Build Sage theme
cd /roots/app/public/content/themes/sage
bun install
bun run build
bun run dev
```

## Customization

**WordPress Site Title**: Defaults to "Acorn Testing". Override with:
```bash
WP_SITE_TITLE="My Custom Site"
```

**WordPress Repository**: Clones Bedrock by default. To use a different setup:
```bash
REPOSITORY_URL=https://github.com/your/repo.git
```

**Port overrides**: Set in `.devcontainer/.env`:
```bash
FORWARD_WEB_PORT=8081
FORWARD_DB_PORT=3307
FORWARD_MAILPIT_PORT=1026
FORWARD_MAILPIT_DASHBOARD_PORT=8026
```

## Troubleshooting

- **`Could not resolve host: web`** — The nginx container isn't running. From the host, run:
  ```bash
  cd .devcontainer && docker compose up -d web
  ```
- **Port conflicts** — Change ports in `.devcontainer/.env` (e.g., `FORWARD_WEB_PORT=8081`)
- **Stale containers** — Run `.devcontainer/destroy.sh` to tear everything down and start fresh
- **Rebuild container** — In VS Code: `Dev Containers: Rebuild Container`
- **Database issues** — `setup.sh` runs `wp db reset --yes` on every container create, so a rebuild gives you a clean database
