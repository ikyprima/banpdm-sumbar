<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MediumTextType extends Type
{
    public const NAME = 'mediumtext';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'mediumtext';
    }
}
