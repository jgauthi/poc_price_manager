# Poc Calcul Tarif
Mini Framework pour gérer les calculs de prix, avec promo et remise.

## Prerequisite

* PHP 5.4+ (v1.*) ou PHP 7.4 (v2.*)

## Install
`composer install`

Or you can add this poc like a dependency, in this case edit your [composer.json](https://getcomposer.org) (launch `composer update` after edit):
```json
{
  "repositories": [
    { "type": "git", "url": "git@github.com:jgauthi/poc_calcul_tarif.git" }
  ],
  "require": {
    "jgauthi/poc_calcul_tarif": "1.*"
  }
}
```

## Usage
You can test with php internal server and go to url http://localhost:8000 :

```shell script
php -S localhost:8000 -t public
```


## Documentation
You can look at [folder public](https://github.com/jgauthi/poc_calcul_tarif/tree/master/public).

