# Development Guide

This guide provides instructions for setting up the development environment, running tests, and maintaining code quality.

## Project Tree

```text
├── app
│   ├── Actions // An alternative to using middleware, for example, if you want to create a new event
│   ├── Concerns
│   ├── Http // Middleware, Controllers, etc.
│   ├── Livewire // Logic and functions for the livewire components
│   ├── Models // User, Event, etc.
│   └── Providers
├── bootstrap // Define trusted proxies, aliases or middleware
├── config // Connect .env values with your application
├── database
│   ├── factories // Here you define how a certain model can be generated for testing
│   ├── migrations
│   └── seeders // An alternative to using factories
├── lang // Translations
│   └── sv
├── public
│   ├── build
│   └── images // Pre-defined project images
├── resources
│   ├── css // Tailwind and theme setup
│   ├── js // Custom js components
│   └── views // Blade view components
├── routes // Route definitions (e.g. /admin)
├── storage
│   ├── app
│   ├── framework
│   └── logs // Here you can see your debugging logs
└── tests
    ├── Feature
    └── Unit
```

## Dev Setup

### Clone the repository
```bash
git clone ssh://git@git.ssis.nu:822/togethernet/Togethernet-Events.git
cd TogethernetEvents
```

### Install dependencies
```bash
composer install
npm install
```

### Copy the default values from .env.example
```bash
cp .env.example .env
```

### Generate an app key
```bash
php artisan key:generate
```

### Start up a dev server
```bash
vendor/bin/sail up -d
vendor/bin/sail npm run dev
```

## Testing

- **Pest 4** with browser testing (playwright)
```bash
vendor/bin/sail pest
```
- **phpstan** for static analysis
```bash
vendor/bin/phpstan
```

## Code Style & Formatting

- **Pint**
- **Rector**

You can do the formatting by running:
```bash
composer run format
```

## Administrative Access

### Give user an admin role from terminal
Simply open the running pod in OpenShift and run the following command with the email of the user you want to give the super-admin role to:

```bash
php artisan app:make-superadmin 12abcd@stockholmscience.se
```

Note: The user needs to have logged in to the website before the command will work.

### Terms of Service Management

When updating the Terms of Service:
1. Edit the content in `resources/views/terms.md`.
2. Run the reset command to force all users to re-accept:
   ```bash
   php artisan app:reset-tos
   ```
3. The automated system (running via `app:notify-tos-update` daily) will:
   - Send notification emails to all users who haven't accepted the new terms.
   - Automatically anonymize accounts that fail to accept within 30 days of the notification.
   - Intercept users upon login via middleware to require acceptance before app access.

---

Lastly, remember to use [dd()](https://laravel.com/docs/13.x/helpers#method-dd).
