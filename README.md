# dna-poc
Proof of concept for eCommerce Plugin

### Static Analysis

```php
$ vendor/bin/parallel-lint --exclude vendor/composer --exclude vendor/jakub-onderka .
```

```php
$ vendor/bin/phpstan analyse src tests -l 7
```

```php
$ vendor/bin/phpmd src html cleancode, codesize, controversial, design, naming, unusedcode --reportfile phpmd.html
```

```php
$ vendor/bin/phpcs --standard=PSR2 src
```