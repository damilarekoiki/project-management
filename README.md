## Versions

- PHP 8.3
- Laravel 12

## Setup

Create env file

    cp .env.example .env

Installations.

    composer install

    npm install

Database Migration.

    php artisan migrate

    php artisan db:seed

Start the application.

    composer run dev

## Admin Login

Email

    admin@gmail.com

Password

    password

## Non-admin Login

Email

    nonadmin@gmail.com

Password

    password

## Technologies used

- Laravel for the backend

- Sanctum for authentication

- Vue.js for frontend

- Inertia to handle navigation of pages

- MySQL for database

- PestPHP for test

- Larastan for PHP static code analysis to capture future possible code errors

- ESLint for Javascript static code analysis

- Pint(PHP) and Prettier(Javascript) for consistent code styling

- Husky to handle pre-commits (runs static analysis and code style tools before every commit)


## Architecture

- MVC pattern - I made used of the MVC pattern using `Inertia.js` to controll the flow of page navigation. This was done to speed up implementation and delivery of this task, and also to have just 1 code repository instead of 2. I also have `APIs` set up in api.php to handle REST requests coming from Vue.js. `Sanctum` is best fit for this application as it handles both stateful and stateless(REST) requests authentications.

- Repository pattern - I made use of the repository pattern so as to delegate communication to a database table to just one class.

- Action pattern - I created some invokable controllers to handle only one concern


## Project Structure

- Models - Instances 

- Observers - Project and Task observers to listen to transactions that happen in projects and tasks tables. Registered to the models using the `ObservedBy` attribute

- Policies - Project and Task policies to handle authorization of admin and non-admin access. Registered to the models using the `UsePolicy` attribute

- Repositories - Project and Task repositories to delegate db transactions to these classes

- helper.php - I created this because I needed the auth user globally

- DTOs - I created DTO classes to encapsulate project and task filters data

- Enums - To register constants and prevent duplicating their values

- FormRequests - For requests validation

- Routes -  api.php and web.php

- Controllers - Web controllers to render Inertia components, Api controllers to handle the API requests

- Seeders - To populate the database

- Factories - To create fake data for testing purpose


## Test

Run `composer run dev` then `php artisan test`.