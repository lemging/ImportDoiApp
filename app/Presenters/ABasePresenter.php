<?php

namespace App\Presenters;

use App\Model\Data\AData;
use Nette\Application\UI\Presenter;

abstract class ABasePresenter extends Presenter
{
    public AData $data;

    public function renderDefault(): void
    {
        $this->data->presenter = ABasePresenter::class;
        $this->template->data = $this->data;

    }
}
