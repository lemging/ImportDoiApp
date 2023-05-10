<?php

namespace App\Providers;

use App\Exceptions\AccountUnsetException;
use Nette\Localization\Translator;

class AccountProvider
{
    public function __construct(
        private Translator $translator
    )
    {
    }

    /**
     * @throws AccountUnsetException
     */
    public function getLogin(): string
    {
        if (!isset($_ENV['LOGIN']))
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingLogin'));
        }

        return $_ENV['LOGIN'];
    }

    /**
     * @throws AccountUnsetException
     */
    public function getPassword(): string
    {
        if (!isset($_ENV['PASSWORD']))
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingPassword'));
        }

        return $_ENV['PASSWORD'];
    }

    /**
     * @throws AccountUnsetException
     */
    public function getDoiPrefix(): string
    {
        if (!isset($_ENV['DOI_PREFIX']))
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingDoiPrefix'));
        }

        return $_ENV['DOI_PREFIX'];
    }
}
