<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Helper;

use function strtr;

final class ComgatePlaceholder
{
    /**
     * Dummy tokeny pouzite pro obejiti encodovani specialnich znaku v Symfony UrlGeneratoru (napr. aby v URL byl znak $ a nikoliv %24 apod.)
     * Tyto tokeny jsou po vygenerovani URL nahrazeny za spravne Comgate placeholdery, ktere platebni brana vyzaduje
     * Platebni brana tyto placeholdery vyzaduje presne i se specialnimi znaky
     */
    public const DUMMY_TRANSACTION_ID = '__COMGATE_PLACEHOLDER_TRANSACTION_ID__';
    public const DUMMY_REFERENCE_ID   = '__COMGATE_PLACEHOLDER_REFERENCE_ID__';

    /**
     * Comgate placeholdery
     */
    private const COMGATE_TRANSACTION_ID_PLACEHOLDER = '${id}';
    private const COMGATE_REFERENCE_ID_PLACEHOLDER = '${refId}';

    /**
     * Nahradi dummy tokeny ve vygenerovane URL za Comgate placeholdery
     */
    public static function replace(string $url): string
    {
        return strtr($url, [
            self::DUMMY_TRANSACTION_ID => self::COMGATE_TRANSACTION_ID_PLACEHOLDER,
            self::DUMMY_REFERENCE_ID   => self::COMGATE_REFERENCE_ID_PLACEHOLDER,
        ]);
    }
}
