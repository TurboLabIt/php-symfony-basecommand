<?php
namespace TurboLabIt\BaseCommand\Service;


class Dates
{
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
}
