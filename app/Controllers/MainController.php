<?php

namespace App\Controllers;

use Sabre\VObject;
use DateTimeZone;
use DateTimeImmutable;
use DateInterval;
use SimpleCalDAVClient;

require __DIR__ . '/../Lib/simpleCalDAV/SimpleCalDAVClient.php';

class MainController extends Controller {

    public function index($request, $response, $args) {
        return $this->view->render($response, 'pages/index.html');
    }

    public function event($request, $response, $args) {
        return $this->view->render($response, 'pages/event.html');
    }

    public function registerEvent($request, $response, $args) {
            
        // prepare post variables
        $post      = $request->getParsedBody();
        $firstName = $post['firstname'];
        $lastName  = $post['lastname'];
        $email     = $post['email'];
        $datetime  = $post['date'] . ' ' . $post['time'];

        // event details
        $summary      = 'Telefonisches Erstgespräch';
        $description  = "{$firstName} {$lastName}";
        $description .= "\n{$email}";
        $dtstart      = new DateTimeImmutable($datetime, new DateTimeZone($this->env->timezone));
        $dtend        = $dtstart->add(new DateInterval($this->env->eventDuration));
        
        // create vcalendar
        $vcalendar = new VObject\Component\VCalendar([
            'VEVENT' => [
                'SUMMARY'     => $summary,
                'DESCRIPTION' => $description,
                'DTSTART'     => $dtstart,
                'DTEND'       => $dtend
            ]
        ]);
        
        // Settings
        $mainCalendarID = $this->env->mainCalID;

        // Initiate client
        $client = new SimpleCalDAVClient();
        $client->connect($this->env->caldavurl, $this->env->username, $this->env->password);
        $arrayOfCalendars = $client->findCalendars();

        // create new event        
        $client->setCalendar($arrayOfCalendars[$mainCalendarID]);
        $client->create($vcalendar->serialize());

        $data = array(
            'summary': $summary,
            'description': $description,
            'duration': $this->env->eventDuration,
            'datetime': $dtstart->format('d.m.Y - H:i');
        );

        // send mail to event owner
        $this->mailer->addAddress('coaching@sebclemens.de', 'Sebastian Clemens');
        $this->mailer->Subject = 'Neuer Termin:' . $summary;
        $this->mailer->Body    = $this->view->fetch('mails/newEventOwner.html', $data);
        $this->mailer->send();

        // display confirmed page
        return $this->view->render($response, 'pages/confirmed.html');
    }
}
