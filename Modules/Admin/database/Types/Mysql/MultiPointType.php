<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MultiPointType extends Type
{
    public const NAME = 'multipoint';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'multipoint';
    }
}
