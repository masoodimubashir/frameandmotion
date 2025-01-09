<?php

namespace App;

use Google_Client;
use Google_Service_Calendar;
use Google_Service_Calendar_Event;
use Google_Service_Calendar_EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    private $client;
    private $service;

    public function __construct()
    {
        $this->initializeGoogleClient();
    }

    private function initializeGoogleClient()
    {
        if (session()->has('google_token')) {
            $this->client = new Google_Client();
            $this->client->setAccessToken(session('google_token'));
            $this->service = new Google_Service_Calendar($this->client);
        }
    }

    public function createEvent(array $eventDetails): bool
    {
        if (!$this->service) {
            Log::error('Google Calendar service not initialized');
            return false;
        }

        try {
            $event = new Google_Service_Calendar_Event([
                'summary' => $eventDetails['title'],
                'description' => $eventDetails['description'],
                'start' => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $eventDetails['start_datetime'],
                    'timeZone' => $eventDetails['timezone'],
                ]),
                'end' => new Google_Service_Calendar_EventDateTime([
                    'dateTime' => $eventDetails['end_datetime'],
                    'timeZone' => $eventDetails['timezone'],
                ]),
                'attendees' => array_map(function ($email) {
                    return ['email' => $email];
                }, $eventDetails['attendees']),
            ]);

            $this->service->events->insert('primary', $event);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create Google Calendar event: ' . $e->getMessage());
            return false;
        }
    }
}
