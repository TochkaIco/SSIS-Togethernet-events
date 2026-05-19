# Togethernet Events

The central event management and engagement platform for the Togethernet org at Stockholm Science & Innovation School.

Togethernet Events is a Laravel application built with Livewire and Flux UI, designed to manage organizational events, meetings, and games like QR-Tag.

## Quick Start

```bash
# Clone and setup
git clone ssh://git@git.ssis.nu:822/togethernet/Togethernet-Events.git
composer install
cp .env.example .env
vendor/bin/sail up -d

# Initialize app
vendor/bin/sail artisan key:generate
vendor/bin/sail npm install
vendor/bin/sail npm run dev
```

## Documentation

Detailed documentation is available in the `docs` directory:

- **[Feature Overview](docs/FEATURES.md)**: What the application does.
- **[System Architecture](docs/ARCHITECTURE.md)**: Technical stack and architectural decisions.
- **[Development Guide](docs/DEVELOPMENT.md)**: Local setup, testing, and contribution workflows.

## Tech Stack

- **Backend**: Laravel 13, Fortify, Socialite, LDAP
- **Frontend**: Livewire 4, Flux UI, Tailwind CSS 4
- **Database**: MariaDB
- **Testing**: Pest 4 (with Browser testing)
- **Dev Env**: Laravel Sail (Docker)

## Contributing

We use **Pest** for testing and **Laravel Pint** with **Rector** for code style. Before submitting changes, please ensure all tests pass and the code is formatted.

```bash
composer run format
vendor/bin/sail pest
```

---
&copy; 2026 Fedor Romanov and Togethernet. All rights reserved.
