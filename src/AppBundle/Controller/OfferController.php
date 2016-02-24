<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Room;
use AppBundle\Service\RoomFinderService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class OfferController extends Controller
{
    /** @var RoomFinderService $service */
    protected $service = null;

    /**
     * @param RoomFinderService $finderService
     */
    public function __construct(RoomFinderService $finderService) {
        $this->service = $finderService;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addAction(Request $request)
    {
        $date = $request->request->get('date');
        /** @var Room[] $rooms */
        $rooms = $this->service->find($date);

        $result = array();

        if ($rooms && count($rooms) > 0){
            foreach ($rooms as $room) {
                $result[] = $room->getName();
            }
        }

        return new JsonResponse(['date' => $date, 'result' => $result]);
    }

    /**
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function removeAction($id, Request $request)
    {
        $msg = 'Success';
        if (!$this->service->remove($id))
            throw $this->createNotFoundException('The offer does not exist');

        return new JsonResponse(['msg' => $msg]);
    }

}
