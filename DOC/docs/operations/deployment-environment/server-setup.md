### Environment Setup

#### Ubuntu

To set up a LAMP stack under Ubuntu:

```bash
sudo apt update
sudo apt install apache2 mysql-server php libapache2-mod-php
```
Enable the mod-rewrite module:

```bash
sudo a2enmod rewrite
```

Restart Apache:

```bash
sudo systemctl restart apache2
```

Retrieve the PHP binary to the PATH:

```bash
export PATH=$PATH:/usr/bin/php
```

#### RedHat / Fedora / CentOS

Install MYSQL server, PHP and Apache:

```bash
yum update
yum install httpd php mysql-server php-mysql
```

#### Windows

For Windows, use a WAMP environment such as:

* [XAMPP 7.3](https://www.apachefirends.org/download.html)
* [WAMP Server 3.2+](https://www.wampserver.com/en/)
* [DevServer 17.x](https://www.easyphp.org/easyphp-devserver.php)

Retrieve the PHP executable path:

```bash
where php
```

Add the PHP binary to the PATH:

```bash
SET PATH=%PATH%;C:\wamp64\bin\php\php7.4.26
```

### Virtual Host Configuration

To run eQual on a local web server using `equal.local` as the server name:

1. Create a virtual host that uses `/public` as the DocumentRoot.

Example for Apache2:

```apache
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
2. Map the domain name to an IP address in your local hosts file:
* **Linux:** `/etc/hosts`
* **Windows:** `C:\Windows\System32\drivers\etc\hosts`

Example:

```
127.0.0.1 equal.local
```

3. Test the setup by browsing to:
```
http://equal.local/index.php?get=demo_hello
```
You should see the output: `hello universe`.

---