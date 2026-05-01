# Docker Setup for CI/CD

Docker is a powerful tool for setting up a **replicable environment** for your project. Below is a specific example to use eQual with Docker using an official PHP image.

## Setup

Go ahead and [install Docker](https://docs.docker.com/get-docker/). On Windows, it comes with **Docker Compose**, which automates the deployment process.

### Dockerfile

The `Dockerfile` is used to build an image with additional requirements. Here's an example using eQual with a PHP-Apache image:

```dockerfile
FROM php:7.3-apache
COPY . /var/www/html
RUN docker-php-ext-install pdo_mysql mysqli \
    && chown -R www-data:www-data /var/www \
    && cp /etc/apache2/mods-available/rewrite.load /etc/apache2/mods-enabled/
WORKDIR /var/www/html
```

### docker-compose.yml

The `docker-compose.yml` file is used to deploy containers. Here's an example:

```yaml
version: '3'
services:
  localhost:
    build:
      context: .
      dockerfile: .docker/Dockerfile
    image: todolist
    ports:
      - 80:80
    links:
      - mysql
  mysql:
    image: mysql:5.7
    environment:
      MYSQL_DATABASE: equal
```

To launch the containers:
```bash
docker-compose up -d
```

## Automating the Process & Database Initialization

To automate the setup process, you can use a script to build the Docker image, deploy the containers, and initialize the database.

Example script:
```bash
docker build --file .docker/Dockerfile -t todolist .
docker-compose up -d
sleep 10
docker exec -ti localhost /bin/bash .docker/init.sh
```

The `init.sh` script inside the container:
```bash
php run.php --do=init_db
php run.php --do=init_package --package=core
```

---