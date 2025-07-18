<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class IntervalType extends Type
{
    public const NAME = 'interval';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'interval';
    }
}
