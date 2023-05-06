<?php

namespace App\Presenters;

use App\Components\DoiValidAndInvalidList\DoiValidAndInvalidListControl;
use App\Components\DoiValidAndInvalidList\IDoiValidAndInvalidListControlFactory;
use App\Exceptions\SystemException;
use App\Model\Data\FileStructure\FileStructureData;
use App\Model\Data\ImportDoiConfirmation\ConfirmationData;
use App\Model\Facades\ImportDoiConfirmationFacade;
use Nette\Application\AbortException;
use PhpOffice\PhpSpreadsheet\Exception;

/**
 * Processed data from the uploaded file and errors in the file.
 */
class ImportDoiConfirmationPresenter extends ABasePresenter
{
    public function __construct(
        private ImportDoiConfirmationFacade $importDoiConfirmationFacade,
        private IDoiValidAndInvalidListControlFactory $doiValidAndInvalidListControlFactory
    ) {
        parent::__construct();
    }

    /**
     * Display all valid data from the uploaded file and all errors with the option to upload or re-upload.
     *
     * @param $destination
     * @throws Exception
     * @throws SystemException
     */
    public function actionDefault($destination): void
    {
        $importDoiData = $this->importDoiConfirmationFacade->prepareImportDoiConfirmationData($destination);
        $this->data = $importDoiData;
    }

    /**
     * Sends all valid doi and redirects to the results page.
     *
     * @throws AbortException
     */
    public function handleAddDois(): void
    {
        $messages = $this->importDoiConfirmationFacade->sendDoisDataToApi($this->data->doiDataList);
        $session = $this->getSession()->getSection(ImportDoiResultMessagesPresenter::DOI_SEND_RESPONSE_MESSAGES_SECTION);
        $session->set(ImportDoiResultMessagesPresenter::DOI_SEND_RESPONSE_GENERAL_MESSAGE_AND_MESSAGES, $messages);
        $this->redirect('ImportDoiResultMessages:default');
    }

    public function createComponentDoiValidAndInvalidListControl(): DoiValidAndInvalidListControl
    {
        /** @var ConfirmationData $data */
        $data = $this->data;

        return $this->doiValidAndInvalidListControlFactory->create($data->doiDataList, $data->doiDataErrorDataList);
    }
}
