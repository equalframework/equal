<?php
/*
    This file is part of the eQual framework <http://www.github.com/equalframework/equal>
    Some Rights Reserved, eQual framework, 2010-2026
    Original author(s): Cedric FRANCOYS
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/

namespace equal\dns;

interface DnsProviderInterface {

    public function ensureRecord(array $record, array $options = []): array;

    public function getRecords(string $zone, string $name, string $type = 'A', array $options = []): array;

    public function supports(string $type): bool;
}
