<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TimeTzType extends Type
{
    public const NAME = 'timetz';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'time(0) with time zone';
    }
}
