<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TimeStampTzType extends Type
{
    public const NAME = 'timestamptz';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'timestamp(0) with time zone';
    }
}
