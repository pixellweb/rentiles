<?php

namespace PixellWeb\Rentiles\app\Ressources;



use PixellWeb\Rentiles\app\Crawler;

abstract class Ressource
{

    public Crawler $crawler;


    /**
     * Ressource constructor.
     */
    public function __construct()
    {
        $this->crawler = new Crawler();
    }



}
