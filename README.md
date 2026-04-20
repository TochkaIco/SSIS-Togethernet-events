# Togethernet
This is the main webside for the Togethernet org.

## Project Overview:
This project as many other Togethernet applications, uses Laravel with Livewire. They have a very nice documentation, go read it :>

## Project Tree:
```text
├── app
│   ├── Actions // An alternative to using middleware, for example, if you want to create a new event
│   ├── Concerns
│   ├── Http // Middleware, Controllers, etc.
│   ├── Livewire // Logic and functions for the livewire components
│   ├── Models // User, Event, etc.
│   └── Providers
├── bootstrap // Define trusted proxies, aliases or middleware
├── config // Connect .env values with your application
├── database
│   ├── factories // Here you define how a certain model can be generated for testing
│   ├── migrations
│   └── seeders // An alternative to using factories
├── lang // Translations
│   └── sv
├── public
│   ├── build
│   └── images // Pre-defined project images
├── resources
│   ├── css // Tailwind and theme setup
│   ├── js // Custom js components
│   └── views // Blade view components
├── routes // Route definitions (e.g. /admin)
├── storage
│   ├── app
│   ├── framework
│   └── logs // Here you can see your debugging logs
└── tests
    ├── Feature
    └── Unit
```

___
## Dev Setup

### Install dependencies
```commandline
$ composer install
$ npm install
```

### Copy the default values from .env.example
```commandline
$ cp .env.example .env
```

### Generate an app key
```commandline
$ php artisan key:generate
```

### Start up a dev server
```commandline
$ vendor/bin/sail up -d
$ vendor/bin/npm run dev
```

___
## Testing

- Pest4 with browser testing (playwright)
```commandline
$ vendor/bin/sail pest
```
- phpstan for static analysis
```commandline
$ vendor/bin/phpstan
```
___
## Code Style & Formatting

- pint
- rector

You can do the formatting by simply running this script:
```commandline
$ composer run format
```

___
### Give user an admin role from terminal
Simply open the running pod in openshift and run the following command with the email of the user you want to give the super-admin role to.
```commandline
$ php artisan app:make-superadmin 12abcd@stockholmscience.se
```
OBS. The user needs to have logged in to the webside before, for the command to work.

___
```
⠀⠀⠀⠀⢀⠠⠤⠀⢀⣿⡀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
⠀⠀⠐⠀⠐⠀⠀⢀⣾⣿⡇⠀⠀⠀⠀⠀⢀⣼⡇⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⠀⣸⣿⣿⣿⠀⠀⠀⠀⣴⣿⣿⠇⠀⠀⠀⠀
⠀⠀⠀⠀⠀⠀⢠⣿⣿⣿⣇⠀⠀⢀⣾⣿⣿⣿⠀⠀⠀⠀⠀
⠀⠀⠀⠀⠀⣴⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⣿⡟⠀⠀⠐⠀⡀
⠀⠀⠀⠀⢰⡿⠉⠀⡜⣿⣿⣿⡿⠿⢿⣿⣿⡃⠀⠀⠂⠄⠀
⠀⠀⠒⠒⠸⣿⣄⡘⣃⣿⣿⡟⢰⠃⠀⢹⣿⡇⠀⠀⠀⠀⠀
⠀⠀⠚⠉⠀⠊⠻⣿⣿⣿⣿⣿⣮⣤⣤⣿⡟⠁⠘⠠⠁⠀⠀
⠀⠀⠀⠀⠀⠠⠀⠀⠈⠙⠛⠛⠛⠛⠛⠁⠀⠒⠤⠀⠀⠀⠀
⠨⠠⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠑⠀⠀⠀⠀⠀⠀
⠁⠃⠉⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀⠀
```
Lastly, remember to use [dd()](https://laravel.com/docs/13.x/helpers#method-dd) :)
