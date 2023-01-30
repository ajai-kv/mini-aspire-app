# Mini-Aspire app

A small independant application which simulates some basic functionalities related to loan management.

There are mainly two kind of actors in the application

- Admin
- Customer

- Customers can be onboarded into the application by registering thrugh the API (auth/register/customer)
- Admin can be onboarded into the application by registering through the API (auth/register/admin)
- Login from both Admin and Customer using the API (auth/login) is needed to use the further features of the application

This application provides the following features,

For a customer,

- Can create new loan applications which will be subject to approval
- Can view their existing loan applications
- Can view each loan application in detail
- Can view the repayment schedules related to a loan
- Can repay their loan with an amount greater than or equal to the due amount

For Admin,

- Can approve / reject a pending loan application
- Can view the different loan applications in the system
- Can view each loan application in detail


This application requires PHP 8.0.2 (or higher) and Laravel 9.19 (or higher)

PostgreSQL is used to represent the database

Laravel Sanctum [https://laravel.com/docs/9.x/sanctum] is used for Authentication


## Steps to spin up the application

- Before proceeding please ensure that you have PHP and Composer [https://getcomposer.org] installed on your system

### 1. Clone the repository

    git clone https://github.com/ajai-kv/mini-aspire-app.git

### 2. Spin up the docker via Laravel Sail

    - Laravel Sail is a simple command line interface which can be used to manage the docker service. 

    ./vendor/bin/sail up

    - This command will spin up the docker containers based on the configuration in docker-compose.yml

### 3. Start the Artisan server

    - To start the Artisan server, use the following command

    php artisan serve

    - The server will be up and listening to http://localhost:8000

### 4. Import the postman collection

    - Import the postman collection shared to start playing around with the APIs

### 4. For running test cases

    .vendor/bin/phpunit

    - This command will let the test cases to be executed
