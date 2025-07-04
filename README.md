## Versions

- PHP 8.3
- Laravel 12

## Setup

Create env file.

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

- Laravel for the backend.

- Sanctum for authentication.

- Vue.js and TypeScript for frontend.

- Inertia to handle navigation of pages.

- MySQL for database.

- PestPHP for test.

- Larastan for PHP static code analysis to capture future possible code errors.

- ESLint for Javascript static code analysis.

- Pint(PHP) and Prettier(Javascript) for consistent code styling.

- Husky to handle pre-commits (runs static analysis and code style tools before every commit).


## Architecture

- MVC pattern - I made use of the MVC pattern; using the Eloquent model, contollers, and then for the view, I used `Inertia.js` to render Vue components. I used Inertia so as to speed up the implementation and the delivery of this task, and also to have only 1 code repository instead of 2. I also have `APIs` set up in api.php to handle REST requests coming from the Vue components. `Sanctum` is best fit for the authentication of this application as it handles both stateful and stateless(REST) requests authentications.

- Repository pattern - I made use of the repository pattern so as to delegate transactions on a database table to just one class.

- Action pattern - I created some invokable controllers to handle only one concern.


## Code Project Structure

- Models - Project, Task, and User models.

- Observers - Project and Task observers to listen to transactions that happen in projects and tasks tables. Registered to the models using the `ObservedBy` attribute.

- Policies - Project and Task policies to handle authorization of admin and non-admin access. Registered to the models using the `UsePolicy` attribute.

- Repositories - Project and Task repositories to delegate db transactions to these classes.

- helper.php - I created this because I needed the auth user globally.

- DTOs - I created DTO classes to encapsulate project and task filters data.

- Enums - I created these to register constants and prevent duplicating their values.

- FormRequests - I created these to validate requests.

- Routes -  api.php and web.php.

- Controllers - I created Web controllers to render Inertia components, and Api controllers to handle the API requests.

- Seeders - To populate the database.

- Factories - To create fake data for testing purpose.

- Migration files - To create database tables

## Tasks in a project on the frontend

- Multiple tasks can be created at once.

- During update, only the tasks whose values are changed will be sent to the backend.

- Assignees can also be searched and selected.

- The tasks can be filtered by status or due date.

- The tasks are paginated with infinite scroll.


## Authorization

- Only admin can create, update, and delete a project.

- Only the project creator and assignees can view a project.

- Only admin can create and delete a task.

- The task creator (who is currently an admin) can update all the details of a task.

- An assignee can update only the status of a task.

- An authenticated user can view the dashboard and the metrics.


## Test

Run `composer run dev` then `php artisan test`.