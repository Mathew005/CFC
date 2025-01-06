# CFC - College Fest Central Backend

## Description

CFC (College Fest Central) is a platform and management system designed for creating, managing, and publishing college events and fests. This backend API handles event and user data, managing both **Organizer** and **Participant** interactions with the platform.

- **Organizer**: Can create, manage, and publish events or programs.
- **Participant**: Can filter, view, and register for events or programs.

This backend is implemented in **PHP**. The backend serves as a crucial part of the system, providing the necessary data and logic for the frontend to function properly.

This backend is specifically designed to work as the backend for the frontend application available [here](https://github.com/Mathew005/event-platform).

## Requirements

- PHP 7.4 or higher
- XAMPP or any PHP-compatible server

## Installation and Setup

1. **Clone the Repository**:
    ```bash
    git clone https://github.com/Mathew005/cfc
    ```

2. **Move to XAMPP Folder**:
    To make the PHP backend work on a local server, clone the repo into the `htdocs` folder of your XAMPP installation.

    ```bash
    mv cfc {xampp-folder}/htdocs/cfc
    ```

3. **Start the Server**:
    - Open XAMPP and start Apache and MySQL servers.
    - Access the backend by navigating to [http://localhost/cfc](http://localhost/cfc) in your browser.

4. **Configure Database**:
    - Set up the database with the provided SQL schema. The schema is located in the `db` folder of the backend repo.
    - Ensure the `.env` or configuration file is properly set up with the correct database credentials.

## Features

- **Event Management**: Organizers can create and manage events or programs.
- **User Management**: Supports user registration and login for both organizers and participants.
- **Registration Analytics**: Data on event registrations.
- **PDF & XLSX Export**: Organizers can export registration analytics in multiple formats.

### Technologies Used

- **PHP**
- **MySQL** (for database management)

## Author

- **Mathew005**
