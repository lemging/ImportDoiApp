<?php

namespace App\Presenters;

use App\Model\Facades\HomepageFacade;
use Exception;

class HomepagePresenter extends ABasePresenter
{
    public function __construct(
        private HomepageFacade $homepageFacade
    ) {
        parent::__construct();
    }

    /**
     * @throws Exception
     */
    public function actionDefault(): void
    {
        $homepageData = $this->homepageFacade->prepareHomepageData();
        $this->data = $homepageData;
    }
}
