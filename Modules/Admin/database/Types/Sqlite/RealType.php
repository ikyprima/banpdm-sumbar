<?php

namespace Modules\Admin\database\Types\Sqlite;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class RealType extends Type
{
    public const NAME = 'real';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'real';
    }
}
