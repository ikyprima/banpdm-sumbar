<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class YearType extends Type
{
    public const NAME = 'year';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'year';
    }
}
