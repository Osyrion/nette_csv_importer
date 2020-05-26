<?php

//declare(strict_types=1);

namespace App\Presenters;

use App\Model;
use App\Model\ImportRepository;
use Nette;
use Nette\Application\UI\Form;
use Nette\Utils\FileSystem;
use Nette\Utils\ArrayHash;
use Nette\Forms\Controls\UploadControl;
use Nette\Http\FileUpload;

class ImportPresenter extends Nette\Application\UI\Presenter
{
	/** @var Model\ImportRepository */
	private $csv_import;


	public function __construct(Model\ImportRepository $csv_import)
	{
		$this->csv_import = $csv_import;
	}

	/********************* component factories *********************/


	/**
	 * Edit form factory.
	 */
	protected function createComponentImportForm(): Form
	{
        $form = new Form;
		$form->addUpload('file', 'Vyberte soubor:')
             //->setRequired(true)
             //->addRule(Form::MIME_TYPE, 'Only CSV you can upload!', 'text/csv')
             ->addRule(Form::MAX_FILE_SIZE, 'Max 5 mB.', 5 * 1024 * 1024);
        $form->addText('username', 'Meno:')
             ->setDefaultValue('uniuser');
        $form->addPassword('password', 'Heslo:')
             ->setRequired('Zadajte prosím heslo!');
        $form->addSubmit('exported', 'EXPORTOVÁNO')
             ->onClick[] = [$this, 'viewExported'];
        $form->addSubmit('export', 'EXPORT')
             ->onClick[] = [$this, 'uploadCsvFormSucceeded'];
        $form->addProtection();
        return $form;
    }

/*
        public function viewExported($form, $values)
        {
            $csv = $values->file;
            echo $csv->getTemporaryFile();
            echo $values->file->name;
            echo "<br>";
            echo $form['file']->value;
            echo "<br>";
            //$this->redirect('this', $usera);

            //$file = fopen('"' . $values->file . '"', "r");
            $filename = $csv->getTemporaryFile();
                if (is_readable($filename)) {
                    echo 'The file is readable';
                } else {
                    echo 'The file is not readable';
                }
            
            echo file_get_contents($filename);
        }
*/

    public function viewExported($form, $values): void
    {
        if ($values->username == ImportRepository::UNIUSER && $values->password == ImportRepository::UNIPASS)
        {
            $this->redirect('output');
        }
        else {
            echo "NOT OK";
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
                        if (array_key_exists(13, $data) || array_key_exists(33, $data) || array_key_exists(11, $data))
                        {
                            $payment_date = $data[13];
                            $amount = $data[33];

                            // CHECKING ENCODING
                            $check_encoding = mb_detect_encoding($data[11], mb_detect_order(), false);

                            if (mb_check_encoding($data, 'utf-8')) {
                                // USE THIS IF CSV IS ALREADY IN UTF-8
                                $comment = $data[11];
                            }
                            else {
                                if($check_encoding == "UTF-8")
                                {
                                    $data[11] = mb_convert_encoding($data[11], 'UTF-8', 'UTF-8');    
                                }

                                // CONVERT TO UTF-8
                                $comment = iconv($check_encoding, 'utf-8//IGNORE', $data[11]);
                            }
                            
                            // CHECKING IF AMOUNT IS NOT 0
                            if ($amount != 0){
                                
                                $this->csv_import->insert([
                                    'payment_date' => $payment_date,
                                    'amount' => $amount,
                                    'comment' => $comment
                                ]);
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
            echo "NOT OK";
        }

        if ($close)
        {
            $this->redirect('Output:output');
        }
           
    }
    
}
