# Assesment

## Motivations

- Use `docker` for portability/reusability and multi platform maintenance
- Use industry standards (Docker, PHP-FPM, Nginx, Postgresql, Symfony, PHPUnit, etc) to comply with industry standards
- Use proper `test` environment for `automated-testing`
- Learn a bit more about swagger :)

### Environment

the main dependency is [Docker](https://www.docker.com/) and [Docker Compose](https://docs.docker.com/compose/)

### Installation / Setup / TL;DR

After `docker` and `docker compose` are installed the following commands can be used;

```sh
# spin up `docker` containers
docker-compose up --build
# run migrations
docker-compose exec -w "/var/www/html/customer-api" php-fpm php bin/console doctrine:migrations:migrate
# run migrations on the `test_db`
docker-compose exec -w "/var/www/html/customer-api" php-fpm php bin/console doctrine:migrations:migrate --env test
# test all the api's
docker-compose exec -w "/var/www/html/customer-api" php-fpm ./vendor/bin/phpunit
# api runs at
[localhost:8080](http://localhost:8080)
# swagger documentation
[localhost:8081](http://localhost:8081)
# peek `test_db` -> customers
docker-compose exec test_db psql -U user -d symfony_test -c "SELECT * FROM customers;"
# peek `db` -> customers`
docker-compose exec db psql -U user -d symfony -c "SELECT * FROM customers;"
```

### Future Improvements

- I added a very basic documentation and swagger implementation -> this should be improved
- I got away from making different Interfaces/Validators for private/business with :

```php
<?php
    private function getBusinessContent(Request $request) {
        $dataString = $request->getContent();
        $originalData = json_decode($dataString, true);

        // Create a new array with 'type' as the first key
        $data = ['type' => 'business'];

        // Copy other elements from the original array to the new array
        foreach ($originalData as $key => $value) {
            // Skip 'type' to avoid overwriting it if it's already set in original data
            if ($key !== 'type') {
                $data[$key] = $value;
            }
        }

        return json_encode($data);
    }
```

(using symfony Entity validator by ensuring type is set before a dependency is validated) -> innovation or cheatcode, whatever your perspective ;)

- Since 2 endpoints were specified I assumed the `Private customer endpoint` will ALWAYS be `private` thus not needing a `type`
- Add more documentation for implementation -> `Curl` or popular `fetch` request examples

## Developer notes to keep all honest

### setup default git config for `symfony cli` usage

the symfony scaffolder wants to make it a git, so to let it set `git config` for the running container so we can scaffold a `symfony` project

```sh
docker-compose exec php-fpm bash
# within container
git config --global user.email "miguel@midgetgiraffe.com"
git config --global user.name "Fuentastic"
```

### symfony cli + scaffold a project

```sh
docker-compose exec php-fpm symfony new --webapp "/var/www/html/customer-api"
rm -rf customer-api/.git # since we already have a repo
```

### composer cli + symfony/uid

```sh
docker-compose exec -w "/var/www/html/customer-api" php-fpm composer require symfony/uid

```

### Migrations

#### create migration

```sh
docker-compose exec -w "/var/www/html/customer-api" php-fpm php bin/console doctrine:migrations:diff
```

#### migrate migration

```sh
docker-compose exec -w "/var/www/html/customer-api" php-fpm php bin/console doctrine:migrations:migrate
```

### doublecheck `postgresql` tables

```sh
docker-compose exec db psql -U user -d symfony -c "\dt"
docker-compose exec db psql -U user -d symfony -c "SELECT * FROM customers;"

# for test
docker-compose exec test_db psql -U user -d symfony_test -c "\dt"
docker-compose exec test_db psql -U user -d symfony_test -c "SELECT * FROM customers;"
```

### run tests

```sh
docker-compose exec -w "/var/www/html/customer-api" php-fpm ./vendor/bin/phpunit
#specific tests
docker-compose exec -w "/var/www/html/customer-api" php-fpm ./vendor/bin/phpunit --filter testFailCreatePrivateCustomer

```

#### check `symfony` statuses for _debug_

```sh
docker-compose exec -w "/var/www/html/customer-api" php-fpm php bin/console debug:router | grep doc
```
