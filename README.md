# 🚀 Project Management - Symfony 📊

This Laravel project is dedicated to creating a project management application with a robust backend architecture, developed in the form of an API. The main goal is to be able to connect any kind of Front End App (React, Vue.js), mobile (Android / iOS), and desktop apps (Electron, ...).

### Tasks

- [ ] **Settings Controller**: Create a controller for managing user settings, with capabilities for updating name, email, and password.
- [ ] **Forms**: Develop forms for both creating and updating.
- [ ] **Database**: Implement database functionality for storing and retrieving data.
- [ ] **Tests**: Implement tests for Project, Task & Settings.


### Bundles:

- composer require --dev symfony/maker-bundle
- composer require symfony/orm-pack
- composer require symfony/form
- composer require symfony/serialize
- composer require --dev orm-fixtures
- composer require symfony/password-hasher
- composer require symfony/validator

### Commands

    symfony server:start
    php bin/console doctrine:database:create
    sudo chmod 777 var/app.db
    
    php bin/console make:migration
    php bin/console doctrine:migrations:migrate
    php bin/console make:form UserStoreType
    php bin/console doctrine:fixtures:load
    

