<?php

namespace SquaredPoint;

interface OpinionRepository
{
    public function addOpinion(string $opinion) : void;

    public function readOpinions() : array;
}
