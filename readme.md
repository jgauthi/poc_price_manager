# Poc Price Manager
Small Framework to manage the calculation of a price according to different parameters _(old project name: calcul tarif)_.

## Prerequisite
* [v1.*](https://github.com/jgauthi/poc_price_manager/tree/v1.3): PHP 5.4+
* [v2.*](https://github.com/jgauthi/poc_price_manager/tree/v2.2): PHP 7.4
* **v3** (current version): PHP 8.1+


## Install
`composer install`

Or you can add this poc like a dependency, in this case edit your [composer.json](https://getcomposer.org) (launch `composer update` after edit):
```json
{
  "repositories": [
    { "type": "git", "url": "git@github.com:jgauthi/poc_price_manager.git" }
  ],
  "require": {
    "jgauthi/poc_price_manager": "3.*"
  }
}
```

## Usage
You can test with php internal server and go to url <http://localhost:8000>:

```shell script
php -S localhost:8000 -t public
```


## Documentation
You can look at [folder public](https://github.com/jgauthi/poc_price_manager/tree/master/public).

