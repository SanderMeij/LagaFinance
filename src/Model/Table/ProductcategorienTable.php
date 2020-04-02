<?php

namespace App\Model\Table;

use Cake\ORM\Table;

class ProductcategorienTable extends Table
{
    public static function defaultConnectionName(): string
    {
        return 'turf';
    }

    public function initialize(array $config): void
    {
        $this->addBehavior('Timestamp');
    }

    public function getIdFromNotes($notes) {
        $lines = explode(";", $notes);
        foreach ($lines as $line) {
            if (substr($line, 0, 9) == "Turfwaar ") {
                $caption = trim(substr($line, 9, strlen($line) - 9));
                print_r($caption);
                $categorie = $this->find()
                    ->where(['caption' => $caption])
                    ->first();
                if ($categorie) {
                    return $categorie->get('ID');
                }
            }
        }
        return -1;
    }
}