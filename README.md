Rhea
========

Projet de gestion de tâches pour Extia.

Utilise un moteur de workflow, EasyTask, pour modéliser et utiliser les différentes tâches en mode "plug an play", modélisées chacune dans des bundles.


# Install

```
wget http://getcomposer.org/composer.phar
php composer.phar install --dev
```

Fill *app/config/parameters.yml* with your env configuration (will not be committed).
```
parameters:
    database_driver:   mysql
    database_host:     localhost
    database_port:     ~
    database_name:     easy_task
    database_user:     root
    database_password: ~

    locale:            fr
```

Create database and build it
```
php app/console propel:database:create --connection=default
php app/console propel:build --insert-sql
```


# Moteur de Workflow
