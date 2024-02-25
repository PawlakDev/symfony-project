<?php

namespace App\Command;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ICalCommand extends Command
{
    
    protected function configure(): void
    {
        $this
            ->setName('app:ical')
            ->setDescription('This command loads a link to an .ics file and splits it into individual events: id, start, end, summary')
            ->setHelp('Example use: php bin/console app:ical *here-link* ')
            ->addArgument('file', InputArgument::REQUIRED, 'Link to ics file');
    }

    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        // Get link from InputArgument
        $content = $input->getArgument('file');


        // Get .ics content
        $icalString = file_get_contents($content);
        $this->logger->debug('iCal content received.');

        // Process the contents of the .ics file and return formatted events
        $events = $this->parseICalEvents($icalString);
        $this->logger->info('Processed iCal events.');

        $formattedEvents = $this->formatEvents($events);
        $this->logger->info('Formatted iCal events.');

        // Convert the data to JSON format
        $jsonData = json_encode($formattedEvents, JSON_PRETTY_PRINT);
        $this->logger->debug('Formatted events JSON: ' . $jsonData);

        $this->logger->info('Returning iCal events as JSON.');
        dump($jsonData);

        return Command::SUCCESS;

    }

    private function parseICalEvents(string $icalString): array
    {
        // icalString -> Line Array
        $lines = explode("\n", $icalString);

        $this->logger->debug('Starting parsing iCal events.');

        $events = [];
        $currentEvent = null;

        foreach ($lines as $line) {
            $this->logger->debug('Parsing line: ' . $line);

            // Detect the beginning of an event
            if (str_contains($line, 'BEGIN:VEVENT')) {
                $this->logger->debug('Beginning of event detected.');
                $currentEvent = [];
            }
            // Detect the end of an event
            elseif (str_contains($line, 'END:VEVENT')) {
                if ($currentEvent !== null) {
                    $this->logger->debug('End of event detected. Adding event to the list.');
                    $events[] = $currentEvent;
                    $currentEvent = null;
                }
            } elseif ($currentEvent !== null) {
                // analyze line data
                list($key, $value) = explode(':', $line, 2);
                // Add data to actual event
                $currentEvent[$key] = $value;

                $this->logger->debug('Added data to event: ' . $key . ' => ' . $value);
            }
        }
        $this->logger->debug('Finished parsing iCal events.');

        return $events;
    }

    private function formatEvents(array $events): array
    {
        $this->logger->debug('Formatting events.');
        $formattedEvents = [];

        foreach ($events as $event) {
            $this->logger->debug('Formatted event - ID: ' . $event['UID'] . ', Start: ' . date("Y-m-d", strtotime($event['DTSTART;VALUE=DATE'])) . ', End: ' . date("Y-m-d", strtotime($event['DTEND;VALUE=DATE'])) . ', Summary: ' . $event['SUMMARY']);
            $formattedEvents[] = [
                "id" => $event['UID'],
                "start" => date("Y-m-d", strtotime($event['DTSTART;VALUE=DATE'])),
                "end" => date("Y-m-d", strtotime($event['DTEND;VALUE=DATE'])),
                "summary" => $event['SUMMARY']
            ];
        }

        $this->logger->debug('Finished formatting events.');

        return $formattedEvents;
    }
}
