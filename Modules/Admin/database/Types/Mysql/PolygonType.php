<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class PolygonType extends Type
{
    public const NAME = 'polygon';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'polygon';
    }
}
