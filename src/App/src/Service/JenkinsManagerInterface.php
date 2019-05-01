<?php

namespace App\Service;

interface JenkinsManagerInterface
{
    /**
     * @return string
     */
    public function getUrl(): string;

    /**
     * @return string
     */
    public function getUser(): string;

    /**
     * @return string
     */
    public function getPassword(): string;
}
