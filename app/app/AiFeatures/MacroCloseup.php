<?php

namespace App\AiFeatures;

use App\Contracts\AiFeatureContract;

class MacroCloseup implements AiFeatureContract
{
    public function getKey(): string
    {
        return 'macro_closeup';
    }

    public function getCategory(): string
    {
        return 'style';
    }

    public function getPrompt(): string
    {
        return 'Capture the product in a macro close-up style highlighting textures and fine details.';
    }
}
