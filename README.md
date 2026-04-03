# Togethernet Events

Togethernet Events is a web application built with Laravel and Livewire, designed to manage and facilitate events. It leverages a modern PHP and JavaScript ecosystem to provide a dynamic and responsive user experience.

## Organization

Togethernet is a 13-year-old organization founded and run by committed students at Stockholm Science & Innovation School to create fun events for all students. At the beginning of each school year we hold a “Killergame” called QRTag where a quarter of the school’s students participate. Togethernet also organizes a LAN once a semester where 100 students spend the night at the school with games, tournaments and other fun activities.

## Features

Togethernet Events includes:

*   **Event Management**: Core functionality for creating, managing, and displaying events.
*   **User Authentication**: Secure user login, registration, and profile management powered by Laravel Fortify.
*   **Social Authentication**: Integration with social login providers using Laravel Socialite.
*   **Dynamic Frontend**: Interactive and reactive user interfaces built with Livewire v4 and styled with Flux UI v2 and Tailwind CSS v4.
*   **Database**: Utilizes MySQL for robust data storage.

## Technology Stack

*   **PHP**: 8.5
*   **Laravel Framework**: 13.2.0
*   **Livewire**: 4.2.2
*   **Flux UI**: 2.13.1
*   **Tailwind CSS**: 4.2.2
*   **Laravel Fortify**: 1.36.2 (Authentication scaffolding)
*   **Laravel Socialite**: 5.26.0 (OAuth Social Login)
*   **MySQL**: Database engine
*   **Pest PHP**: 4.4.3 (Testing Framework)
*   **Larastan**: 3.9.3 (Static Analysis)
*   **Laravel Pint**: 1.29.0 (Code Style Fixer)

## Installation

To get this project up and running, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone [repository-url]
    cd TogethernetEvents
    ```

2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Copy the environment file:**
    ```bash
    cp .env.example .env
    ```

5.  **Generate an application key:**
    ```bash
    php artisan key:generate
    ```

6.  **Configure your database:**
    Edit the `.env` file with your MySQL database credentials.

7.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

8.  **Build frontend assets:**
    ```bash
    npm run dev
    # or for production
    # npm run build
    ```

9.  **Start the local development server:**
    ```bash
    php artisan serve
    ```

    You can then access the application in your web browser at `http://127.0.0.1:8000`.

## Testing

To run the tests, use Pest:

```bash
php artisan test
```

## License

### GPL-3.0 license
