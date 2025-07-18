<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TimeStampType extends Type
{
    public const NAME = 'timestamp';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'timestamp(0) without time zone';
    }
}
