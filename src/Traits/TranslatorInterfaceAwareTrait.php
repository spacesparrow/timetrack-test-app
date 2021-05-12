<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorInterfaceAwareTrait
{
    /** @var TranslatorInterface  */
    private TranslatorInterface $translator;

    /**
     * @required
     * @param TranslatorInterface $translator
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * @return TranslatorInterface
     */
    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}