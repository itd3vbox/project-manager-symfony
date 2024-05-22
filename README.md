# 🚀 Project Management - Symfony 📊

This Laravel project is dedicated to creating a project management application with a robust backend architecture, developed in the form of an API. The main goal is to be able to connect any kind of Front End App (React, Vue.js), mobile (Android / iOS), and desktop apps (Electron, ...).

### Tasks

- [ ] Export / Import Data
- [ ] Create Fixtures
- [ ] Add image for a project
- [ ] Implement user Sign In
- [ ] Write PHP Tests

### Bundles:

- composer require --dev symfony/maker-bundle
- composer require symfony/orm-pack
- composer require symfony/form
- composer require symfony/serialize
- composer require --dev orm-fixtures
- composer require symfony/password-hasher
- composer require symfony/validator
- composer require --dev symfony/test-pack

### Commands

    symfony server:start
    php bin/console php bin/console doctrine:database:create
    sudo chmod 777 var/app.db
    sudo chown -R zero:zero project-manager-symfony
    sudo chmod -R u+w project-manager-symfony

    
    php bin/console make:migration
    php bin/console php bin/console doctrine:migrations:migrate
    php bin/console make:form UserStoreType
    php bin/console doctrine:fixtures:load

    php bin/console doctrine:database:create --env=test
    php bin/console doctrine:migrations:migrate --env=test
    php bin/console d:f:l --env=test --no-interaction
    php bin/console make:test
    php bin/phpunit

### SQL

    SHOW DATABASES;
    SELECT host, user FROM mysql.user;
    SHOW GRANTS FOR 'alpha'@'%';
    SHOW GRANTS FOR 'superuser'@'%';
    

