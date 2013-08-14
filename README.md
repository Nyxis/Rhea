Rhea
========

Projet de gestion de tâches pour Extia.

Utilise un moteur de workflow, EasyTask, pour modéliser et utiliser les différentes tâches en mode "plug an play", modélisées chacune dans des bundles.


# Install

```
git clone git@github.com:extia/Rhea.git
cd Rhea/
wget http://getcomposer.org/composer.phar
php composer.phar install --dev

# database and fixtures build
php app/console propel:database:create --connection=default
php app/console propel:build --insert-sql
php app/console propel:fixtures:load
php app/console cache:warmup

# assets compilation
php app/console assetic:dump --force
```
