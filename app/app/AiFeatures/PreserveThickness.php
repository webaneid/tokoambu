<?php

namespace App\AiFeatures;

use App\Contracts\AiFeatureContract;

class PreserveThickness implements AiFeatureContract
{
    public function getKey(): string
    {
        return 'preserve_thickness';
    }

    public function getCategory(): string
    {
        return 'fx';
    }

    public function getPrompt(): string
    {
        return 'Maintain the original thickness and proportions of the product. No distortion or stretching.';
    }
}
