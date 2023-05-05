<?php

namespace App\Model\Facades;

use App\Model\Data\Homepage\HomepageData;
use App\Presenters\HomepagePresenter;
use Nette\Localization\Translator;

class HomepageFacade
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    public function prepareHomepageData(): HomepageData
    {
        $homepageData = new HomepageData();
        $homepageData->title = $this->translator->translate('homepage.title');
        $homepageData->navbarActiveIndex = 0;

        return $homepageData;
    }
}
