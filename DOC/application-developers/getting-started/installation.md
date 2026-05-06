# Installation

## System Requirements

eQual requires the following dependencies:

- **PHP 7.1+** with the following extensions: `mysqli` (mandatory), `gd`, `opcache`, `zip`, `tidy` (optional)
- **Apache 2+** or **Nginx**
- **MySQL 5+** compatible DBMS (MySQL or MariaDB)

---

## 1. Install

### Downloading eQual

You can download eQual in one of the following ways:

- **Download as ZIP:**
  ```bash
  wget https://github.com/equalframework/equal/archive/master.zip
  ```
- **Clone with Git:**
    ```bash
    git clone https://github.com/equalframework/equal.git
    ```
Copy the files to your web server's HTML directory. For example:

```bash
cp equal /var/www/html/
```

### Option A - Using Docker

#### A.1. Install Docker

##### Linux
[See the official doc on docker.com](https://docs.docker.com/desktop/install/linux-install/).

##### Windows 
[See the official doc on docker.com](https://docs.docker.com/desktop/install/windows-install/).

* install Windows HyperV
* install WSL
* install WSL2 core update  (can be found [here](https://docs.microsoft.com/fr-fr/windows/wsl/install-manual#step-4---download-the-linux-kernel-update-package))
* install [Docker Desktop for windows ](https://docs.docker.com/desktop/install/windows-install/)



#### A.2. Start the container

Download the [Docker Compose file from github](https://raw.githubusercontent.com/equalframework/equal/master/.docker/docker-compose.yml).

```bash
$ wget https://raw.githubusercontent.com/equalframework/equal/master/.docker/docker-compose.yml
```

And instantiate the stack by using the following command :  

```bash
$ docker compose up -d
```



!!! note "Using the Docker image"
    Alternatively, you may create a docker-compose file or a Dockerfile of your own, and use the image from Docker.io. In this case, you can just use this pull command  
    `docker pull equalframework/equal:latest`



Remember to map the default domain name with an IP address in your local hosts file:

* Windows :  `C:\Windows\System32\drivers\etc\hosts`
* Linux : `/etc/hosts`

Example:
```
127.0.0.1 equal.local
```

You can now either browse to the welcome screen : [http://equal.local/apps/](http://equal.local/apps/).

or start a shell on the container : 

```bash
$ docker exec -ti equal.local /bin/bash
```

Some editors use extensions that able you to use a [Docker container](https://docker.com/) as a full-featured development environment. 



!!! note "Using VS Code"
    **"VS Code"** has the extension [Remote - Containers](https://code.visualstudio.com/docs/remote/containers) , where you may work as if everything were running locally on your machine, except now they are inside a container.




### Option B - Manual installation

#### Requirements

eQual requires the following dependencies:

- **PHP 7.1+** with following extensions : `mysqli` (mandatory) + `gd` `opcache` `zip` `tidy` (optional)
- **Apache 2+** or **Nginx**
- **MySQL 5+** compatible DBMS (MySQL or MariaDB)



#### B.1. Environment setup


##### Ubuntu

Installing the LAMP stack under Ubuntu is straightforward:

```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php
```

Make sure that the mod-rewrite module is enabled: 
```bash
sudo a2enmod rewrite
```

Restart Apache: 
```bash
sudo systemctl restart apache2
```
OR
```bash
sudo service apache2 restart
```


Retrieve the path of the PHP binary:

```bash
which php
```
This will output something like `/usr/bin/php`.

Add the PHP binary to the PATH environment variable:
``` bash 
export PATH=$PATH:/usr/bin/php
```

##### RedHat / Fedora / Centos 

Install Mysql server, PHP and Apache:
```bash
yum update
yum install httpd php mysql-server php-mysql
```

##### Windows

Under Windows, you can use any of the following tools for a ready-to-use WAMP environment :

* [XAMPP 7.3](https://www.apachefriends.org/download.html)
* [WAMP Server 3.2+](https://www.wampserver.com/en/) 
* [DevServer 17.x](https://www.easyphp.org/easyphp-devserver.php)

Retrieve the path of the PHP executable:
```bash
where php
```
This will output something like `C:\wamp64\bin\php\php7.4.26\php.exe` 

Add the PHP binary to the PATH environment variable:
```bash
SET PATH=%PATH%;C:\wamp64\bin\php\php7.4.26
```



#### B.2. Getting eQual

- Download code as ZIP: 

  `wget https://github.com/equalframework/equal/archive/master.zip`

**OR**

- Clone with Git :

  `git clone https://github.com/equalframework/equal.git`


Copy the files to your webserver HTML directory.

Example : 

```bash
cp equal /var/www/html/
```



#### B.3. Virtual host configuration

Within the documentation pages, we refer to the installation that runs on a local web server using `equal.local`as servername  (accessible through [http://equal.local](http://equal.local)).

If this is the first time you install eQual, we suggest you use that domain name to make things easier.

In the HTTP server config, create a virtual host that uses `/public` as DocumentRoot.

Example for Apache2:	

```
<VirtualHost *:80>
  ServerName equal.local
  DocumentRoot "c:/wamp64/www/equal/public"
  <Directory "c:/wamp64/www/equal/public/">
    Options +Indexes +Includes +FollowSymLinks +MultiViews
    AllowOverride All
    Require local
  </Directory>
</VirtualHost>
```

Remember to map the domain name with an IP address in your local hosts file:

* Linux : `/etc/hosts`
* Windows :  `C:\Windows\System32\drivers\etc\hosts`


Example:
```
127.0.0.1 equal.local
```

To make sure everything is setup properly, try to request the hello controller by browsing to http://equal.local/index.php?get=demo_hello.

You should get the simple output "hello universe". If not, review carefully the previous steps of the installation.



#### B.4. File permissions

Make sure that the `/var/www/` directory and its content have `www-data:www-data` as owner:

All checks against mandatory folders and file permissions can be done using the following command : 

```bash
$ ./equal.run --do=test_fs-consistency
```

---

## 2. Configure

#### Backend config

eQual expects an optional root config file in the `/config` directory.

To create and customize your config file, start by creating `config.json`:

```bash
$ touch config/config.json
```
To write to your config file, type the following command

```bash
$ vi config.json
```

As an alternative, you can also use one of the example config files, by copying

```bash
$ cp config-example_mysql.json config.json
```

Here is a minimalist `config.json` that you can adapt according to your environment:

```php
{
    "DB_DBMS": "MYSQL",
    "DB_HOST": "127.0.0.1",
    "DB_PORT": "3306",
    "DB_USER": "root",
    "DB_PASSWORD": "test",
    "DB_NAME": "equal",
    "DB_CHARSET": "UTF8"
}
```

If you are under a docker environment, replace the DB_HOST value with `equal_db`.

`ROOT_APP_URL` is particularly important to allow Aps to comunicate with the backend and to avoir CORS errors.

!!! note "DB_CHARSET"  
    If there are issues with the charset, it may be because UTF8 is an alias of utf8mb3 in mysql 8.0 (utf8/utf8mb3 can only store a maximum of three bytes and is deprecated. if you wish to store language characters and symbols, consider using utf8mb4).

!!! note "Front-end config"
    Make sure to set `backend_url` & `rest_api_url` according to your environment (virtual host), to avoid CORS errors.

#### Database initialization

You should now have a properly configured environment and be able to perform some operations calls.

##### Connection DBMS

To make sure the DBMS can be access, you can use the following controller : 
```bash
$ ./equal.run --do=test_db-connectivity
```
Upon success this controller exits with no message (exit 0), and the database is created. If an error occurs, a JSON message is returned with a short description about the issue. Example:
```json
{
    "errors": {
        "INVALID_CONFIG": "Unable to establish connection to DBMS host 
        (wrong hostname or port)"
    }
}
```
##### Creation database

The database can be created by using the `core_init_db controller`.

| **PATH**        | `core\actions\init\db.php`                                                                                                                                                                                                   |
| --------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_db`                                                                                                                                                                                                                |
| **CLI**         | `$ ./equal.run --do=init_db`                                                                                                                                                                                                 |
| **DESCRIPTION** | Creates a database using the details provided in config file. This controllers calls db-connectivity and if connection can be established with the host, it requests the creation of the database, if it does not exist yet. |


#### Package  initialization


In order to be able to manipulate entities, the related package needs to be initialized (each package contains the class definition of its own entities).
This can be done by using the `core_init_package` controller.

| **PATH**        | `core\actions\init\package.php`                                                         |
| --------------- | --------------------------------------------------------------------------------------- |
| **URL**         | `?do=init_package&package=core`                                                         |
| **CLI**         | `$ ./equal.run --do=init_package --package=core --import=true`                          |
| **DESCRIPTION** | Initialize database for given package. If no package is given, initialize core package. |

Now, you should be able to fetch data by using the controllers from the `core` package.



Example: 

| **PATH**        | `core\data\model\collect.php`                                                                 |
| --------------- | --------------------------------------------------------------------------------------------- |
| **URL**         | `?get=model_collect&entity=core\User`                                                         |
| **CLI**         | `$ ./equal.run --get=model_collect --entity=core\\User`                                       |
| **DESCRIPTION** | Returns a list of entities according to given domain (filter), start offset, limit and order. |

## 3. Make first API requests

A list of routes related to default API is defined in `/config/routing/99-default.json`.
Here below are some examples of HTTP calls and their responses (in JSON) that you can us to test your installation:



#### Fetch the details of the root user [1]

**HTTP Request:**

`GET /user/1`

**CLI Equivalent:**

`$ ./equal.run --get=model_read --entity=core\\User --ids=[1]`

**Response:**

```json
[
    {
        "modified": "2023-01-28T00:00:00+00:00",
        "state": "instance",
        "id": 1,
        "name": "root@host.local"
    }
]
```



#### Fetch the full list of existing users

**HTTP Request:**

`GET /users`

**CLI equivalent:**

`$ ./equal.run --get=model_collect --entity=core\\User`

**Response:**

```json
[
    {
        "modified": "2023-01-28T00:00:00+00:00",
        "state": "instance",
        "id": 1,
        "name": "root@host.local"
    },
    {
        "modified": "2023-02-12T12:12:52+00:00",
        "state": "instance",
        "id": 2,
        "name": "cedric@equal.run"
    },
]
```


#### Fetch the full list of existing groups

**Request:**

`GET /groups`

**CLI equivalent:**

`$ ./equal.run --get=model_collect --entity=core\\Group`


**Response:**

```json
[
    {
        "id": 1,
        "name": "root",
        "state": "",
        "modified": "2012-05-30T20:45:20+02:00"
    },
    {
        "id": 2,
        "name": "default",
        "state": "",
        "modified": "2012-05-30T20:45:20+02:00"
    }
]
```



#### Create a new group

**Request:**

```
POST /group
{
    "name": "test"
}
```

Note: The body request may be empty, but it is preferable to specify a name for a group.

**Response:**

```json
{
    "entity": "core\\Group",
    "id": 3
}
```



!!! note "Id field"  
    All entities are identified with an id, which is an auto increment field and is generated automatically.



#### Update the 'name' property of the group[3]

**Request:**

`PUT group/3?fields[name]=test`

**Response:**

```json
[]
```

---

## 4. More

#### CLI autocomplete
eQual comes with a command line autocompletion, made to ease commands typing in CLI.

To enable it, simply run :
```
source autocomplete
```

---