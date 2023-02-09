<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Common\AdminAccounting\Facade\Exception\CannotImportDocumentException;
use App\Components\Forms\ImportDoiForm\IImportDoiFormControlFactory;
use App\Components\Forms\ImportDoiForm\ImportDoiFormControl;
use App\Enums\DoiState;
use App\Exceptions\DoiDataException;
use App\Model\Builders\DoiDataBuilder;
use App\Model\Entities\DoiCreatorData;
use App\Model\Entities\DoiData;
use App\Model\Entities\DoiTitleData;
use App\Model\Facades\DoiImportFacade;
use InvalidArgumentException;
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\FileUpload;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tracy\Debugger;
use Tracy\ILogger;


final class ImportDoiPresenter extends Presenter
{
    /**
     * @var DoiData[]
     */
    private array $doiDataList = [];

    /**
     * @var DoiDataException[]
     */
    private array $doiDataExceptionList = [];

    public function __construct(
        private IImportDoiFormControlFactory $doiFormControlFactory,
        private DoiImportFacade $doiImportFacade
    )
    {
        parent::__construct();
    }

    public function renderDefault()
    {
        $this->template->doiDataList = $this->doiDataList;
        $this->template->doiDataExceptionList = $this->doiDataExceptionList;
    }

    /**
     * @return ImportDoiFormControl
     */
    public function createComponentUploadXlsxFileForm(): ImportDoiFormControl
    {
        $control = $this->doiFormControlFactory->create(ImportDoiFormControl::IMPORT_FILE_FORMAT_XLSX);

//        /**
//         * @param array $doiDataList
//         * @param array $doiDataExceptionList
//         * @return void
//         */
//        $control->onSuccess[] = function (array $doiDataList, array $doiDataExceptionList): void {
//            $this->doiDataList = $doiDataList;
//            $this->doiDataExceptionList = $doiDataExceptionList;
//
////            $this->redrawControl('doi-data-exception-list');
////            $this->redrawControl('doi-data-list');
////            if (empty($doiDataExceptionList))
////            {
////                dumpe('asasas');
////            }
////            $this->redirect('this', [
////                'doiDataExceptionList' => $doiDataExceptionList,
////                'doiDataList' => $doiDataList
////            ]);
//        };
        //todo

        $control->onImport[] = function(FileUpload $file): void
        {
            try
            {
                $this->doiImportFacade->importDoi(
                    $file,
                    $this->doiDataList,
                    $this->doiDataExceptionList
                );
            }
            catch (InvalidArgumentException)
            {
                $this->flashMessage('Vyber xml soubor.', 'error');
            }
        };

        return $control;
    }


}
