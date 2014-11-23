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
        /** @var Manager $sharer */
        $sharer = $app['sharer'];

        while($iterator->valid()) {
            /** @var Feature $feature */
            $feature = $iterator->current();
            $properties = $feature->getProperties();
            $point = $feature->getGeometry();

            $sharePoints = $dm->createQueryBuilder(SharePoint::class)
                ->geoNear($point)
                ->spherical(true)
                ->distanceMultiplier(\SMYQ\Helper\Distance::KILOMETERS_MULTIPLIER)
                ->getQuery()->execute();

            if(empty($sharePoints)) {
                $output->writeln(sprintf("<info>No share points found for event #%s</info>", $feature->getId()));
            } else {
                $event = (new \SMYQ\Event\Earthquake())->populate($properties);

                /** @var SharePoint $sharePoint */
                foreach($sharePoints as $sharePoint) {
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
