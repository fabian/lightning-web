# Lightning

[![Build Status](https://secure.travis-ci.org/fabian/lightning-web.png?branch=master)](http://travis-ci.org/fabian/lightning-web)

Symfony2 project with a restful API for a school project.

## 1) Installation

First checkout source code and install the required dependencies:

```
git clone git://github.com/fabian/lightning-web.git lightning-web
curl -s http://getcomposer.org/installer | php
php composer.phar install
```

Then create the database and the tables:

```
php app/console doctrine:database:create
php app/console doctrine:migrations:migrate
```

## 2) Development

Run the server and open http://localhost:8000/ in your browser:

```
php app/console server:run
```
