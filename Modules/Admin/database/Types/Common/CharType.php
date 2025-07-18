<?php

namespace Modules\Admin\database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class CharType extends Type
{
    public const NAME = 'char';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        $field['length'] = empty($field['length']) ? 1 : $field['length'];

        return "char({$field['length']})";
    }
}
