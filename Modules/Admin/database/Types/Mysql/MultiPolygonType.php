<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MultiPolygonType extends Type
{
    public const NAME = 'multipolygon';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'multipolygon';
    }
}
