<?php

//declare(strict_types=1);

namespace App\Model;

use Nette;


class ExportTable
{
        /** @var Nette\Database\Context */
    private $database;

	public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function viewTableData($csv_import)
    {
        $tableData = $this->database->table($csv_import)->fetchAll();
        return $tableData;
    }
}