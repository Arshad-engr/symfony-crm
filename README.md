# Symfony App
## Table of Contents
   - [Features](#Features)
   - [Installation](#installation)
   - [Docker](#docker)

## Features
   This is a simple CRM developed in Symfony 7 with follwoings features
   * Login and Registration
   * Roles and Permissions
   * CRUD operation of tasks
   * User with ROLE_USER can view all their tasks
   * User can update status of their own tasks
   * User can view their dashboard analytics 
   * user can generate report of dashboard analytics
   * User can update their profile
   * Detail dashbaord for ADMIN i.e role with ROLE_ADMIN
   * Admin can view list of all users
   * Admin can deactivate any user
   * Admin have the privilege to perform CRUD operation of tasks
   * Admin can assign user to a specific task
   * Admin can update their profile information
   * Admin can generate report of dashboard analytics  

## Installation
  Follow these steps to set up the application local machine
  ### 1. Clone the repository:

   To get started, clone this repository to your local machine:

     
        git clone https://github.com/Arshad-engr/flaskapp.git
        

  ### 2. Run as a dockerize app

  If you have docker installed in your machine and don't to go with manuall installation, follow these steps to containerize your application
   ```
   docker-compose up --build

   ``` 
   This will pull three images 
   * **symfony_crm-symfony** Custom-built image for the Symfony application
   * **mysql** Official MySQL image to handle the database.
   * **phpmyadmin/phpmyadmin** Official phpMyAdmin image for database management.

   From these images, three containers will be created
   * symfony_app, Accessible at [http://localhost:8080](http://localhost:8080)
   * symfony_phpmyadmin, Accessible at [http://localhost:8081](http://localhost:8081)
   * symfony_db, listning on 3306:3306 (phpmyadmin service running on top of it)

  


