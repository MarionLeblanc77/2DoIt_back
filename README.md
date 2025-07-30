# 2DoIt_back

Welcome to the back-end repository of the 2DoIt project.

- php with Symfony
- mySQL

## DEVELOPMENT

TODO

### FIRST INSTALLATION

TODO

### RUN LOCALLY

Run on <http://localhost:8000/> with the local web server of Symfony:

```bash
symfony server:start
```

#### MODIFICATION OF ENTITIES

After a change in the entities, affecting the database, run:

```bash
(optional, to check the current mappings for valid forward and reverse mappings ) bin/console doctrine:schema:validate
bin/console make:migration
bin/console doctrine:migrations:migrate
```

#### DEBUG (to fix)

Issue with cache access ("...Cannot rename..., var\cache\dev"):

```PowerShell
Remove-Item -Recurse -Force var\cache
```

```bash
bin/console cache:clear --env=dev
bin/console cache:warmup --env=dev
```

Issue with token jwt:

```bash
php bin/console lexik:jwt:generate-keypair
```
