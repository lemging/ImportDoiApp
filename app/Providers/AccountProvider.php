<?php

namespace App\Providers;

use App\Exceptions\AccountUnsetException;
use Nette\Localization\Translator;

class AccountProvider
{
    private const DEFAULT_VALUE_UNSET = 'unset';

    public function __construct(
        private array $config,
        private Translator $translator
    )
    {
    }

    /**
     * @throws AccountUnsetException
     */
    public function getLogin(): string
    {
        if ($this->config['login'] === self::DEFAULT_VALUE_UNSET)
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingLogin'));
        }

        return $this->config['login'];
    }

    /**
     * @throws AccountUnsetException
     */
    public function getPassword(): string
    {
        if ($this->config['password'] === self::DEFAULT_VALUE_UNSET)
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingPassword'));
        }

        return $this->config['password'];
    }

    /**
     * @throws AccountUnsetException
     */
    public function getDoiPrefix(): string
    {
        if ($this->config['doiPrefix'] === self::DEFAULT_VALUE_UNSET)
        {
            throw new AccountUnsetException($this->translator->translate('account.errorMessages.missingDoiPrefix'));
        }

        return $this->config['doiPrefix'];
    }
}
