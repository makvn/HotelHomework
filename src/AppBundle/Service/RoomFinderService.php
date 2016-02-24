<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Room;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DomCrawler\Crawler;
use Doctrine\ORM\EntityManager;

class RoomFinderService implements IRoomFinderService
{

    protected $hotelName = 'The Reverie Residence';

    private $searchDate;

    /**
     * @var EntityManager $entityManager ;
     */
    private $entityManager;

    public function __construct(EntityManager $em)
    {
        $this->entityManager = $em;
    }

    /**
     * @param $date
     * @return \AppBundle\Entity\Room[]|bool
     * @throws \Exception
     */
    public function find($date)
    {
        $fromDate = \DateTime::createFromFormat('Y-m-d', $date);
        $this->searchDate = clone $fromDate;
        $toDate = clone $fromDate;
        $toDate = $toDate->modify('+1 day');

        $uri = $this->getHotelUri($fromDate->format('d/m/Y'), $toDate->format('d/m/Y'));
        try {
            $client = new Client();
            $response = $client->get($uri);
            $body = $response->getBody();
            $rooms = $this->transformResponse($body);

            return $rooms;
        } catch (GuzzleException $e) {
            $message = $e->getMessage();
            $code = $e->getCode();

            throw new ClientException($message, $code);
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Exception
     */
    public function remove($id)
    {
        // cascade remove, room auto removed
        $offer = $this->entityManager->getRepository('AppBundle:Offer')->find($id);

        if (!$offer) {
            return false;
        }

        $this->entityManager->remove($offer);
        $this->entityManager->flush();

        return true;
    }

    /**
     * @param $body
     * @return \AppBundle\Entity\Room[]|bool
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Exception
     */
    private function transformResponse($body)
    {
        $crawler = new Crawler($body->getContents());
        $filter = $crawler->filter('#rooms-and-rates .room-info h3');
        $result = array();

        if (iterator_count($filter) > 1) {
            foreach ($filter as $i => $content) {
                $crawler = new Crawler($content);
                $result[$i] = array(
                    'name' => $crawler->filter('h3')->text()
                );
            }
        }

        if (count($result) == 0)
            return false;

        try {
            $this->entityManager->getConnection()->beginTransaction();

            $offer = new Offer();
            $offer->setName($this->hotelName);
            $offer->setDate($this->searchDate);
            $this->entityManager->persist($offer);

            /** @var Room[] $rooms */
            $rooms = array();
            foreach ($result as $i => $item) {
                $room = new Room();
                $room->setName($item['name']);
                $room->setOffer($offer);
                $this->entityManager->persist($room);
                $this->entityManager->flush();
                $rooms[] = $room;
            }

            $this->entityManager->commit();

            return $rooms;
        } catch (\Exception $e) {
            $this->entityManager->getConnection()->rollback();
            throw $e;
        }
    }

    /**
     * @param $checkIn
     * @param $checkOut
     * @return string
     */
    private function getHotelUri($checkIn, $checkOut)
    {
        $url = 'http://www.hotels.com/hotel/details.html?hotel-id=555246&q-localised-check-in=%s&q-localised-check-out=%s';
        $uri = sprintf($url, $checkIn, $checkOut);

        return $uri;
    }
}