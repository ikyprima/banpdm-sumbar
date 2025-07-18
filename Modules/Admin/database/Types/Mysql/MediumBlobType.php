<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class MediumBlobType extends Type
{
    public const NAME = 'mediumblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'mediumblob';
    }
}
