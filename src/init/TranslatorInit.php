<?php

namespace App\Init;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\XliffFileLoader;

/**
 * Initialise le service de traduction.
 */
class TranslatorInit
{


    /**
     * Initialise le traducteur pour la locale spécifiée.
     *
     * @param string $locale La locale à utiliser pour les traductions (par exemple, 'fr').
     *
     * @return Translator Le traducteur initialisé.
     */
    public function initialize(string $locale): Translator
    {
        $translator = new Translator($locale);
        $translator->addLoader('xlf', new XliffFileLoader());
        $translator->addResource(
            'xlf',
            __DIR__.'/../../vendor/symfony/validator/Resources/translations/validators.'.$locale.'.xlf',
            $locale,
            'validators'
        );
        return $translator;

    }//end initialize()


}//end class
