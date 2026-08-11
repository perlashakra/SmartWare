<?php

namespace App\Services\Translation;

use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleTranslationService implements TranslationService{
    public function translate(string $text, string $sourceLanguage, string $targetLanguage) : string{
        $translator = new GoogleTranslate();
        $translator->setSource($sourceLanguage);
        $translator->setTarget($targetLanguage);

        return $translator->translate($text);
    }

}