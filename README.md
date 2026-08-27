# ExMassTree

Low-code BPM / admin platform (PHP + MySQL). UI and modules are declared in `config/config.json`.

## Requirements

- PHP 7.4+ (8.x recommended) with mysqli, json, curl, mbstring
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Apache (or nginx) with document root pointing to this directory

## Quick start

```bash
composer install
cp config/config.example.php config/config.php
# edit config.php and config/config.json database credentials
mysql -u root < config/schema.sql
```

Default login after schema seed: **admin** / **admin**

Point the vhost at this folder, then open `/?login`.

## Structure

| Path | Purpose |
|------|---------|
| `index.php` | Front controller |
| `config/config.json` | Categories, components, fields, project settings |
| `config/config.php` | Local secrets / DB overrides (not in git) |
| `config/schema.sql` | Auth tables + admin seed |
| `comp/` | Application core, auth, DB driver, field components |
| `templates/` | PHP templates |
| `css/`, `js/` | Assets (includes two-level sidebar shell) |

## Adding a module

1. Describe category / component / elements in `config/config.json`
2. Grant the component name to a group in Admin → Groups
3. Create matching `t_*` tables (or use Admin → Database tools)

## License

Proprietary — DigitalInk / diclofoss.
