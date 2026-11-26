<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cédric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

[$params, $providers] = eQual::announce([
    'description'   => 'Checks or formats PHP files using the eQual package formatting rules.',
    'help'          => "The target path is relative to the selected package. Directories are processed recursively.\n"
                        ."Check mode never writes files; write mode atomically replaces only files whose formatting changes.",
    'params'        => [
        'package' => [
            'type'          => 'string',
            'description'   => 'Name of the package containing the PHP files.',
            'required'      => true
        ],
        'path' => [
            'type'          => 'string',
            'description'   => 'File or directory path relative to the package root.',
            'default'       => '.'
        ],
        'write' => [
            'type'          => 'boolean',
            'description'   => 'Apply formatting changes instead of reporting them only.',
            'default'       => false
        ],
        'copyright' => [
            'type'          => 'string',
            'description'   => 'Exact optional ownership line for the leading file comment.',
            'default'       => ''
        ]
    ],
    'access'        => [
        'visibility'    => 'protected'
    ],
    'response'      => [
        'content-type'  => 'application/json',
        'charset'       => 'utf-8',
        'accept-origin' => '*'
    ],
    'constants'     => ['EQ_BASEDIR'],
    'providers'     => ['context']
]);

/**
 * @var \equal\php\Context $context
 */
['context' => $context] = $providers;

if(!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $params['package'])) {
    throw new Exception('invalid_package', EQ_ERROR_INVALID_PARAM);
}

if(
    str_contains($params['copyright'], "\n")
    || str_contains($params['copyright'], "\r")
    || str_contains($params['copyright'], '*/')
    || ($params['copyright'] !== '' && !str_starts_with($params['copyright'], 'Some Rights Reserved, '))
) {
    throw new Exception('invalid_copyright', EQ_ERROR_INVALID_PARAM);
}

$package_dir = realpath(EQ_BASEDIR.'/packages/'.$params['package']);
if($package_dir === false || !is_dir($package_dir)) {
    throw new Exception('unknown_package', EQ_ERROR_INVALID_PARAM);
}

$target = realpath($package_dir.'/'.$params['path']);
$package_prefix = rtrim($package_dir, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
if($target === false || ($target !== $package_dir && !str_starts_with($target, $package_prefix))) {
    throw new Exception('invalid_path', EQ_ERROR_INVALID_PARAM);
}

$files = [];
if(is_file($target)) {
    if(strtolower(pathinfo($target, PATHINFO_EXTENSION)) !== 'php') {
        throw new Exception('invalid_php_path', EQ_ERROR_INVALID_PARAM);
    }
    $files[] = $target;
}
elseif(is_dir($target)) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($target, FilesystemIterator::SKIP_DOTS),
            static function(SplFileInfo $file): bool {
                if($file->isLink()) {
                    return false;
                }
                if($file->isDir()) {
                    return !in_array($file->getFilename(), ['.git', 'node_modules', 'vendor'], true);
                }
                return strtolower($file->getExtension()) === 'php';
            }
        )
    );

    foreach($iterator as $file) {
        if($file->isFile()) {
            $files[] = $file->getRealPath();
        }
    }
}

sort($files, SORT_STRING);

$findClosingBracket = static function(string $source, int $open_position): ?int {
    $length = strlen($source);
    $depth = 0;
    $quote = null;
    $line_comment = false;
    $block_comment = false;

    for($position = $open_position; $position < $length; ++$position) {
        $char = $source[$position];
        $next = $position + 1 < $length ? $source[$position + 1] : '';

        if($line_comment) {
            if($char === "\n") {
                $line_comment = false;
            }
            continue;
        }

        if($block_comment) {
            if($char === '*' && $next === '/') {
                $block_comment = false;
                ++$position;
            }
            continue;
        }

        if($quote !== null) {
            if($char === '\\') {
                ++$position;
                continue;
            }
            if($char === $quote) {
                $quote = null;
            }
            continue;
        }

        if($char === '/' && $next === '/') {
            $line_comment = true;
            ++$position;
            continue;
        }
        if($char === '#') {
            $line_comment = true;
            continue;
        }
        if($char === '/' && $next === '*') {
            $block_comment = true;
            ++$position;
            continue;
        }
        if($char === "'" || $char === '"') {
            $quote = $char;
            continue;
        }
        if($char === '[') {
            ++$depth;
            continue;
        }
        if($char === ']') {
            --$depth;
            if($depth === 0) {
                return $position;
            }
        }
    }

    return null;
};

$splitArrayItems = static function(string $source, ?array &$inline_comments = null): array {
    $items = [];
    $start = 0;
    $length = strlen($source);
    $depths = ['[' => 0, '(' => 0, '{' => 0];
    $quote = null;
    $line_comment = false;
    $block_comment = false;
    $trailing_line_comment_start = null;

    for($position = 0; $position < $length; ++$position) {
        $char = $source[$position];
        $next = $position + 1 < $length ? $source[$position + 1] : '';

        if($line_comment) {
            if($char === "\n") {
                $line_comment = false;
            }
            continue;
        }
        if($block_comment) {
            if($char === '*' && $next === '/') {
                $block_comment = false;
                ++$position;
            }
            continue;
        }
        if($quote !== null) {
            if($char === '\\') {
                ++$position;
                continue;
            }
            if($char === $quote) {
                $quote = null;
            }
            continue;
        }
        if($trailing_line_comment_start !== null && !ctype_space($char)) {
            $trailing_line_comment_start = null;
        }
        if($char === '/' && $next === '/') {
            if($inline_comments !== null && array_sum($depths) === 0) {
                $trailing_line_comment_start = $position;
            }
            $line_comment = true;
            ++$position;
            continue;
        }
        if($char === '#') {
            if($inline_comments !== null && array_sum($depths) === 0) {
                $trailing_line_comment_start = $position;
            }
            $line_comment = true;
            continue;
        }
        if($char === '/' && $next === '*') {
            $block_comment = true;
            ++$position;
            continue;
        }
        if($char === "'" || $char === '"') {
            $quote = $char;
            continue;
        }
        if(isset($depths[$char])) {
            ++$depths[$char];
            continue;
        }
        if($char === ']') {
            --$depths['['];
            continue;
        }
        if($char === ')') {
            --$depths['('];
            continue;
        }
        if($char === '}') {
            --$depths['{'];
            continue;
        }
        if($char === ',' && array_sum($depths) === 0) {
            $item = trim(substr($source, $start, $position - $start));
            if($item !== '') {
                $items[] = $item;
            }

            if($inline_comments !== null && $item !== '') {
                $comment_start = $position + 1;
                while(
                    $comment_start < $length
                    && ($source[$comment_start] === ' ' || $source[$comment_start] === "\t")
                ) {
                    ++$comment_start;
                }

                if(
                    ($source[$comment_start] ?? '') === '#'
                    || substr($source, $comment_start, 2) === '//'
                ) {
                    $comment_end = $comment_start;
                    while(
                        $comment_end < $length
                        && $source[$comment_end] !== "\n"
                        && $source[$comment_end] !== "\r"
                    ) {
                        ++$comment_end;
                    }
                    $inline_comments[count($items) - 1] = [
                        'text'      => rtrim(substr($source, $comment_start, $comment_end - $comment_start)),
                        'separator' => true
                    ];

                    if(
                        $comment_end < $length
                        && $source[$comment_end] === "\r"
                        && ($source[$comment_end + 1] ?? '') === "\n"
                    ) {
                        ++$comment_end;
                    }
                    $start = min($length, $comment_end + 1);
                    $position = $comment_end;
                    continue;
                }
            }
            $trailing_line_comment_start = null;
            $start = $position + 1;
        }
    }

    if(
        $inline_comments !== null
        && $trailing_line_comment_start !== null
        && $trailing_line_comment_start >= $start
    ) {
        $item = trim(substr($source, $start, $trailing_line_comment_start - $start));
        if($item !== '') {
            $items[] = $item;
            $inline_comments[count($items) - 1] = [
                'text'      => rtrim(substr($source, $trailing_line_comment_start)),
                'separator' => false
            ];
        }
        return $items;
    }

    $item = trim(substr($source, $start));
    if($item !== '') {
        $items[] = $item;
    }

    return $items;
};

$formatHttpResponseChains = static function(string $source): string {
    $offset = 0;
    while(preg_match(
        '/^([ \t]*)\$context\s*->\s*httpResponse\s*\(\s*\)/m',
        $source,
        $matches,
        PREG_OFFSET_CAPTURE,
        $offset
    )) {
        $statement_start = $matches[0][1];
        $indent = $matches[1][0];
        $expression_start = $statement_start + strlen($indent);
        $length = strlen($source);
        $depths = ['(' => 0, '[' => 0, '{' => 0];
        $quote = null;
        $line_comment = false;
        $block_comment = false;
        $operators = [];
        $statement_end = null;

        for($position = $expression_start; $position < $length; ++$position) {
            $char = $source[$position];
            $next = $position + 1 < $length ? $source[$position + 1] : '';

            if($line_comment) {
                if($char === "\n") {
                    $line_comment = false;
                }
                continue;
            }
            if($block_comment) {
                if($char === '*' && $next === '/') {
                    $block_comment = false;
                    ++$position;
                }
                continue;
            }
            if($quote !== null) {
                if($char === '\\') {
                    ++$position;
                    continue;
                }
                if($char === $quote) {
                    $quote = null;
                }
                continue;
            }
            if($char === '/' && $next === '/') {
                $line_comment = true;
                ++$position;
                continue;
            }
            if($char === '#') {
                $line_comment = true;
                continue;
            }
            if($char === '/' && $next === '*') {
                $block_comment = true;
                ++$position;
                continue;
            }
            if($char === "'" || $char === '"') {
                $quote = $char;
                continue;
            }
            if(isset($depths[$char])) {
                ++$depths[$char];
                continue;
            }
            if($char === ')') {
                --$depths['('];
                continue;
            }
            if($char === ']') {
                --$depths['['];
                continue;
            }
            if($char === '}') {
                --$depths['{'];
                continue;
            }
            if(array_sum($depths) === 0 && $char === '-' && $next === '>') {
                $operators[] = $position;
                ++$position;
                continue;
            }
            if(array_sum($depths) === 0 && $char === ';') {
                $statement_end = $position + 1;
                break;
            }
        }

        if($statement_end === null || !$operators) {
            $offset = $matches[0][1] + strlen($matches[0][0]);
            continue;
        }

        $segments = [substr($source, $expression_start, $operators[0] - $expression_start)];
        foreach($operators as $index => $operator_position) {
            $segment_start = $operator_position + 2;
            $segment_end = $operators[$index + 1] ?? $statement_end;
            $segments[] = substr($source, $segment_start, $segment_end - $segment_start);
        }

        if(
            trim($segments[0]) !== '$context'
            || !preg_match(
                '/^httpResponse\s*\(\s*\)(?:\s*(?:(?:\/\/|#)[^\r\n]*|\/\*.*?\*\/))*\s*$/s',
                trim($segments[1] ?? '')
            )
            || !preg_match('/^send\s*\(.*\)\s*;$/s', trim($segments[count($segments) - 1]))
        ) {
            $offset = $statement_end;
            continue;
        }

        $formatted = $indent.'$context';
        foreach(array_slice($segments, 1) as $segment) {
            $segment = preg_replace(
                '/\n[ \t]*(?=(?:\/\/|#|\/\*))/',
                "\n".$indent.'    ',
                trim($segment)
            );
            $formatted .= "\n".$indent.'    ->'.$segment;
        }

        $source = substr_replace(
            $source,
            $formatted,
            $statement_start,
            $statement_end - $statement_start
        );
        $offset = $statement_start + strlen($formatted);
    }

    return $source;
};

$itemKey = static function(string $item): ?string {
    if(preg_match('/^\s*([\'\"])([^\'\"\r\n]+)\1\s*=>/', $item, $matches)) {
        return $matches[2];
    }
    return null;
};

$reorderKnownItems = static function(array $items, array $order) use($itemKey): array {
    $rank = array_flip($order);
    $known = [];
    foreach($items as $index => $item) {
        $key = $itemKey($item);
        if($key !== null && isset($rank[$key])) {
            $known[] = ['item' => $item, 'index' => $index, 'rank' => $rank[$key]];
        }
    }

    usort(
        $known,
        static fn(array $left, array $right): int => $left['rank'] <=> $right['rank'] ?: $left['index'] <=> $right['index']
    );

    $known_index = 0;
    foreach($items as $index => $item) {
        $key = $itemKey($item);
        if($key !== null && isset($rank[$key])) {
            $items[$index] = $known[$known_index++]['item'];
        }
    }

    return $items;
};

$alignItems = static function(array $items, int $indent, ?int $maximum_key_length = null) use($itemKey): array {
    $detected_maximum_key_length = 0;
    foreach($items as $item) {
        $key = $itemKey($item);
        if($key !== null) {
            $detected_maximum_key_length = max($detected_maximum_key_length, strlen($key) + 2);
        }
    }
    $maximum_key_length = max($maximum_key_length ?? 0, $detected_maximum_key_length);

    $arrow_column = $maximum_key_length + (4 - ($maximum_key_length % 4));
    foreach($items as $index => $item) {
        $item = trim($item);
        if(preg_match('/^([\'\"])([^\'\"\r\n]+)\1\s*=>\s*/', $item, $matches)) {
            $quoted_key = $matches[1].$matches[2].$matches[1];
            $padding = max(1, $arrow_column - strlen($quoted_key));
            $item = $quoted_key.str_repeat(' ', $padding).'=> '.substr($item, strlen($matches[0]));
        }
        $items[$index] = str_repeat(' ', $indent).$item;
    }

    return $items;
};

$formatNestedArray = static function(
    string $item,
    int $item_indent,
    bool $only_with_inline_comments = false
) use($alignItems, $findClosingBracket, $splitArrayItems): string {
    if(!preg_match('/^\s*([\'\"])([^\'\"\r\n]+)\1\s*=>\s*\[/', $item, $matches)) {
        return $item;
    }

    $open_position = strpos($item, '[', strlen($matches[0]) - 1);
    $close_position = $findClosingBracket($item, $open_position);
    if($close_position === null) {
        return $item;
    }

    $inline_comments = [];
    $items = $splitArrayItems(
        substr($item, $open_position + 1, $close_position - $open_position - 1),
        $inline_comments
    );
    if(!$items) {
        return substr($item, 0, $open_position + 1).']'.trim(substr($item, $close_position + 1));
    }
    if($only_with_inline_comments && !$inline_comments) {
        return $item;
    }

    $items = $alignItems($items, $item_indent + 4, 12);
    $maximum_commented_line_length = 0;
    $last_index = count($items) - 1;
    foreach($inline_comments as $index => $inline_comment) {
        $has_separator = $index < $last_index || $inline_comment['separator'];
        $maximum_commented_line_length = max(
            $maximum_commented_line_length,
            strlen($items[$index].($has_separator ? ',' : ''))
        );
    }
    $comment_column = $maximum_commented_line_length > 0
        ? $maximum_commented_line_length + (4 - ($maximum_commented_line_length % 4))
        : 0;

    $formatted_items = [];
    foreach($items as $index => $nested_item) {
        $has_inline_comment = isset($inline_comments[$index]);
        if(
            $index < $last_index
            || ($has_inline_comment && $inline_comments[$index]['separator'])
        ) {
            $nested_item .= ',';
        }
        if($has_inline_comment) {
            $nested_item .= str_repeat(' ', $comment_column - strlen($nested_item))
                .$inline_comments[$index]['text'];
        }
        $formatted_items[] = $nested_item;
    }

    return substr($item, 0, $open_position + 1)
        ."\n".implode("\n", $formatted_items)."\n".str_repeat(' ', $item_indent).']'
        .trim(substr($item, $close_position + 1));
};

$formatParamsItem = static function(string $item) use(
    $alignItems,
    $findClosingBracket,
    $formatNestedArray,
    $itemKey,
    $reorderKnownItems,
    $splitArrayItems
): string {
    if(!preg_match('/^\s*([\'\"])params\1\s*=>\s*\[/', $item, $matches)) {
        return $item;
    }

    $open_position = strpos($item, '[', strlen($matches[0]) - 1);
    $close_position = $findClosingBracket($item, $open_position);
    if($close_position === null) {
        return $item;
    }

    $parameter_items = $splitArrayItems(substr($item, $open_position + 1, $close_position - $open_position - 1));
    if(!$parameter_items) {
        return substr($item, 0, $open_position + 1).']'.trim(substr($item, $close_position + 1));
    }

    $descriptors = [];
    $maximum_key_length = 0;
    $metadata_order = [
        'type',
        'usage',
        'foreign_object',
        'description',
        'help',
        'selection',
        'domain',
        'min',
        'max',
        'pattern',
        'in',
        'required',
        'visible',
        'default'
    ];

    foreach($parameter_items as $parameter_item) {
        $parameter_source = $parameter_item;
        $leading_comment = '';
        if(preg_match(
            '/^\s*((?:(?:\/\*.*?\*\/|\/\/[^\r\n]*|#[^\r\n]*)\s*)+)(?=[\'\"])/s',
            $parameter_item,
            $comment_matches
        )) {
            $leading_comment = trim($comment_matches[1]);
            $parameter_item = substr($parameter_item, strlen($comment_matches[0]));
        }

        if(!preg_match('/^\s*([\'\"])([^\'\"\r\n]+)\1\s*=>\s*\[/', $parameter_item, $parameter_matches)) {
            $descriptors[] = ['raw' => $parameter_source];
            continue;
        }

        $descriptor_open = strpos($parameter_item, '[', strlen($parameter_matches[0]) - 1);
        $descriptor_close = $findClosingBracket($parameter_item, $descriptor_open);
        if($descriptor_close === null) {
            $descriptors[] = ['raw' => $parameter_source];
            continue;
        }

        $metadata_items = $splitArrayItems(
            substr($parameter_item, $descriptor_open + 1, $descriptor_close - $descriptor_open - 1)
        );
        $metadata_comments = [];
        foreach($metadata_items as $metadata_index => $metadata_item) {
            if(preg_match('/((?:\R[ \t]*(?:\/\/|#)[^\r\n]*)+)[ \t]*$/', $metadata_item, $comment_matches, PREG_OFFSET_CAPTURE)) {
                $comment_position = $comment_matches[1][1];
                $metadata_item = rtrim(substr($metadata_item, 0, $comment_position));
                $metadata_comments[] = trim($comment_matches[1][0]);
                $metadata_items[$metadata_index] = $metadata_item;
            }
            if(preg_match('/^\s*([\'\"])([^\'\"\r\n]+)\1\s*=>/', $metadata_item, $metadata_matches)) {
                $metadata_key = trim($metadata_matches[2]);
                if($metadata_key === 'require') {
                    $metadata_key = 'required';
                }
                $metadata_items[$metadata_index] = preg_replace(
                    '/^\s*([\'\"])[^\'\"\r\n]+\1\s*=>/',
                    "'{$metadata_key}' =>",
                    $metadata_item,
                    1
                );
                if($metadata_key === 'selection') {
                    $metadata_items[$metadata_index] = $formatNestedArray(
                        $metadata_items[$metadata_index],
                        12,
                        true
                    );
                }
                $maximum_key_length = max($maximum_key_length, strlen($metadata_key) + 2);
            }
        }

        $metadata_items = $reorderKnownItems($metadata_items, $metadata_order);
        $descriptors[] = [
            'name'              => $parameter_matches[2],
            'items'             => $metadata_items,
            'comments'          => $metadata_comments,
            'leading_comment'   => $leading_comment,
            'suffix'            => trim(substr($parameter_item, $descriptor_close + 1))
        ];
    }

    $formatted_parameters = [];
    foreach($descriptors as $descriptor) {
        if(isset($descriptor['raw'])) {
            $formatted_parameters[] = str_repeat(' ', 8).trim($descriptor['raw']);
            continue;
        }

        $metadata_items = $alignItems($descriptor['items'], 12, $maximum_key_length);
        $metadata = implode(",\n", $metadata_items);
        foreach($descriptor['comments'] as $comment) {
            $metadata .= "\n".preg_replace('/^[ \t]*/m', str_repeat(' ', 12), $comment);
        }
        $formatted_parameter = "        '{$descriptor['name']}' => [\n"
            .$metadata
            ."\n        ]{$descriptor['suffix']}";
        if($descriptor['leading_comment'] !== '') {
            $leading_comment = preg_replace('/^[ \t]*/m', '', $descriptor['leading_comment']);
            if(str_starts_with($leading_comment, '/**')) {
                $leading_comment = preg_replace('/^\*/m', ' *', $leading_comment);
            }
            $formatted_parameter = preg_replace(
                '/^/m',
                str_repeat(' ', 8),
                $leading_comment
            )."\n".$formatted_parameter;
        }
        $formatted_parameters[] = $formatted_parameter;
    }

    return substr($item, 0, $open_position + 1)
        .($formatted_parameters ? "\n".implode(",\n", $formatted_parameters)."\n" : '')
        .'    ]'.trim(substr($item, $close_position + 1));
};

$formatAnnouncement = static function(string $source) use(
    $alignItems,
    $findClosingBracket,
    $formatNestedArray,
    $formatParamsItem,
    $itemKey,
    $reorderKnownItems,
    $splitArrayItems
): string {
    $source = preg_replace(
        '/(?:list\s*\(\s*\$params\s*,\s*\$providers\s*\)|\[\s*\$params\s*,\s*\$providers\s*\])\s*=\s*(?:eQual::)?announce\s*\(\s*\[/',
        '[$params, $providers] = eQual::announce([',
        $source,
        1
    );

    if(!preg_match('/\[\$params, \$providers\] = eQual::announce\(\[/', $source, $matches, PREG_OFFSET_CAPTURE)) {
        return $source;
    }

    $open_position = $matches[0][1] + strlen($matches[0][0]) - 1;
    $close_position = $findClosingBracket($source, $open_position);
    if($close_position === null) {
        return $source;
    }

    $items = $splitArrayItems(substr($source, $open_position + 1, $close_position - $open_position - 1));
    foreach($items as $index => $item) {
        $key = $itemKey($item);
        if($key === 'params') {
            $items[$index] = $formatParamsItem($item);
        }
        elseif(in_array($key, ['access', 'response'], true)) {
            $items[$index] = $formatNestedArray($item, 4);
        }
    }

    $items = $reorderKnownItems($items, [
        'description',
        'deprecated',
        'help',
        'examples',
        'extends',
        'params',
        'access',
        'response',
        'constants',
        'providers'
    ]);
    $items = $alignItems($items, 4);

    $announcement = "\n".implode(",\n", $items)."\n";
    // Rewrite physical line breaks inside a single string literal only; existing concatenated fragments stay unchanged.
    $announcement = preg_replace_callback(
        '/^([ ]*[\'\"][^\'\"\r\n]+[\'\"]\s+=>\s+)"((?:[^"\\\\]|\\\\.)*\R(?:[^"\\\\]|\\\\.)*)"(,?)/m',
        static function(array $matches): string {
            $fragments = preg_split('/\R[ \t]*/', $matches[2]);
            $arrow_column = strpos($matches[1], '=>');
            $result = $matches[1];
            $last = count($fragments) - 1;
            foreach($fragments as $index => $fragment) {
                $fragment = $index === 0 ? rtrim($fragment) : trim($fragment);
                if($index === 0) {
                    $result .= '"'.$fragment;
                }
                else {
                    $result .= str_repeat(' ', $arrow_column).'."'.$fragment;
                }
                if($index < $last) {
                    $result .= '\\n"'."\n";
                }
                else {
                    $result .= '"'.$matches[3];
                }
            }
            return $result;
        },
        $announcement
    );

    $tail = substr($source, $close_position);
    if(preg_match('/^\]\s*\);/', $tail, $closing_matches)) {
        $closing = $closing_matches[0];
        $after = substr($tail, strlen($closing));
        if(trim($after) !== '' && !preg_match('/^(?:[ \t]*\n){2}/', $after)) {
            $after = preg_replace('/^[ \t]*(?:\n[ \t]*)?/', "\n\n", $after, 1);
        }
        $tail = $closing.$after;
    }

    return substr($source, 0, $open_position + 1)
        .$announcement
        .$tail;
};

$formatProviders = static function(string $source, array &$errors): string {
    $source = preg_replace_callback(
        '/list\s*\(\s*((?:[\'\"][^\'\"]+[\'\"]\s*=>\s*\$[A-Za-z_][A-Za-z0-9_]*\s*,?\s*)+)\)\s*=\s*\$providers\s*;/',
        static function(array $matches): string {
            preg_match_all(
                '/[\'\"]([^\'\"]+)[\'\"]\s*=>\s*(\$[A-Za-z_][A-Za-z0-9_]*)/',
                $matches[1],
                $mapping_matches,
                PREG_SET_ORDER
            );
            $mappings = [];
            foreach($mapping_matches as $mapping_match) {
                $mappings[] = "'{$mapping_match[1]}' => {$mapping_match[2]}";
            }
            return '['.implode(', ', $mappings).'] = $providers;';
        },
        $source
    );

    $source = preg_replace_callback(
        '/(?:list\s*\(([^)]*)\)|\[([^]]*)\])\s*=\s*\[\s*((?:\$providers\s*\[\s*[\'\"][^\'\"]+[\'\"]\s*\]\s*,?\s*)+)\]\s*;/',
        static function(array $matches): string {
            $variables_source = $matches[1] !== '' ? $matches[1] : $matches[2];
            preg_match_all('/\$[A-Za-z_][A-Za-z0-9_]*/', $variables_source, $variable_matches);
            preg_match_all('/\$providers\s*\[\s*[\'\"]([^\'\"]+)[\'\"]\s*\]/', $matches[3], $provider_matches);
            if(count($variable_matches[0]) !== count($provider_matches[1])) {
                return $matches[0];
            }
            $mappings = [];
            foreach($variable_matches[0] as $index => $variable) {
                $mappings[] = "'{$provider_matches[1][$index]}' => {$variable}";
            }
            return '['.implode(', ', $mappings).'] = $providers;';
        },
        $source
    );

    $source = preg_replace_callback(
        '/^(\s*)\$([A-Za-z_][A-Za-z0-9_]*)\s*=\s*\$providers\s*\[\s*[\'\"]([^\'\"]+)[\'\"]\s*\]\s*;/m',
        static fn(array $matches): string => "{$matches[1]}['{$matches[3]}' => \${$matches[2]}] = \$providers;",
        $source
    );

    if(!preg_match(
        '/\[((?:\s*[\'\"][^\'\"]+[\'\"]\s*=>\s*\$[A-Za-z_][A-Za-z0-9_]*\s*,?)+)\]\s*=\s*\$providers\s*;/',
        $source,
        $mapping_match,
        PREG_OFFSET_CAPTURE
    )) {
        if(preg_match('/[\'\"]providers[\'\"]\s*=>\s*\[[^]]+\]/', $source)) {
            $errors[] = 'providers_declared_without_supported_mapping';
        }
        return $source;
    }

    preg_match_all(
        '/[\'\"]([^\'\"]+)[\'\"]\s*=>\s*(\$[A-Za-z_][A-Za-z0-9_]*)/',
        $mapping_match[1][0],
        $mapping_pairs,
        PREG_SET_ORDER
    );

    $variable_counts = [];
    foreach(token_get_all($source) as $token) {
        if(is_array($token) && $token[0] === T_VARIABLE) {
            $variable_counts[$token[1]] = ($variable_counts[$token[1]] ?? 0) + 1;
        }
    }

    $kept_mappings = [];
    $removed_providers = [];
    foreach($mapping_pairs as $mapping_pair) {
        if(($variable_counts[$mapping_pair[2]] ?? 0) <= 1) {
            $removed_providers[] = $mapping_pair[1];
            continue;
        }
        $kept_mappings[$mapping_pair[1]] = $mapping_pair[2];
    }

    if($removed_providers) {
        $source = preg_replace_callback(
            '/([\'\"]providers[\'\"]\s*=>\s*)\[([^]]*)\]/',
            static function(array $matches) use($removed_providers): string {
                preg_match_all('/[\'\"]([^\'\"]+)[\'\"]/', $matches[2], $provider_matches);
                $providers = array_values(array_filter(
                    $provider_matches[1],
                    static fn(string $provider): bool => !in_array($provider, $removed_providers, true)
                ));
                return $matches[1].'['.implode(', ', array_map(static fn(string $provider): string => "'{$provider}'", $providers)).']';
            },
            $source,
            1
        );
    }

    preg_match(
        '/\[((?:\s*[\'\"][^\'\"]+[\'\"]\s*=>\s*\$[A-Za-z_][A-Za-z0-9_]*\s*,?)+)\]\s*=\s*\$providers\s*;/',
        $source,
        $current_mapping,
        PREG_OFFSET_CAPTURE
    );
    $mapping_position = $current_mapping[0][1];

    $before_mapping = substr($source, 0, $mapping_position);
    $docblock_position = strrpos($before_mapping, '/**');
    $docblock = null;
    if($docblock_position !== false) {
        $candidate = substr($before_mapping, $docblock_position);
        if(preg_match('/^\/\*\*.*\*\/\s*$/s', $candidate)) {
            $docblock = $candidate;
        }
    }

    if($docblock !== null) {
        $docblock_content = preg_replace('/^\/\*\*|\*\/\s*$/s', '', $docblock);
        $docblock_content = preg_replace('/^\s*\*?\s*@var[^\r\n]*$/m', '', $docblock_content);
        $docblock_content = preg_replace('/[\s*]/', '', $docblock_content);
        if($docblock_content !== '') {
            $errors[] = 'provider_docblock_contains_other_content';
            return $source;
        }
    }

    if(!$kept_mappings) {
        $remove_position = $docblock !== null ? $docblock_position : $mapping_position;
        $remove_length = $mapping_position + strlen($current_mapping[0][0]) - $remove_position;
        return substr_replace($source, '', $remove_position, $remove_length);
    }

    $mapping_replacement = '[';
    $mapping_parts = [];
    foreach($kept_mappings as $provider => $variable) {
        $mapping_parts[] = "'{$provider}' => {$variable}";
    }
    $mapping_replacement .= implode(', ', $mapping_parts).'] = $providers;';
    $source = substr_replace($source, $mapping_replacement, $mapping_position, strlen($current_mapping[0][0]));

    $mapping_position = strpos($source, $mapping_replacement, $mapping_position);

    $existing_types = [];
    if($docblock !== null) {
        preg_match_all('/@var\s+([^\s]+)\s+(\$[A-Za-z_][A-Za-z0-9_]*)/', $docblock, $type_matches, PREG_SET_ORDER);
        foreach($type_matches as $type_match) {
            $existing_types[$type_match[2]] = '\\'.ltrim($type_match[1], '\\');
        }
    }

    $provider_types = [
        'access'    => '\\equal\\access\\AccessController',
        'adapt'     => '\\equal\\data\\adapt\\DataAdapterProvider',
        'auth'      => '\\equal\\auth\\AuthenticationManager',
        'context'   => '\\equal\\php\\Context',
        'cron'      => '\\equal\\cron\\Scheduler',
        'db'        => '\\equal\\db\\DBConnector',
        'dispatch'  => '\\equal\\dispatch\\Dispatcher',
        'log'       => '\\equal\\log\\Logger',
        'orm'       => '\\equal\\orm\\ObjectManager',
        'report'    => '\\equal\\error\\Reporter',
        'route'     => '\\equal\\route\\Router',
        'validate'  => '\\equal\\data\\DataValidator'
    ];

    $declarations = [];
    $maximum_type_length = 0;
    foreach($kept_mappings as $provider => $variable) {
        $type = $provider_types[$provider] ?? ($existing_types[$variable] ?? null);
        if($type === null) {
            $errors[] = "unknown_provider_type:{$provider}";
            continue;
        }
        $declarations[] = ['type' => $type, 'variable' => $variable];
        $maximum_type_length = max($maximum_type_length, strlen($type));
    }

    if(count($declarations) !== count($kept_mappings)) {
        return $source;
    }

    if($declarations) {
        $variable_column = count($declarations) === 1
            ? $maximum_type_length + 1
            : $maximum_type_length + (4 - ($maximum_type_length % 4));
        $lines = ['/**'];
        foreach($declarations as $declaration) {
            $lines[] = ' * @var '.$declaration['type']
                .str_repeat(' ', $variable_column - strlen($declaration['type']))
                .$declaration['variable'];
        }
        $lines[] = ' */';
        $new_docblock = implode("\n", $lines)."\n";
    }
    else {
        $new_docblock = '';
    }

    if($docblock !== null) {
        $source = substr_replace($source, $new_docblock, $docblock_position, strlen($docblock));
    }
    else {
        $source = substr_replace($source, $new_docblock, $mapping_position, 0);
    }

    return $source;
};

$formatSource = static function(string $source, string $copyright, array &$errors) use(
    $formatAnnouncement,
    $formatHttpResponseChains,
    $formatProviders
): string {
    $newline = str_contains($source, "\r\n") ? "\r\n" : "\n";
    $source = str_replace("\r\n", "\n", $source);

    if(preg_match('/\A(<\?php[ \t]*\n)([ \t]*\/\*.*?\*\/)(?:[ \t]*\n)*/s', $source, $header_match)) {
        $comment = $header_match[2];
        if($copyright !== '') {
            if(preg_match('/^[ \t]*Some Rights Reserved,.*$/m', $comment)) {
                $comment = preg_replace_callback(
                    '/^[ \t]*Some Rights Reserved,.*$/m',
                    static fn(array $matches): string => '    '.$copyright,
                    $comment,
                    1
                );
            }
            else {
                $comment = preg_replace_callback(
                    '/\n([ \t]*\*\/)$/',
                    static fn(array $matches): string => "\n    {$copyright}\n{$matches[1]}",
                    $comment,
                    1
                );
            }
        }
        $source = substr_replace(
            $source,
            $header_match[1].$comment."\n\n",
            0,
            strlen($header_match[0])
        );
    }

    $source = preg_replace_callback(
        '/^(?:use\s+[^;\r\n]+;\n)+/m',
        static function(array $matches): string {
            $uses = array_values(array_filter(explode("\n", rtrim($matches[0], "\n"))));
            natcasesort($uses);
            return implode("\n", $uses)."\n";
        },
        $source
    );

    $tokens = token_get_all($source);
    $source = '';
    foreach($tokens as $token) {
        if(is_array($token)) {
            $text = $token[1];
            if($token[0] === T_STRING && str_starts_with($text, 'QN_')) {
                $text = 'EQ_'.substr($text, 3);
            }
            $source .= $text;
        }
        else {
            $source .= $token;
        }
    }

    $source = $formatAnnouncement($source);
    $source = $formatProviders($source, $errors);
    $source = $formatHttpResponseChains($source);

    if($newline === "\r\n") {
        $source = str_replace("\n", "\r\n", $source);
    }

    return $source;
};

$result = [];
$changed = 0;
$errors_count = 0;

foreach($files as $file) {
    $relative_path = $params['package'].'/'.substr($file, strlen($package_prefix));
    $source = file_get_contents($file);
    if($source === false) {
        $result[] = "ERROR - Unable to read {$relative_path}.";
        ++$errors_count;
        continue;
    }

    $format_errors = [];
    $formatted = $formatSource($source, $params['copyright'], $format_errors);
    if(!$format_errors) {
        $idempotence_errors = [];
        if($formatSource($formatted, $params['copyright'], $idempotence_errors) !== $formatted) {
            $format_errors[] = 'non_idempotent_result';
        }
        $format_errors = array_merge($format_errors, $idempotence_errors);
    }

    try {
        token_get_all($formatted, TOKEN_PARSE);
    }
    catch(ParseError $error) {
        $format_errors[] = 'invalid_php_result';
    }

    if($format_errors) {
        foreach(array_unique($format_errors) as $format_error) {
            $result[] = "ERROR - {$relative_path}: {$format_error}.";
            ++$errors_count;
        }
        continue;
    }

    if($formatted === $source) {
        $result[] = "OK - {$relative_path}.";
        continue;
    }

    ++$changed;
    if(!$params['write']) {
        $result[] = "CHANGE - {$relative_path}.";
        continue;
    }

    $temporary_file = tempnam(dirname($file), '.format-php-');
    if($temporary_file === false || file_put_contents($temporary_file, $formatted) === false) {
        if($temporary_file !== false && file_exists($temporary_file)) {
            unlink($temporary_file);
        }
        $result[] = "ERROR - Unable to write {$relative_path}.";
        ++$errors_count;
        continue;
    }

    $file_mode = fileperms($file) & 0777;
    $file_owner = fileowner($file);
    $file_group = filegroup($file);
    $metadata_preserved = chmod($temporary_file, $file_mode);
    if($metadata_preserved && fileowner($temporary_file) !== $file_owner) {
        $metadata_preserved = chown($temporary_file, $file_owner);
    }
    if($metadata_preserved && filegroup($temporary_file) !== $file_group) {
        $metadata_preserved = chgrp($temporary_file, $file_group);
    }
    if(!$metadata_preserved) {
        unlink($temporary_file);
        $result[] = "ERROR - Unable to preserve file metadata for {$relative_path}.";
        ++$errors_count;
        continue;
    }

    if(!rename($temporary_file, $file)) {
        unlink($temporary_file);
        $result[] = "ERROR - Unable to replace {$relative_path}.";
        ++$errors_count;
        continue;
    }

    $result[] = "FORMATTED - {$relative_path}.";
}

if(!$files) {
    $result[] = 'INFO - No PHP files found.';
}

$context
    ->httpResponse()
    ->body([
        'result'  => $result,
        'files'   => count($files),
        'changed' => $changed,
        'errors'  => $errors_count
    ])
    ->send();

if($errors_count > 0 || (!$params['write'] && $changed > 0)) {
    exit(1);
}
