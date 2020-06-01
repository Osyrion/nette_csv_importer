<?php

//declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use App\Model\ImportRepository;
use Nette;
use Nette\Application\UI\Form;
use Nette\Http\FileUpload;

class ImportPresenter extends Nette\Application\UI\Presenter
{
	/** @var Model\ImportRepository */
    private $csv_import;

	public function __construct(Model\ImportRepository $csv_import)
	{
        $this->csv_import = $csv_import;
    }

    
    public function renderDefault()
    {
        $this->template->csv_import = $this->csv_import->findHash($trn_hash); 
    }


	/********************* component factories *********************/


	/**
	 * Edit form factory.
	 */
	protected function createComponentImportForm(): Form
	{
        $form = new Form;
        $form->addUpload('file', 'Vyberte soubor:')
             ->setHtmlAttribute('class', 'custom-file-input')
             ->setHtmlAttribute('accept', '.csv')
             ->addRule(Form::MAX_FILE_SIZE, 'Max 2 mB.', 2 * 1024 * 1024);
        $form->addText('username', 'Meno:')
             ->setDefaultValue('uniuser');
        $form->addPassword('password', 'Heslo:')
             ->setHtmlAttribute('class', 'form-control')
             ->setHtmlAttribute('placeholder', 'Zadejte heslo')
             ->addRule(Form::REQUIRED, 'Zadejte prosím heslo!');
        $form->addSubmit('exported', 'EXPORTOVÁNO')
             ->setHtmlAttribute('id', 'exported')
             ->setHtmlAttribute('class', 'btn btn-success col-sm-6 uploadInput')
             ->onClick[] = [$this, 'viewExported'];
        $form->addSubmit('export', 'EXPORT')
             ->setHtmlAttribute('id', 'export')
             ->setHtmlAttribute('class', 'btn btn-primary col-sm-6 uploadInput')
             ->onClick[] = [$this, 'uploadCsvFormSucceeded'];
        $form->addProtection();
        return $form;
    }

    public function viewExported($form, $values): void
    {
        if ($values->username == ImportRepository::UNIUSER && $values->password == ImportRepository::UNIPASS)
        {
            $this->redirect('Output:output');
        }
        else {
            $this->flashMessage('Zadané heslo není správné!');
        }
    }

    public function uploadCsvFormSucceeded($form, $values)
    {
        
        if ($values->username == ImportRepository::UNIUSER && $values->password == ImportRepository::UNIPASS)
        {

            $file = $values->file;

            if($file->hasFile() && $file->isOk())
            {
                $temp = $file->getTemporaryFile();

                if (is_readable($temp))
                {
                    $csvFile = fopen($temp, 'r');

                    while (($data = fgetcsv($csvFile, 1000, ";")) !== FALSE)
                    {
                        if (array_key_exists(13, $data) || array_key_exists(33, $data) || array_key_exists(11, $data) || array_key_exists(8, $data))
                        {
                            
                            $amount = $data[33];

                            // CHECKING IF AMOUNT IS NOT 0
                            if ($amount != 0){

                                $payment_date = $data[13];
                                $comments = $data[11] . " " . $data[23] . " " . $data[24] . " " . $data[25];

                                // CHECKING ENCODING
                                $check_encoding = mb_detect_encoding($data[11], mb_detect_order(), false);

                                if (mb_check_encoding($data, 'utf-8')) {
                                    // USE THIS IF CSV IS ALREADY IN UTF-8
                                    $comment = $comments;
                                }
                                else {
                                    if($check_encoding == "UTF-8")
                                    {
                                        $comments = mb_convert_encoding($comments, 'UTF-8', 'UTF-8');    
                                    }

                                    // CONVERT TO UTF-8
                                    $comment = iconv($check_encoding, 'utf-8//IGNORE', $comments);
                                }
                            
                                $trn_hash = sha1($data[8]);
                                $check_trn = $this->csv_import->findHash($trn_hash);

                                if ($check_trn == 0) {
                                    $this->csv_import->insert([
                                        'transaction_hash' => $trn_hash,
                                        'payment_date' => $payment_date,
                                        'amount' => $amount,
                                        'comment' => $comment
                                    ]);
                                }
                            }
                        }
                    }

                    // Close opened CSV file
                    fclose($csvFile);   
                    $close = true;
                }
            }

        }
        else {
            $close = false;
            $this->flashMessage('Zadané heslo není správné!');
        }

        if ($close)
        {
            $this->redirect('Output:output');
        }
           
    }
    
}
