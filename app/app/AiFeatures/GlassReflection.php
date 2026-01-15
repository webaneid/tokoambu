<?php

namespace App\AiFeatures;

use App\Contracts\AiFeatureContract;

class GlassReflection implements AiFeatureContract
{
    public function getKey(): string
    {
        return 'glass_reflection';
    }

    public function getCategory(): string
    {
        return 'fx';
    }

    public function getPrompt(): string
    {
        return 'Place the product on a sleek glass surface with a soft reflection below it.';
    }
}
