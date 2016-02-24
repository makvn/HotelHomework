<?php

namespace AppBundle\Service;

interface IRoomFinderService {

    public function find($date);

    public function remove($offerId);

} 