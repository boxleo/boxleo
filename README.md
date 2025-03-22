# Human Resource Management System (HRM) - Laravel 10 + Vue.js 3

## Overview

This Human Resource Management System (HRM) is a web application built using the Laravel 10 PHP framework on the backend and Vue.js 3 on the frontend. It is designed to streamline and automate various HR processes, making it easier for organizations to manage their human resources effectively.

## Features

- **Employee Management:** Keep track of employee details, performance, and attendance.
- **Leave Management:** Efficiently manage employee leave requests and approvals.
- **Payroll:** Automate payroll calculations and generate payslips.
- **Task Management:** Assign tasks, set deadlines, and monitor progress.
- **Reports:** Generate insightful reports to make informed decisions.

## Prerequisites

- PHP >= 8.2
- Composer
- Node.js and npm
- MySQL or any other compatible database

## Installation

1. Clone the repository:

   ```bash
         git clone https://github.com/boxleo/boxleo.git


Navigate to the project directory:

bash

    cd boxleo
Install PHP dependencies:

bash

     composer install

Install JavaScript dependencies:

bash

    npm install

Copy the .env.example file to .env and configure your database settings.

bash

    cp .env.example .env

Update the .env file with your database credentials.

Generate application key:

bash

    php artisan key:generate

Run database migrations and seed:

bash

      php artisan migrate --seed

Compile assets:

bash

     npm run dev

Serve the application:

bash

    php artisan serve

Visit http://localhost:8000 in your browser to access the HRM system.
# hrm-system
