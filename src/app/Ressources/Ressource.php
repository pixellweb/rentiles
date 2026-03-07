<?php

namespace PixellWeb\Rentiles\app\Ressources;



use PixellWeb\Rentiles\app\Request;

class Ressource
{

    public Request $request;


    /**
     * Ressource constructor.
     */
    public function __construct()
    {
        $this->request = new Request();
    }



}
