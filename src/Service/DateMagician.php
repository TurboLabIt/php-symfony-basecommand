<?php
namespace TurboLabIt\BaseCommand\Service;


class DateMagician
{
    // https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table

    // Monday 18 September 2023
    const INTL_FORMAT_IT_DATE_COMPLETE  = 'EEEE dd MMMM yyyy';

    // 18 September 2023
    const INTL_FORMAT_IT_DATE           = 'dd MMMM y';

    // 18 September
    const INTL_FORMAT_DAY_MONTH         = 'dd MMMM';

    // 18 September 09:30
    const INTL_FORMAT_DAY_MONTH_TIME    = 'dd MMMM HH:mm';

    // September
    const INTL_FORMAT_MONTH             = 'MMMM';

    // 2025-09-04 10:44
    const INTL_FORMAT_YEAR_MONTH_TIME   = 'yyyy-mm-dd hh:mm';


    public static string $locale    = 'it_IT';
    public static string $timeZone  = 'Europe/Rome';


    public function buildDateTimeFromISO8601(?string $dateTime) : ?\DateTime
    {
        // input example: 2023-08-14T15:56:55Z
        // timezone is UTC

        if( empty($dateTime) ) {
            return null;
        }

        // workaround for input without the trailing "Z"
        $dateTime .= str_ends_with($dateTime, 'Z') ? '' : 'Z';

        $tzUTC  = new \DateTimeZone('UTC');
        $oDate  = \DateTime::createFromFormat('Y-m-d\TH:i:sp', $dateTime,  $tzUTC);

        if( empty($oDate) ) {
            return null;
        }

        // timezone conversion: from UTC to local
        $tzDefault = new \DateTimeZone(static::$timeZone);
        $oDate->setTimezone($tzDefault);

        return $oDate;
    }


    public function buildDateFromDDMMYYYY(?string $date) : ?\DateTime
    {
        // input example: 23/01/1982
        if( empty($date) ) {
            return null;
        }

        $oDate = \DateTime::createFromFormat('d/m/Y', $date);

        if( empty($oDate) ) {
            return null;
        }

        $oDate->setTime(0, 0);

        return $oDate;
    }


    public function intlFormat(\DateTime $dateTime, string $icuFormat) : string
    {
        $timezone = $dateTime->getTimeZone()->getName();

        $dateFormatter =
            new \IntlDateFormatter(static::$locale, \IntlDateFormatter::FULL,\IntlDateFormatter::FULL,
                $timezone, \IntlDateFormatter::GREGORIAN, $icuFormat);

        $txtDate = $dateFormatter->format($dateTime);

        return $txtDate;
    }
}
