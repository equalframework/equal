# PHP formatting rules

This file records reusable PHP formatting and mechanical refactoring rules for eQual package code. Project-specific ownership, branding, and copyright rules belong in the relevant project-level agent guide.

## Scope

1. Apply these rules only within the scope explicitly named in the task.
2. Apply all relevant rules to newly added code.
3. When modifying an existing file, apply these rules only to the code being added or otherwise changed for the task. Do not reformat, reorder, or clean up unrelated existing code in that file unless the task explicitly requests it.
4. Unless a rule says otherwise, preserve behavior and change only syntax, declarations, ordering, and whitespace.

## Automated enforcement

The reusable formatter is exposed by the `core_config_format-php` action. It accepts a package name and a package-relative file or directory path. Check mode is the default; set `write=true` to apply changes.

```bash
./equal.run --do=core_config_format-php --package=sale --path=actions/booking/bankcheck/add-deposit-number.php
./equal.run --do=core_config_format-php --package=sale --path=actions/booking/bankcheck/add-deposit-number.php --write=true
```

Project-specific ownership can be supplied without hard-coding it in the core action:

```bash
./equal.run --do=core_config_format-php --package=sale --path=actions --write=true \
    --copyright='Some Rights Reserved, Example project, 2020-2026'
```

Directory targets are processed recursively. The action rejects paths outside the selected package, skips symbolic links and dependency directories, validates the generated PHP, checks formatter idempotence, and writes changed files atomically.

## All PHP files

### Leading file comment spacing

There must be exactly one empty line after the leading file comment and before the first `use`, `namespace`, or other PHP statement.

```php
<?php
/*
    Project-specific ownership and license text.
*/

use example\Example;
use example1\Example1;
```

### Use statements

Order consecutive `use` statements alphabetically by their fully qualified imported name.

```php
use core\Lang;
use documents\Document;
use equal\email\Email;
use equal\email\EmailAttachment;
```

### Framework constants

Replace legacy uppercase constants beginning with `QN_` by their `EQ_` equivalents.

```php
// Before
throw new Exception('invalid_param', QN_ERROR_INVALID_PARAM);

// After
throw new Exception('invalid_param', EQ_ERROR_INVALID_PARAM);
```

This rule concerns uppercase `QN_*` constants. It does not rename lowercase functions solely because their name begins with `qn_`.

## Action handlers and data providers

The rules in this section apply specifically to PHP controllers under `actions/` and `data/`. Future rules for model classes should be added in a separate sibling section.

### Controller announcement

Use short array destructuring and call the static announcement method.

```php
// Before
list($params, $providers) = announce([

// After
[$params, $providers] = eQual::announce([
```

The same result is required when the legacy code already calls `eQual::announce()` but still uses `list()`.

The formatter accepts both `list($params, $providers)` and `[$params, $providers]` as announcement destructuring input. The canonical output always uses short array destructuring.

### Controller announcement separation

Leave at least one empty line after the closing `]);` of the `eQual::announce()` block and before the provider docblock or subsequent executable code.

```php
[$params, $providers] = eQual::announce([
    // ...
]);

/**
 * @var \equal\php\Context $context
 */
```

### Top-level announcement key order

When present, order these keys as follows:

1. `description`
2. `deprecated`
3. `help`
4. `examples`
5. `extends`
6. `params`
7. `access`
8. `response`
9. `constants`
10. `providers`

Do not add a missing key solely to complete this sequence.

This places descriptive and documentation metadata first, controller inheritance before local parameter declarations, the request and response contract next, and injected dependencies last.

Custom keys that are not defined by the controller schema or handled by `eQual::announce()`, such as `name`, `type`, and `package_name`, are not part of this canonical order. Preserve their relative position unless a task defines a rule for them.

### Top-level announcement alignment

Align the top-level announcement keys as shown:

```php
[
    'description'   => 'Controller description.',
    'params'        => [
        // ...
    ],
    'access'        => [
        // ...
    ],
    'response'      => [
        // ...
    ],
    'constants'     => [],
    'providers'     => ['context']
]
```

The access and response metadata use the same alignment style:

```php
'access'        => [
    'visibility'    => 'protected',
    'groups'        => ['example.default.user']
],
'response'      => [
    'content-type'  => 'application/json',
    'charset'       => 'utf-8',
    'accept-origin' => '*'
],
```

### Multiline metadata strings

Do not embed a source-code line break and its indentation inside a single announcement metadata string literal. When a line break is part of one quoted string, replace that physical line break with an explicit `\n`, split the value into concatenated fragments, and align the continuation `.` with the owning metadata key's `=>` column. This preserves the existing runtime line break without retaining source indentation in the value.

```php
// Incorrect
'help'          => "First line.
                    Second line.",

// Correct
'help'          => "First line.\n"
                    ."Second line.",
```

Do not add `\n` merely because an existing value uses concatenated fragments on separate source lines. Those line breaks format the PHP source and are not part of the runtime string. The following is valid and must remain without added `\n` characters:

```php
'help'          => "First fragment."
                    ."Second fragment."
                    ."Third fragment.",
```

Use the same alignment for every additional concatenated line.

### Parameter names

Parameter names use one space on each side of `=>` and no column padding:

```php
'params'        => [
    'entity' => [
        // ...
    ],
    'center_office_id' => [
        // ...
    ]
],
```

### Parameter descriptor key order

Parameter descriptor metadata is grouped in this order:

1. Data identity: `type`, `usage`, `foreign_object`.
2. Documentation: `description`, `help`.
3. Constraints: `selection`, `domain`, `min`, `max`, `pattern`, `in`.
4. Behavior: `required`, `visible`, `default`.

Within those groups, order known parameter metadata keys as follows:

1. `type`
2. `usage`
3. `foreign_object`
4. `description`
5. `help`
6. `selection`
7. `domain`
8. `min`
9. `max`
10. `pattern`
11. `in`
12. `required`
13. `visible`
14. `default`

Do not add a missing key solely to complete this sequence. Keys not included in this canonical order keep their relative position after the ordered keys unless another rule applies.

Malformed variants such as `require` or a key containing trailing whitespace are not distinct metadata keys and must be corrected to their valid form when the intended key is unambiguous.

### Parameter descriptor alignment

Treat the metadata keys from every parameter descriptor in the same `params` block as one alignment group. Find the longest quoted metadata key in that complete block, place the shared `=>` at the first four-column tab stop strictly after it, and pad every shorter metadata key with spaces to reach that column. Do not use literal tab characters.

In the example below, `'foreign_object'` is the longest quoted metadata key. It receives four spaces before `=>`, while all shorter metadata keys receive enough additional spaces to align their arrows with it:

```php
'params'        => [
    'entity' => [
        'type'              => 'string',
        'description'       => 'name',
        'default'           => 'example\Entity'
    ],
    'center_office_id' => [
        'type'              => 'many2one',
        'description'       => 'Related entity.',
        'foreign_object'    => 'example\RelatedEntity'
    ]
],
```

Another example when `description` is the longest quoted metadata key:

```php
'params'        => [
    'entity' => [
        'type'          => 'string',
        'description'   => 'name',
        'default'       => 'example\Entity'
    ],
    'name' => [
        'type'          => 'string',
        'description'   => 'Name.'
    ]
]
```

In particular, do not leave a metadata arrow attached to its key:

```php
// Incorrect
'foreign_object'=> 'example\RelatedEntity'

// Correct
'foreign_object'    => 'example\RelatedEntity'
```

### Array spacing

Use a single space between `=>` and an opening array bracket. This applies to every parameter name; `view_id` is only an example.

```php
// Incorrect
'view_id' =>  [

// Correct
'view_id' => [
```

Keep empty arrays compact. In particular, an announcement without parameters uses:

```php
'params'        => [],
```

Do not expand an empty array with spaces or blank lines, such as `[    ]` or a multiline empty pair of brackets.

### Provider destructuring

Use associative destructuring from the complete providers array instead of positional `list()` extraction.

```php
// Before
list($context, $orm) = [$providers['context'], $providers['orm']];

// After
['context' => $context, 'orm' => $orm] = $providers;
```

Preserve every provider-to-variable mapping when converting a declaration, including aliases such as `'adapt' => $adapter` or `'report' => $reporter`.

The formatter also converts keyed legacy `list()` destructuring from the complete providers array:

```php
// Before
list('context' => $context, 'auth' => $auth) = $providers;

// After
['context' => $context, 'auth' => $auth] = $providers;
```

Positional legacy extraction remains supported and is converted to the same canonical mapping:

```php
// Before
list($context, $auth) = [$providers['context'], $providers['auth']];

// After
['context' => $context, 'auth' => $auth] = $providers;
```

### Provider type declarations

Every variable extracted from the providers array must have a fully qualified type declaration in the provider docblock.

```php
/**
 * @var \equal\php\Context $context
 */
```

The formatter accepts a single-line provider type declaration as legacy input and expands it to the canonical multiline docblock:

```php
// Before
/** @var \equal\php\Context $context */
['context' => $context] = $providers;

// After
/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;
```

Use the fully qualified type even when the corresponding class is imported.

When the docblock declares multiple provider variables, align all variable names to the same column. Determine the longest fully qualified type, then place the variable column at the first position divisible by four strictly after that type. Pad shorter declarations with spaces; do not use literal tab characters.

```php
/**
 * @var \equal\php\Context                  $context
 * @var \equal\auth\AuthenticationManager   $auth
 * @var \equal\dispatch\Dispatcher          $dispatch
 */
```

### Unused providers

Remove any provider when its mapped variable is not used by the controller body.

Remove all three associated declarations together:

1. The provider name, such as `'orm'`, from the announcement's `providers` array.
2. The provider mapping, such as `'orm' => $orm`, from provider destructuring.
3. The mapped variable's `@var` line from the provider docblock.

This applies to every provider, including aliased mappings such as `'adapt' => $adapter` and `'report' => $reporter`.

Do not remove a provider based only on assumptions about the APIs used elsewhere in the file. First verify that its mapped variable is unused in executable code, excluding its declaration and type annotation.

### HTTP response chain

Put `$context` on its own line and indent every method in the chain by four spaces.

```php
// Before
$context->httpResponse()
        ->status(200)
        ->send();

// After
$context
    ->httpResponse()
    ->status(200)
    ->send();
```

Apply the same chain layout when methods such as `body()` or `header()` are present.
Comments between calls use the same indentation as the calls, including comments
that temporarily disable a call.

```php
$context
    ->httpResponse()
    // ->header('Content-Disposition', 'attachment; filename="document.pdf"')
    ->header('Content-Disposition', 'inline; filename="document.pdf"')
    ->body($output)
    ->send();
```

The formatter accepts both single-line and multiline response chains as input. It formats each `$context->httpResponse()...->send();` statement independently and does not alter object operators nested inside method arguments.
