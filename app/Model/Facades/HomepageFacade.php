<?php

namespace App\Model\Facades;

use App\Model\Data\Homepage\HomepageData;

class HomepageFacade
{
    public function prepareHomepageData()
    {
        $homepageData = new HomepageData();
        $homepageData->title = 'Domovská stránka';

        return $homepageData;
    }
}