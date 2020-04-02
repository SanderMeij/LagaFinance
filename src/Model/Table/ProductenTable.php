<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class ProductenTable extends Table
{
    public static function defaultConnectionName(): string
    {
        return 'turf';
    }

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
    }
}