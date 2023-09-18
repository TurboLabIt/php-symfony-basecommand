<?php
namespace TurboLabIt\BaseCommand\Service;


class DateMagician
{
    // https://unicode-org.github.io/icu/userguide/format_parse/datetime/#date-field-symbol-table

    // monday 18 September 2023
    const INTL_FORMAT_IT_DATE_COMPLETE  = 'EEEE dd MMMM yyyy';


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
        $txtTimeZoneDefault = date_default_timezone_get();
        $tzDefault = new \DateTimeZone($txtTimeZoneDefault);
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
