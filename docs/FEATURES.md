# Project Features

TogethernetEvents is a comprehensive event management system designed for organization members, featuring a unique gamified experience and administrative tools.

## Core Features

### 1. Event Management
- **Multiple Event Types**: Supports Karaoke, Film Parties, QR-Tag games, and Custom events.
- **Registration System**:
    - Capacity management (seats).
    - Waiting list support with priority for those who entered the queue first.
    - Paid entry support.
- **Event Periods**: Allows splitting karaoke events into specific time intervals, each of which have their own attendants.
- **Public Event List**: A discovery page for upcoming and past events.

### 2. QR-Tag
- **Automatic Cycle Generation**: Generates a loop of hunters and targets.
- **Unique Tokens**: Every participant gets a unique QR code every time they tag someone or get tagged themselves.
- **Scanning System**: Scanning a target's QR code eliminates them and assigns their target to the hunter.
- **Leaderboard**: Real-time display of top taggers.
- **TV View**: A dedicated dashboard for displaying leaderboard and basic game-specific information on the large screens at SSIS.

### 3. Kiosk System
- **Product Management**: Organize articles into categories.
- **Purchase Tracking**: Log purchases made during events (e.g., at the kiosk).
- **Event Integration**: Kiosks are linked to specific events, but can also be imported from previous events.

### 4. Meeting Management
- **Attendance Tracking**: Manage attendees for organizational meetings.
- **Protocol Generation**: Generate and manage meeting protocols.
- **Cloud Backup**: Automated backup of meeting notes to Google Drive.

### 5. Feedback System
- **Anonymous/Authenticated Feedback**: Collect bug reports, feature and qol requests from users.
- **Admin Review**: Moderation and tracking of feedback status.

### 6. User Management & Auth
- **LDAP Integration**: Integration with SSIS Active Directory (e.g., users' names and classes are being pulled from there).
- **OAuth Support**: Sign in with Google (Socialite).
- **Role-Based Access Control (RBAC)**: Fine-grained permissions (Spatie Permission).
- **Two-Factor Authentication (2FA)**: Enhanced security via Laravel Fortify.
- **Impersonation**: Admins can impersonate users for support and debugging.
- **Anonymization**: Support for GDPR-compliant user data anonymization, including automated cleanup for graduated students and inactive accounts.
- **Terms of Service Enforcement**: Mandatory TOS acceptance for all users with automated notifications and enforcement grace periods.

## Administrative Tools
- **Global Logs**: Audit trail for all significant actions across the system.
- **App Configuration**: Dynamic management of system settings within the application.
- **Dashboard**: High-level overview of system activity, with charts nad some quick actions.
- **Pulse Integration**: Real-time application monitoring and health metrics.
