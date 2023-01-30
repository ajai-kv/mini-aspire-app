<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>


# Mini-Aspire app

An small independant application which simulates some basic functionalities related to loan management.

There are mainly two kind of actors in the application

- Admin
- Customer

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

Before proceeding please ensure that you have PHP and Composer [https://getcomposer.org] installed on your system

### 1.
## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.
