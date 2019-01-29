<?php

namespace App\Controllers;

use Sabre\VObject;
use SimpleCalDAVClient;
use CalDAVFilter;
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;
#use IntlDateFormatter;

require __DIR__ . '/../Lib/simpleCalDAV/SimpleCalDAVClient.php';

class AjaxController extends Controller {

	private function getFreeBusy($startdate) {

		// Settings
        $calendarIDs = (array) $this->env->calIDs;
		$timezone    = new DateTimeZone($this->env->timezone);
		$daysahead   = 'P11D';

		// Initiate client
		$client = new SimpleCalDAVClient();
		$client->connect($this->env->caldavurl, $this->env->username, $this->env->password);
        $arrayOfCalendars = $client->findCalendars();

		// time for now
		$dt = new DateTime($startdate, $timezone);
		$now = $dt->format('Ymd\Thms\Z');

		// add 30 days
		$dt->add(new DateInterval($daysahead));
		$future = $dt->format('Ymd\Thms\Z');

		// filter events from now to 30 days in future
		$filter = new CalDAVFilter("VEVENT");
        $filter->mustOverlapWithTimerange($now, $future);
        $vcalendar = new VObject\Component\VCalendar;

        // loop through each calendar
        foreach($calendarIDs as $calendarID) {
            if(isset($arrayOfCalendars[$calendarID])) {
    
                // set defined calendar
		        $client->setCalendar($arrayOfCalendars[$calendarID]);
                $events = $client->getCustomReport($filter->toXML());
                
                // Summarize all events into one vcalendar object
		        foreach($events as $event) {
                    $vevent = VObject\Reader::read($event->getData())->getBaseComponent('VEVENT');
			        $vcalendar->add($vevent);
                }
            }        
        }

		// Get free-busy report
		$freebusy = new VObject\FreeBusyGenerator(
			new DateTime(),
			$dt,
            $vcalendar,
            $timezone
		);
        $vcalendar = $freebusy->getResult();
        
        // Return free-busy version of calendar
		return $vcalendar->VFREEBUSY;
	}

	public function scheduleEvent($request, $response, $args) {

		// Settings
        $eventDuration   = new DateInterval($this->env->eventDuration);
        $slotInterval    = new DateInterval($this->env->slotInterval);
		$available_start = explode(':', $this->env->availableStart);
		$available_end   = $this->env->availableEnd;
		$exclude_days    = (array) $this->env->excludeDays;
        $daysahead       = 7;
        $timezone        = new DateTimeZone($this->env->timezone);
        $display_week    = array(
            'Diese Woche',
            'Nächste Woche',
            'In 2 Wochen',
            'In 3 Wochen',
            'In 4 Wochen',
            'In 5 Wochen',
            'In 6 Wochen'
        );

        // get start date
        $startdate = $request->getQueryParam('startdate', 'now');

        // get vfreebusy calendar object
        $vfreebusy = $this->getFreeBusy($startdate);

		// event start and end datetimes
        $eventSlots = array();

        // time for now to compare with
        // add one hour to have time for preparation
        $now = new DateTime('now', $timezone);
        $now->add(new DateInterval('PT1H'));

        // start current slot today or at given time
        #error_log($request->getUri());
        $currentSlot = new DateTimeImmutable($startdate, $timezone);
        $currentSlot = $currentSlot->setTime($available_start[0], $available_start[1]);

        // save starting point for prev button
        $prev_days   = 8 + count($exclude_days);
        $prev        = $currentSlot->sub(new DateInterval('P'.$prev_days.'D'));

        $prevWeekday  = 7;
        $calendarWeek = $now->format('W');
        #$formatter = new IntlDateFormatter('de_DE', IntlDateFormatter::SHORT, IntlDateFormatter::SHORT, $timezone);

        // Plan: first loop through each day 
        // second loop from a-start to a-end
		// loop seven days ahead (matter of UI)
        for($i = 0; $i < $daysahead; $i++) {

            // get weekday as number
            $weekday = $currentSlot->format('N');

             // skip excluded days
            if(in_array($weekday, $exclude_days)) {
                $daysahead++;
            }
            else {
             
                // add date to event slot 
                // add if eventSlot is today
                // add if first day of the week
                $eventSlots[$i]['date']  = $currentSlot->format('d.m.Y');
                $eventSlots[$i]['today'] = ($currentSlot->format('dmY') == $now->format('dmY')) ? true : false;

                // define vars for first day in week 
                if($weekday <= $prevWeekday) {
                    $eventSlots[$i]['firstday'] = true;
                    $calendarWeekDiff           = $currentSlot->format('W') - $calendarWeek;
                    if($calendarWeekDiff >= 0) {
                        $eventSlots[$i]['display_week'] = $display_week[$calendarWeekDiff];
                    }
                }
                else {
                    $eventSlots[$i]['firstday']  = false;
                }
                #$eventSlots[$i]['weekday'] = $formatter->setPattern('D')->format($currentSlot);

                // loop till end time for this day
                $endTime = new DateTime($currentSlot->format('d.m.Y ' . $available_end), $timezone);
                $anyFree = false;
                while($currentSlot < $endTime) {

                    // check free slots after time of now
                    if($currentSlot < $now) {
                        $isFree = false;
                    }
                    else {
                    
                        // Unfortunately we have to convert to UTC for comparison
                        $currentSlot = $currentSlot->setTimeZone(new DateTimeZone('UTC'));
                        $isFree = $vfreebusy->isFree($currentSlot, $currentSlot->add($eventDuration));
                    }

                    // save if any slot is free at this day
                    if(!$anyFree) {
                        if($isFree) {
                            $anyFree = true;
                        }
                    }
                    
                    // convert back, save time and add duration
                    $currentSlot = $currentSlot->setTimeZone($timezone);
                    $eventSlots[$i]['times'][$currentSlot->format('H:i')] = $isFree;
                    $currentSlot = $currentSlot->add($slotInterval);
                }

                // Save if there was any free slot
                $eventSlots[$i]['anyFree'] = $anyFree;
            }
             
            // go to next day
            $currentSlot = $currentSlot->add(new DateInterval('P1D'));
            $currentSlot = $currentSlot->setTime($available_start[0], $available_start[1]);
            $prevWeekday = $weekday;
        }

        // Add one day to go further
        $next = $currentSlot->add(new DateInterval('P1D'));

        // set data for html template
        $data = array(
            'prev'  => $prev->format('d-m-Y'),    
            'next'  => $next->format('d-m-Y'),
            'slots' => $eventSlots
        );

        return $this->view->render($response, 'ajax/scheduler.html', $data); 
	}
}
