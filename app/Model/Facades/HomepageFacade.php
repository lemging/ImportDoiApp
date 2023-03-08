<?php

namespace App\Model\Facades;

use App\Model\Data\Homepage\HomepageData;
use Nette\Localization\Translator;

class HomepageFacade
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    public function prepareHomepageData()
    {
        $homepageData = new HomepageData();
        $homepageData->title = $this->translator->translate('homepage.title');

        return $homepageData;
    }
}