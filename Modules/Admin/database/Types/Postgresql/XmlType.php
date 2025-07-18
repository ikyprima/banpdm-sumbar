<?php

namespace Modules\Admin\database\Types\Postgresql;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class XmlType extends Type
{
    public const NAME = 'xml';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        return 'xml';
    }
}
