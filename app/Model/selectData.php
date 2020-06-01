<?php

//declare(strict_types=1);

namespace App\Model;
use Nette\Database\Context;
use Nette\Database\IConventions;

use Nette;
use Nette\SmartObject;

class selectData
{
        /** @var Nette\Database\Context */
    private $database;

    public $context;

	public function __construct(Nette\Database\Context $database)
    {
        $this->database = $database;
    }

    public function selectTableData($val)
	{
		$csvTable = $context->table('csv_import');
		$csvTable->where('transaction_hash', $val);
		return $csvTable->getRowCount();
	}
}