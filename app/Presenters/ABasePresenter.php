<?php

namespace App\Presenters;

use App\Model\Data\AData;
use Nette\Application\UI\Presenter;

abstract class ABasePresenter extends Presenter
{
    public AData $data;

    /**
     * Render základní akce.
     *
     * @return void
     */
    public function renderDefault(): void
    {
        $this->data->presenter = ABasePresenter::class;

        // Nahrajeme data do šablony.
        $this->template->data = $this->data;

    }
}