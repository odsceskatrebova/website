# Sulu and Doctrine Migrations

## The Problem

Sulu's `sulu:build` (init) process runs `doctrine:schema:update` which creates all entity tables — including application-specific ones like `cookie_consent` and `lead`. However, it **does not** record these in `doctrine_migration_versions`.

This causes a conflict when `doctrine:migrations:migrate` runs afterwards:

1. `sulu:build` creates tables via `schema:update`, wipes `doctrine_migration_versions`
2. `doctrine:migrations:migrate` recreates the `doctrine_migration_versions` table (Doctrine does this automatically)
3. All migrations show as "not executed" — including ones whose tables already exist
4. `CREATE TABLE` without `IF NOT EXISTS` fails on the existing tables

## The Fix

Always use `IF NOT EXISTS` on `CREATE TABLE` statements in migrations:

```php
$this->addSql('CREATE TABLE IF NOT EXISTS my_table (...)');
```

This makes migrations idempotent — they run cleanly regardless of whether `schema:update` already created the tables, get recorded in `doctrine_migration_versions`, and leave the database in a consistent state.

## Rule

Every `CREATE TABLE` in a Doctrine migration in this project must use `IF NOT EXISTS`.
