# PHP Standar

PHP Standar
## Installation

```json
{
    "require-dev": {
        "tito/mwsstandar": "dev-master"
    }
}
```

Obligar A Utilizar

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
