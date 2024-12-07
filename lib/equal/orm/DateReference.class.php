<?php
/*
    This file is part of the eQual framework <http://www.github.com/cedricfrancoys/equal>
    Some Rights Reserved, Cedric Francoys, 2010-2021
    Licensed under GNU LGPL 3 license <http://www.gnu.org/licenses/>
*/
namespace equal\orm;

class DateReference {

    private $date;

    public function __construct($descriptor) {
        $this->date = time();
        $this->parse($descriptor);
    }

    /**
     * Parse a descriptor and assign `$date` member to the retrieved date (as a timestamp)
     *
     * @param string $descriptor
     *   - either a string representing a date in ISO format (ISO 8601)
     *   - either a string representing the description of a date, wit a format relating to NOW
     *
     *   Result of the parsing is always a timestamp.
     *
     *   Syntax:
     *      date.{this|prev|next}[(<offset>)].{day|week|month|quarter|semester|year}.{first|last|get(reference:index)}
     *
     *  Examples:
     *   - today = date.this.day
     *   - first day of current year = date.this.year.first
     *   - last day of last week = date.prev.week.last*
     *
     */
    public function parse($descriptor) {
        if(is_numeric($descriptor)) {
            $this->date = $descriptor;
        }
        else {
            $matches = [];
            $descriptor = strtolower($descriptor);

            if(preg_match('/date\.(this|prev|next)(\((\d*)\))?\.(day|week|month|quarter|semester|year)(\.(first|last|get\((.+)\)))?/', $descriptor, $matches)) {
                // init at today
                $date = new \DateTime();

                $origin = $matches[1];
                $offset = isset($matches[3]) && $matches[3] !== '' ? (int)$matches[3] : 1;
                $interval = isset($matches[4]) ? $matches[4] : null;
                $method = isset($matches[6]) ? $matches[6] : null;
                $args = isset($matches[7]) ? $matches[7] : '';

                if($origin === 'prev') {
                    $offset = -$offset;
                }
                if($interval) {
                    $sign = ($offset >= 0 ? '+' : '');
                    switch($interval) {
                        case 'day':
                            $date->modify($sign.$offset.' day');
                            break;
                        case 'week':
                            $date->modify($sign.$offset.' week');
                            break;
                        case 'month':
                            $date->modify($sign.$offset.' month');
                            break;
                        case 'quarter':
                            $date->modify($sign.($offset * 3).' month');
                            break;
                        case 'semester':
                            $date->modify($sign.($offset * 6).' month');
                            break;
                        case 'year':
                            $date->modify($sign.$offset.' year');
                            break;
                    }
                }

                if($method) {
                    $month = (int)$date->format('n');
                    switch($method) {
                        case 'first':
                            switch($interval) {
                                case 'week':
                                    $date->setISODate((int)$date->format('o'), (int)$date->format('W'), 1);
                                    break;
                                case 'month':
                                    $date->modify('first day of this month');
                                    break;
                                case 'quarter':
                                    $quarter_start = (int)(($month - 1) / 3) * 3 + 1;
                                    $date->setDate($date->format('Y'), $quarter_start, 1);
                                    break;
                                case 'semester':
                                    $semester_start = $month <= 6 ? 1 : 7;
                                    $date->setDate($date->format('Y'), $semester_start, 1);
                                    break;
                                case 'year':
                                    $date->setDate($date->format('Y'), 1, 1);
                                    break;
                            }
                            break;
                        case 'last':
                            switch($interval) {
                                case 'week':
                                    $date->setISODate((int)$date->format('o'), (int)$date->format('W'), 7);
                                    break;
                                case 'month':
                                    $date->modify('last day of this month');
                                    break;
                                case 'quarter':
                                    $quarter_end = (int)(($month - 1) / 3) * 3 + 3;
                                    $date->setDate($date->format('Y'), $quarter_end, 1);
                                    $date->modify('last day of this month');
                                    break;
                                case 'semester':
                                    $semester_end = $month <= 6 ? 6 : 12;
                                    $date->setDate($date->format('Y'), $semester_end, 1);
                                    $date->modify('last day of this month');
                                    break;
                                case 'year':
                                    $date->setDate($date->format('Y'), 12, 31);
                                    break;
                            }
                            break;
                        default:
                            $parts = explode(':', $args);
                            if(count($parts) != 2) {
                                break;
                            }
                            [$reference, $index] = $parts;

                            $start = clone $date;
                            $end = clone $date;

                            switch ($interval) {
                                case 'week':
                                    $day_of_week = $start->format('w');
                                    $start->modify('-'.$day_of_week.' days');
                                    $end->modify('+'.(6 - $day_of_week).' days');
                                    break;
                                case 'month':
                                    $start->modify('first day of this month');
                                    $end->modify('last day of this month');
                                    break;
                                case 'quarter':
                                    $current_month = (int)$start->format('n');
                                    $quarter_start = (int)(($current_month - 1) / 3) * 3 + 1;
                                    $quarter_end = $quarter_start + 2;
                                    $start->setDate($start->format('Y'), $quarter_start, 1);
                                    $end->setDate($start->format('Y'), $quarter_end, 1)->modify('last day of this month');
                                    break;
                                case 'semester':
                                    $current_month = (int)$start->format('n');
                                    $semester_start = $current_month <= 6 ? 1 : 7;
                                    $semester_end = $semester_start + 5;
                                    $start->setDate($start->format('Y'), $semester_start, 1);
                                    $end->setDate($start->format('Y'), $semester_end, 1)->modify('last day of this month');
                                    break;
                                case 'year':
                                    $start->setDate($start->format('Y'), 1, 1);
                                    $end->setDate($start->format('Y'), 12, 31);
                                    break;
                            }

                            switch ($reference) {
                                case 'day':
                                    if(is_numeric($index)) {
                                        $start->modify('+' . ($index - 1) . ' days');
                                    }
                                    elseif($index == 'last') {
                                        $start = $end;
                                    }
                                    break;
                                case 'week':
                                    if(is_numeric($index)) {
                                        $start->modify('+' . ($index - 1) . ' weeks');
                                    }
                                    elseif($index == 'last') {
                                        $start = $end;
                                    }
                                    $day_of_week = $start->format('w');
                                    $start->modify('-'.$day_of_week.' days');
                                    break;
                                default:
                                    $days = array_flip([1 => 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']);
                                    if(in_array($reference, array_keys($days))) {
                                        $day_of_week = $days[$reference];

                                        $dates = [];
                                        $current = clone $start;

                                        while($current <= $end) {
                                            if($current->format('N') == $day_of_week) {
                                                $dates[] = clone $current;
                                                $current->modify('+7 day');
                                            }
                                            else {
                                                $current->modify('+1 day');
                                            }
                                        }

                                        if($index === 'first') {
                                            $start = $dates[0];
                                        }
                                        elseif($index === 'last') {
                                            $start = $dates[count($dates) - 1];
                                        }
                                        else {
                                            $start = $dates[$index - 1];
                                        }
                                    }
                                    break;
                            }
                            $date = $start;
                            break;
                    }
                }

                $this->date = $date->getTimestamp();
            }
        }
    }

    public function getDate()  {
        return $this->date;
    }

}
