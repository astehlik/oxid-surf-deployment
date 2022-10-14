# Oxid eShop Surf deployment

[![Run tests and linting](https://github.com/astehlik/oxid-surf-deployment/actions/workflows/test.yml/badge.svg)](https://github.com/astehlik/oxid-surf-deployment/actions/workflows/test.yml)

This library contains an application that allows the deployment
of [Oxid eShops](<(https://www.oxid-esales.com/)>) with
[TYPO3 Surf](<(https://github.com/TYPO3/Surf)>).

## How to use it

Simply add it as a composer dependency to your Surf project:

```bash
composer require de-swebhosting/oxideshop-surf-deployment
```

Then create a deployment using the `OxidEshop` application:

```php
$application = new De\SWebhosting\OxidSurf\Application\OxidEshop();
$deployment->addApplication($application);

$application->setOption('branch', 'develop');
$application->setOption('repositoryUrl', 'git@myhoster.tld:my/oxid-project-repo.git');
$application->setDeploymentPath('/var/www/my-oxid-shop');

$node = new Node('myhost');
$node->setHostname('user@my-ssh-host');

$application->addNode($node);
```

## Prepare your project

This deployment assumes that your project is based on the
`oxid-esales/oxideshop-project` Composer package as described
[here](<(https://docs.oxid-esales.com/eshop/en/6.2/installation/new-installation/preparing-for-installation.html)>)

This deployment assumes, that you included an override config
with your database and path configurations. Put this at the end of the
`source/config.inc.php` file:

```php
if (file_exists(__DIR__ . '/config.inc.override.php')) {
    include __DIR__ . '/config.inc.override.php';
}
```

## Prepare your environment

On the server you deploy to create a `config.inc.override.php` at
`<deployment_root>/shared/source/config.inc.override.php`
configuring the database connection and the paths of the instance:

```php
$this->dbHost = 'localhost';
$this->dbName = '<db_name>';
$this->dbUser = '<db_user>';
$this->dbPwd = '<db_pass>';
$this->sShopURL = 'https://my-shop-url.tld';
$this->sSSLShopURL  = 'https://my-shop-url.tld';
$this->sShopDir = '/<deployment_root>/releases/current/source/';
$this->sCompileDir = '/<deployment_root>/releases/current/source/tmp/';
```

You also need to create shared directories for files that should be persisted
during deployments:

```bash
mkdir -p <deployment_root>/shared/out/contents
mkdir <deployment_root>/shared/out/downloads
mkdir <deployment_root>/shared/out/pictures
```

## Enjoy

I hope this package works for you.

Feel free to open an issue to report errors or request features.
