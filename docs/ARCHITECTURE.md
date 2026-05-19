# System Architecture

TogethernetEvents is built on the modern Laravel stack, prioritizing developer productivity and a responsive user experience.

## Core Stack
- **Framework**: Laravel 13
- **Frontend**: Livewire 4 & Flux UI (Blade-based reactivity)
- **Styling**: Tailwind CSS 4
- **Database**: MariaDB
- **Authentication**: Laravel Fortify (Backend) & Spatie Permission (RBAC)

## Architectural Patterns

### 1. Action Pattern
The application uses the **Action** pattern (`app/Actions`) to encapsulate business logic. This keeps controllers and Livewire components thin and makes logic reusable across different backend components.

**Key Actions:**
- `RegisterUserToEvent.php`: Handles seat availability checks, waiting list logic, and priority scoring.
- `ProcessWaitingList.php`: Automatically moves users from the waiting list to active participants when seats become available.
- `ShuffleQrTagTargets.php`: Reshuffles the QR-tag game cycle to ensure a fresh experience.
- `RegisterUserToEvent.php`: Logic for joining an event with period/seat validation.

### 2. Livewire & Flux
The frontend is built primarily with **Livewire**, that has been created specifically for Laravel. **Flux UI** provides a set of high-quality, accessible components that are heavily used across the application.

### 3. Integrated Auth Flow
The system intentionally focuses on Google OAuth to avoid having to deal with extra sensitive information (passwords):
- **LDAP**: Integration with SSIS Active Directory via `ldaprecord-laravel`; users' names and classes are being pulled from there.
- **OAuth**: Google Login support via Laravel Socialite.

### 4. Background Processing
Long-running or periodic tasks are handled via Laravel's Queue system:
- **Google Drive Backup**: Togethernet meeting notes are backed up asynchronously.
- **Discord Logging For QR-Tag**: QR-tag events trigger Discord webhooks via Jobs.

### 5. Priority-Based Registration
The system implements a custom priority scoring logic in `User::registrationPriorityFor(Event $event)` to ensure fair access to limited-seat events (e.g., giving priority to those who were on the waiting list previously).

## Data Model Highlights
- **Events & EventUsers**: A many-to-many relationship with rich pivot data (payment status, attendance, QR-tag tokens).
- **QR-Tag Logic**: Implements a cyclic graph for targets. When a node (user) is removed, the graph is dynamically re-linked to maintain the cycle.
- **AppConfig**: A configuration store to avoid hardcoding environment-specific logic and make it possible for administators to control some of the application logic (e.g. allow creation of new accounts with external email domains).

## Security & Privacy
- **RBAC**: Fine-grained permissions managed via Spatie's Permission package.
- **Data Anonymization**: Built-in support for GDPR compliance, allowing users to anonymize their profile while keeping historical event data consistent.
- **Audit Logs**: A global logging system tracks administrative actions for security reasons and a basic level of transparancy.
