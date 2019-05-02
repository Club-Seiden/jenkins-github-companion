<?php

namespace App\Repository;

interface JenkinsJobRepositoryInterface
{
    /**
     * @param string $name
     * @return array
     */
    public function getJobByFullRepositoryName(string $name): array;
}
