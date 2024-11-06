<?php

namespace App\Service;

class CalendarLinkService
{
    public function generateGoogleCalendarLink(\DateTimeInterface $start, \DateTimeInterface $end, string $title, string $description): string
    {
        $params = [
            'action' => 'TEMPLATE',
            'text' => $title,
            'dates' => $start->format('Ymd\THis') . '/' . $end->format('Ymd\THis'),
            'details' => $description,
        ];
        return 'https://calendar.google.com/calendar/render?' . http_build_query($params);
    }

    public function generateIcalLink(\DateTimeInterface $start, \DateTimeInterface $end, string $title, string $description): string
    {
        $content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\n" .
            "DTSTART:" . $start->format('Ymd\THis') . "\r\n" .
            "DTEND:" . $end->format('Ymd\THis') . "\r\n" .
            "SUMMARY:" . $title . "\r\n" .
            "DESCRIPTION:" . $description . "\r\n" .
            "END:VEVENT\r\nEND:VCALENDAR";
        
        return 'data:text/calendar;charset=utf8,' . urlencode($content);
    }
}
