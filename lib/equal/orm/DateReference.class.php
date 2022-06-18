<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

/**
 *
 *   - soit une chaine ISO
 *   - soit une description au format relatif à NOW
 *
 *   Result of the parsing is always a date.
 *
 *
 *   Syntax:
 *    date.[this|prev|next].[day|week|month|quarter|semester|year].[first|last]
 *
 *
 *   - today = date.this.day
 *   - first day of current year = date.this.year.first
 *   - last day of last week = date.prev.week.last*
 *
 *
 */


class DateReference {

    private $date;

    public function __construct($descriptor) {
        $this->date = time();
        $this->parse($descriptor);
    }

    /**
     *
     * descriptor syntax: date.[this|prev|next].[day|week|month|quarter|semester|year].[first|last]
     * 
     * @param string $descriptor
     */
    public function parse($descriptor) {
        if(is_numeric($descriptor)) {
            $this->date = $descriptor;
        }
        else {
            // init at today
            $date = time();
            $descriptor = strtolower($descriptor);
            if(strpos($descriptor, 'date.') == 0) {
                $parts = explode('.', $descriptor);
                $len = count($parts);
                if($len > 2) {
                    $offset = ($parts[1] == 'prev')? -1 : (($parts[1] == 'next')? 1 : 0);
                    $day = ($len >= 4 && $parts[3] == 'last')?'last':'first';

                    switch($parts[2]) {
                        case 'day':
                            $date += ($offset * 86400);
                            break;
                        case 'week':
                            $dow = date('w', $date);
                            $diff = -$dow + ($dow == 0 ? -6:1);
                            $date += $diff + ($offset * 7);
                            if($day == 'last') {
                                $date += 6 * 86400;
                            }
                            break;
                        case 'month':
                            $date = mktime(0, 0, 0, date('n', $date)+$offset, 1, date('Y', $date));
                            if($day == 'last') {
                                $date = mktime(0, 0, 0, date('n', $date)+$offset+1, 0, date('Y', $date));
                            }
                            break;
                        case 'quarter':
                            break;
                        case 'semester':
                            break;
                        case 'year':
                            $date = mktime(0, 0, 0, 1, 1, date('Y', $date)+$offset);
                            if($day == 'last') {
		                        $date = mktime(0, 0, 0, 12, 31, date('Y', $date)+$offset);
                            }
                            break;
                    }
                }
            }
            $this->date = $date;
        }
    }

    public function getDate()  {
        return $this->date;
    }

}