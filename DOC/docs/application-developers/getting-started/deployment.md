# Deployment



The latest build of the eQual Docker image is available at [https://hub.docker.com/repository/docker/equalframework/equal](https://hub.docker.com/repository/docker/equalframework/equal)

Here are the steps for getting the Docker image from Docker Hub and running it.

---

## A. Using Docker service



### 1. Pull Image from Docker Hub

```
docker pull equalframework/equal:latest
```



### 2. Run the downloaded Docker Image 

```
docker run --name equal.local -p 80:8080 -d equalframework/equal:latest 
```



### 3. Access the Instance

```
docker exec -ti equal.local /bin/bash
```



## B. Using Docker Compose



### 1. docker-compose.yml
Create a file named `docker-compose.yml` with the content below:

```yaml
version: '3'
services:
  equal:
    image: equalframework/equal
    restart: always
    ports:
      - 80:80
    volumes:
      - /var/www/html
    environment:
      - VIRTUAL_PORT=80
      - VIRTUAL_HOST=localhost
    links:
      - mysql

  mysql:
    image: mysql:5.7
    restart: always
    ports:
      - 3306:3306
    environment:
      MYSQL_DATABASE: equal
      MYSQL_ROOT_PASSWORD: test

```



### 2. Build and run

```
docker-compose up -d
```

---