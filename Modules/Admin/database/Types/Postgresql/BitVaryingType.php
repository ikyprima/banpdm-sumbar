<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class BitVaryingType extends Type
{
    public const NAME = 'bit varying';
    public const DBTYPE = 'varbit';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $length = empty($field['length']) ? 255 : $field['length'];

        return "varbit({$length})";
    }
}
