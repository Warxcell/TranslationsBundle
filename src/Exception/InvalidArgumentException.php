<?php

namespace ObjectBG\TranslationBundle\Exception;

class InvalidArgumentException extends \InvalidArgumentException
{
    public static function missingTranslations($translatableClass)
    {
        return new self('Missing translations association for entity ' . $translatableClass);
    }

    public static function missingRequiredAnnotation($class, $annotation)
    {
        return new self($class . ' is missing required annotation ' . $annotation);
    }
}
