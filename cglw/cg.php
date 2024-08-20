<?php
// header("Content-Type: text/calendar; charset=utf-8");
// header("Content-Disposition: inline; filename=umkc-roogroup.ics");
$group_id = !empty($_GET["group_id"]) ? htmlentities($_GET["group_id"]) : die("Error: Group ID not found");
$url = "https://roogroups.umkc.edu/rss_events?group_ids=$group_id";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);

$rss = simplexml_load_string($result);
$events = $rss->channel->item;

$eventStart = <<<ESTART
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//University of Missouri-Kansas City//EN
X-WR-CALNAME:University of Missouri-Kansas City Events
ESTART;
echo $eventStart;
foreach ( $events as $event ) :
  $uid = $event->eventUid . "@calendar.umkc.edu";
  $summary = $event->title;
  $description = $event->description;
  $fullDesc = $event->fullDescription;
  $url = $event->link;
  $location = $event->eventLocation;
  $timezone = "America/Chicago";
  $image = $event->eventPhotoFullUrl;
  $start = unixToiCal($event->eventStartDateTime, 5);
  $end = unixToiCal($event->eventEndDateTime, 5);

$eventOut = <<<EVENT
\nBEGIN:VEVENT
DTSTAMP;VALUE=DATE:$start
DTSTART;VALUE=DATE:$start
DTEND;VALUE=DATE:$end
LOCATION:$location
SUMMARY:$summary
UID:$uid
DESCRIPION:$description
URL:$url
X-LIVEWHALE-TYPE:events
X-LIVEWHALE-TIMEZONE:$timezone
ATTACH:$image
X-ALT-DESC;FMTTYPE=text/html:$fullDesc
END:VEVENT
EVENT;
echo $eventOut;
endforeach;
echo "\nEND:VCALENDAR";
/*
BEGIN:VEVENT
DTSTART;VALUE=DATE:20221205
DTEND;VALUE=DATE:20221206
LOCATION:School of Law
GEO:39.032505;-94.58187
SUMMARY:School of Law Fall 2022 Final Exam Period
UID:20221205T150000Z-21005@calendar.umkc.edu
DTSTAMP:20220909T133045Z
URL:https://calendar.umkc.edu/law/event/21005-school-of-law-fall-2022-fin
 al-exam-period
LAST-MODIFIED:20221107T161404Z
X-LIVEWHALE-TYPE:events
X-LIVEWHALE-ID:21005
X-LIVEWHALE-TIMEZONE:America/Chicago
X-LIVEWHALE-ALL-DAY:1
X-LIVEWHALE-CONTACT-INFO:Ashley Swanson-Hoye<br />\nDirector of Law Stude
 nt Services<br />\n<a href="mailto:swansonhoyea@umkc.edu">swansonhoyea@um
 kc.edu</a><br />\n816-213-1654
X-LIVEWHALE-SUMMARY:UMKC School of Law has 2 weeks of final exams. The de
 tailed schedule of exams is available on the school's intranet\, RooLaw.
END:VEVENT
  */


function unixToiCal($uStamp = 0, $tzone = 0.0) {
    $uStampUTC = $uStamp + ($tzone * 3600);
    $stamp  = date("Ymd\THis\Z", $uStampUTC);
    return $stamp;
}
