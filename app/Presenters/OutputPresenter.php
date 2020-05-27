<?php

//declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use App\Model\ExportTable;
use App\Model\ImportRepository;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\UploadControl;
use Nette\Http\FileUpload;

class OutputPresenter extends Nette\Application\UI\Presenter
{
    
    /** @var \App\Model\ExportTable @inject */
    private $exportTable;

    public function __construct(\App\Model\ExportTable $exportTable)
    {
            $this->exportTable = $exportTable;
    }

    public function beforeRender()
{
        $this->template->csv_import = $this->exportTable->viewTableData('csv_import');
}
}