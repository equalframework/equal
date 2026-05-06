## Examples of Using External Libraries

Here are some examples of how to integrate popular libraries into your eQual project:

### Twig - Template Engine

Install Twig via Composer:

```bash
$ composer require "twig/twig:^2.0"
```

Use it in your code:

```php
<?php
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
```

### PHPOffice - MS Office Compatible Documents Generation Library

Install PHPOffice via Composer:

```bash
$ composer require phpoffice/phpspreadsheet
```

Use it in your code:

```php
<?php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
```

### DOMPDF - HTML to PDF Converter

Install DOMPDF via Composer:

```bash
$ composer require dompdf/dompdf
```

Use it in your code:

```php
<?php
use Dompdf\Dompdf;
use Dompdf\Options;
```