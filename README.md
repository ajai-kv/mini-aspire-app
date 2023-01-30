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

## Technology Stack

<table>
    <thead>
      <tr>
        <th>Technology</th>
        <th>Type</th>
        <th>Version</th>
      </tr>
    </thead>
    <tbody>
        <tr>
            <td>PHP</td>
            <td>Language</td>
            <td><code>8.0.2</code></td>
        </tr>
        <tr>
            <td>Laravel</td>
            <td>Framework</td>
            <td><code>9.19</code></td>
        </tr>
        <tr>
            <td>PostgreSQL</td>
            <td>Database</td>
            <td><code>15.0</code></td>
        </tr>
        <tr>
            <td>Sanctum</td>
            <td>Auth</td>
            <td><code>3.0</code></td>
        </tr>
    </tbody>
  </table>

## Steps to spin up the application

- Before proceeding please ensure that you have PHP and Composer [https://getcomposer.org] installed on your system

### 1. Clone the repository

    git clone https://github.com/ajai-kv/mini-aspire-app.git


### 2. Unpack and Install dependencies

    php composer.phar install

- This will unpack and install all the dependencies mentioned in the composer.json file

### 3. Run the database migrations

    php artisan migrate

- This will run the database migrations

### 4. Generate a seeded admin user for the user table

    php artisan db:seed

- This will run the database migrations

### 5. Rollback the migrations (optional)

    php artisan migrate:rollback

- This command can be used to rollback the migrations if needed

### 6. Spin up the docker via Laravel Sail

    ./vendor/bin/sail up

- Laravel Sail is a simple command line interface which can be used to manage the docker service. 
- This command will spin up the docker containers based on the configuration in docker-compose.yml

### 7. Start the Artisan server

    php artisan serve

- To start the Artisan server, use the following command
- The server will be up and listening to http://localhost:8000

### 8. Import the postman collection

- Import the postman collection shared to start playing around with the APIs

### 9. Run test cases

    .vendor/bin/phpunit

- This command will let the test cases to be executed
