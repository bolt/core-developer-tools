Core Developer Tools
====================

This is a Bolt Bundle designed to be used on Bolt git repositories for core
development **only**.

**No warranties or support provided!**


## Requirements

* Bolt 3.4+
* PHP 7.1+


## Commands

| Command                      | Description                                            |
| ---------------------------- | ------------------------------------------------------ |
| `core:locale-update`         | Update locale file(s) to match keys in PHP & Twig      |
| `core:test-db-schema-update` | Update Sqlite database schema used in functional tests |


## Configuration

Clone the repository to somewhere local on a development machine, and add the
full path to the cloned repositories `src/` directory in your Bolt git clone's
root `composer.json` and run `composer dumpautoload` afterwards.
 
### `composer.json`

```json
    "autoload": {
        "psr-4": {
            "Bolt\\": "src",
            "Bolt\\Extension\\CoreDeveloper\\": "/path/to/core-developer-tools/src"
        },
        "files": [
            "app/deprecated.php"
        ]
    },
```


### `.bolt.yml`

```yaml
extensions:
    - Bolt\Extension\CoreDeveloper\ToolsExtension
```
