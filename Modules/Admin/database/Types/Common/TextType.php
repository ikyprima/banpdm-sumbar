<?php

namespace Modules\Admin\database\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Modules\Admin\database\Types\Type;

class TextType extends Type
{
    public const NAME = 'text';

    public function getSQLDeclaration(array $field, AbstractPlatform $platform): string
    {
        // return 'text';
            if ($platform instanceof \Doctrine\DBAL\Platforms\PostgreSqlPlatform) {
                return 'text';
            } elseif ($platform instanceof \Doctrine\DBAL\Platforms\MySqlPlatform) {
                return 'varchar(255)';
            } else {
                throw new \Exception('Unsupported database platform');
            }
    }
}
