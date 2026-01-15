<?php

namespace App\AiFeatures;

use App\Contracts\AiFeatureContract;

class UprightStanding implements AiFeatureContract
{
    public function getKey(): string
    {
        return 'standing';
    }

    public function getCategory(): string
    {
        return 'fx';
    }

    public function getPrompt(): string
    {
        return 'The product must be shown in a perfectly upright standing position with a three-quarter view, standing vertically on its bottom edge, NOT tilted, NOT rotated, NOT slanted.';
    }
}
