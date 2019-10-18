# dna-poc
Proof of concept for eCommerce Plugin

### Static Analysis

```php
$ vendor/bin/parallel-lint --exclude vendor/composer --exclude vendor/jakub-onderka .
```

```php
$ vendor/bin/phpstan analyse src tests -l 7
```