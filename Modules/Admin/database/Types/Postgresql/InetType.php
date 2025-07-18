<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class InetType extends Type
{
    public const NAME = 'inet';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'inet';
    }
}
