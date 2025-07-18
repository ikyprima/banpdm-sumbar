<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class GeometryCollectionType extends Type
{
    public const NAME = 'geometrycollection';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'geometrycollection';
    }
}
