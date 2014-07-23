# PHP Standar

Requiere tener instalado PEAR consulte la Documentacion

http://pear.php.net/manual/en/installation.php

## Importante Windows

```cli

Requiere Git Bash

http://git-scm.com/downloads

Ejecutar el Scrip GIT_ENV.reg incluido en el Bundle

Crear el siguiente directorio C:\tmp

Continuar con la Instalacion

```

## Instalacion

### Agregar al composer.json
```json
{
    "require-dev": {
        "tito/mwsstandar": "dev-master"
    }
}
```

```json
{
    "scripts": {
        "post-install-cmd": [
            "MwsStandar\\Composer\\Script\\Hooks::checkHooks"
        ],
        "post-update-cmd": [
            "MwsStandar\\Composer\\Script\\Hooks::checkHooks"
         ]
    }
}
```

Execute:

```cli
php composer.phar update "tito/mwsstandar"
```

### Agregar al AppKernel

```php
	// ...
	new MwsStandar\MWSStandarBundle(),
```


### Instalacion de Code Standar

En la consola de Symfony correr el comando 

```cli
app/console MWS:Standar
```



### Listo!
