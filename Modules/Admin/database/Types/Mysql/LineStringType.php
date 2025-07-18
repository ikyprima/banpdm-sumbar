<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class LineStringType extends Type
{
    public const NAME = 'linestring';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'linestring';
    }
}
