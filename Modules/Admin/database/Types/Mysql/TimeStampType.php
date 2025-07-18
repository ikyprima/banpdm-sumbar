<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TimeStampType extends Type
{
    public const NAME = 'timestamp';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        if (isset($field['default'])) {
            return 'timestamp';
        }

        return 'timestamp null';
    }
}
