<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class TurfhistorieTable extends Table
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