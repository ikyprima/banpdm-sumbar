<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class BitType extends Type
{
    public const NAME = 'bit';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $length = empty($field['length']) ? 1 : $field['length'];
        $length = $length > 64 ? 64 : $length;

        return "bit({$length})";
    }
}
