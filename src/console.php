<?php

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use GeoJson\GeoJson;
use GeoJson\Geometry\Point;
use GeoJson\Feature\FeatureCollection;
use GeoJson\Feature\Feature;
use SMYQ\Share\Template;
use SMYQ\Share\Manager;
use SMYQ\Document\SharePoint;

$console = new Application('Share My Earthquake', 'n/a');
$console->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', 'dev'));
$console->setDispatcher($app['dispatcher']);
$console
    ->register('fetch-and-share-earthquakes')
    ->setDefinition([
        new InputOption('period', 'p', InputOption::VALUE_REQUIRED, 'Period to be parsed (default all_hour)', 'all_hour')
    ])
    ->setDescription('Fetch and share all the earthquakes data found')
    ->setCode(function (InputInterface $input, OutputInterface $output) use ($app) {
        $period = $input->getOption('period');

        $url = sprintf("http://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/%s.geojson", $period);

        $output->writeln(sprintf("<info>Fetching earthquakes for '%s' period</info>", $period));
        $geoJsonData = @json_decode(@file_get_contents($url) ? : []) ? : [];

        /** @var FeatureCollection $featureCollection */
        $featureCollection = GeoJson::jsonUnserialize($geoJsonData);
        $iterator = $featureCollection->getIterator();
        $template = new Template();

        if($iterator->count() <= 0) {
            $output->writeln(sprintf("<info>There are no new earthquakes out there</info>", $period));
            return 0;
        }

        /** @var \Doctrine\ODM\MongoDB\DocumentManager $dm */
        $dm = $app['odm'];
        $dm->getSchemaManager()->ensureIndexes();

        /** @var Manager $sharer */
        $sharer = $app['sharer'];

        while($iterator->valid()) {
            /** @var Feature $feature */
            $feature = $iterator->current();
            $properties = $feature->getProperties();
            $geometry = $feature->getGeometry();
            list($latitude, $longitude) = $geometry->getCoordinates();

            $dm->clear();// hook to fix issue with distance mapping...
            $sharePoints = $dm->createQueryBuilder(SharePoint::class)
                ->field('coordinates')
                ->geoNear($latitude, $longitude)
                // todo: Figure out this
                ->distanceMultiplier(\SMYQ\Helper\Distance::KILOMETERS_MULTIPLIER)
                //->maxDistance(6000 * \SMYQ\Helper\Distance::KILOMETERS_MULTIPLIER) // what's a magic here =)
                ->getQuery();

            if(empty($sharePoints)) {
                $output->writeln(sprintf("<info>No share points found for event #%s</info>", $feature->getId()));
            } else {
                $event = (new \SMYQ\Event\Earthquake())->populate($properties);

                $output->writeln(sprintf(
                    "<info>Start matching event #%s... (%f:%f)</info>",
                    $feature->getId(),
                    $latitude,
                    $longitude
                ));

                echo "\nEV: $latitude, $longitude\n";

                /** @var SharePoint $sharePoint */
                foreach($sharePoints as $sharePoint) {
                    $output->writeln(sprintf(
                        "<info>Matching event #%s for share point #%s...</info>",
                        $feature->getId(),
                        $sharePoint->getId()
                    ));
echo "\nSP: {$sharePoint->getCoordinates()->getLatitude()}, {$sharePoint->getCoordinates()->getLongitude()}\n";
                    echo "\n{$sharePoint->getCalculatedDistance()} VS {$sharePoint->getDistance()}\n";
                    continue;
                    if($sharePoint->getCalculatedDistance() > $sharePoint->getDistance()) {
                        continue;
                    }

                    $sharedEvent = $dm->createQueryBuilder(\SMYQ\Document\SharedEvent::class)
                        ->field('eventId')->equals($feature->getId())
                        ->getQuery()->getSingleResult();

                    if($sharedEvent) {
                        $output->writeln(sprintf(
                            "<info>Event #%s already shared for share point #%s. Skipping...</info>",
                            $feature->getId(),
                            $sharePoint->getId()
                        ));
                        continue;
                    }

                    $output->writeln(sprintf(
                        "<info>Sharing event #%s for share point #%s!</info>",
                        $feature->getId(),
                        $sharePoint->getId()
                    ));

                    $text = $template->render($sharePoint, $event);
                    $socialAccount = $sharePoint->getSocialAccount();

                    if($sharer->share($socialAccount, $text)) {
                        $sharedEvent = new \SMYQ\Document\SharedEvent();
                        $sharedEvent->setDatetime(new \DateTime());
                        $sharedEvent->setEventId($feature->getId());
                        $sharedEvent->setOriginalData($properties);
                        $sharedEvent->setSharedText($text);
                        $sharedEvent->setType($event->getType());
                        $sharedEvent->setSharePoint($sharePoint);

                        $dm->persist($sharedEvent);
                        $dm->flush();

                        $output->writeln(sprintf(
                            "<info>Event #%s shared for account #%s</info>",
                            $feature->getId(),
                            $socialAccount->getId()
                        ));
                    } else {
                        $output->writeln(sprintf(
                            "<error>Unable to share event #%s for account #%s</error>",
                            $feature->getId(),
                            $socialAccount->getId()
                        ));
                    }
                }
            }

            $iterator->next();
        }

        $output->writeln(sprintf("<info>Parsed earthquakes successfully shared</info>", $period));
        return 0;
    })
;

return $console;
