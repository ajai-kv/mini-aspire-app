# Mini-Aspire app

A small independant application which simulates some basic functionalities related to loan management.

There are mainly two kind of actors in the application

- Admin
- Customer

This application provides the following features,

A customer can,

- Create new loan applications
- View existing loan applications
- View each loan application in detail
- View the repayment schedules related to a loan
- Repay their loan with an amount greater than or equal to the due amount

Admin will be able to,

- Approve / Reject a pending loan application
- View the different loan applications in the system
- View each loan application in detail
- View repayment details of a loan

# Technology Stack

Technology        Type            Version
-------------   | ------------- | ------------- 
PHP             | Language      | 8.0.2 
Laravel         | Framework     | 9.19       
PostgreSQL      | Database      | 15.0  
Sanctum         | Auth          | 3.0  



## Steps to spin up the application

- Before proceeding please ensure that you have PHP and Composer [https://getcomposer.org] installed on your system

### 1. Clone the repository

    git clone https://github.com/ajai-kv/mini-aspire-app.git


### 2. Unpack and Install dependencies

    composer install

- This will unpack and install all the dependencies mentioned in the composer.json file

### 3. Run the database migrations

    php artisan migrate

- This will run the database migrations

### 4. Rollback the migrations (optional)

    php artisan migrate:rollback

- This command can be used to rollback the migrations if needed

### 5. Spin up the docker via Laravel Sail

    ./vendor/bin/sail up

- Laravel Sail is a simple command line interface which can be used to manage the docker service. 
- This command will spin up the docker containers based on the configuration in docker-compose.yml

### 6. Start the Artisan server

    php artisan serve

- To start the Artisan server, use the following command
- The server will be up and listening to http://localhost:8000

### 7. Import the postman collection

- Import the postman collection shared to start playing around with the APIs

### 8. For running test cases

    .vendor/bin/phpunit

- This command will let the test cases to be executed
