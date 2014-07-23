# PHP Standar

## Installation

# Agregar al composer.json
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

Usar el comando composer update

# Agregar al AppKernel

```new MwsStandar\MWSStandarBundle(),

# Instalacion de Code Standar

En la consola de Symfony correr el comando 

```MWS:Standar

# Listo!
