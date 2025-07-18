<?php

namespace Modules\Admin\database\Types\Postgresql;

use Modules\Admin\database\Types\Common\DoubleType;

class DoublePrecisionType extends DoubleType
{
    public const NAME = 'double precision';
    public const DBTYPE = 'float8';
}
