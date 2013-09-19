# Lightning

[![Build Status](https://secure.travis-ci.org/fabian/lightning-web.png?branch=master)](http://travis-ci.org/fabian/lightning-web)

Symfony2 project with a restful API for a school project.

## Installation

First checkout source code and install the required dependencies:

```
git clone git://github.com/fabian/lightning-web.git lightning-web
php composer.phar install --dev
```

Then create the database and the tables:

```
php app/console doctrine:database:create
php app/console doctrine:migrations:migrate
```

## Development

Run the server and open http://localhost:8000/ in your browser:

```
php app/console server:run
```

To execute the unit tests run the following command:

```
vendor/bin/phpunit -c app/
```

Make sure to follow the [PSR-2 coding guidelines](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-2-coding-style-guide.md) - warnings are okay, errors not. You can easily check them on the command line:

```
vendor/bin/phpcs --standard=PSR2 src/
```
