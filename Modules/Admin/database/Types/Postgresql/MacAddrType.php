<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MacAddrType extends Type
{
    public const NAME = 'macaddr';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'macaddr';
    }
}
