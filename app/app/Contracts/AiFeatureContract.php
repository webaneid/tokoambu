<?php

namespace App\Contracts;

interface AiFeatureContract
{
    public function getKey(): string;

    public function getPrompt(): string;

    public function getCategory(): string; // style or fx
}
