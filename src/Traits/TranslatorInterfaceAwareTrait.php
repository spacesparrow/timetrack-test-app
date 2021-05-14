<?php

declare(strict_types=1);

namespace App\Traits;

use Symfony\Contracts\Translation\TranslatorInterface;

trait TranslatorInterfaceAwareTrait
{
    private TranslatorInterface $translator;

    /**
     * @required
     */
    public function setTranslator(TranslatorInterface $translator): void
    {
        $this->translator = $translator;
    }

    protected function getTranslator(): TranslatorInterface
    {
        return $this->translator;
    }
}
