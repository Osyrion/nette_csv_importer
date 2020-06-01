<?php

//declare(strict_types=1);

namespace App\Model;

use Nette;

class ImportRepository
{
	use Nette\SmartObject;

	/** @var Nette\Database\Context */
    private $database;
    
    const 
    UNIPASS = "1234",
    UNIUSER = "uniuser";


	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
	}


	public function findAll(): Nette\Database\Table\Selection
	{
		return $this->database->table('csv_import');
	}


	public function findById(int $id): Nette\Database\Table\ActiveRow
	{
		return $this->findAll()->get($id);
	}


	public function insert(iterable $values): void
	{
		$this->findAll()->insert($values);
	}

	public function findHash($val)
	{
		return $this->database->table('csv_import')->where('transaction_hash = ?', $val)->count('*');
	}

}