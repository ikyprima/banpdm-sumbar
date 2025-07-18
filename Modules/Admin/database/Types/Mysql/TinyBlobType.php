<?php

namespace Modules\Admin\database\Types\Mysql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TinyBlobType extends Type
{
    public const NAME = 'tinyblob';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'tinyblob';
    }
}
