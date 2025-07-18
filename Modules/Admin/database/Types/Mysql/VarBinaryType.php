<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class VarBinaryType extends Type
{
    public const NAME = 'varbinary';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $field['length'] = empty($field['length']) ? 255 : $field['length'];

        return "varbinary({$field['length']})";
    }
}
