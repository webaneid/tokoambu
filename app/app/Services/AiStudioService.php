<?php

namespace App\Services;

use App\Contracts\AiFeatureContract;
use Illuminate\Support\Collection;

class AiStudioService
{
    /** @var array<string,AiFeatureContract> */
    protected array $features = [];

    public function __construct()
    {
        $featureClasses = config('aistudio.features', []);
        $instances = collect($featureClasses)
            ->filter(fn (string $class) => class_exists($class))
            ->map(fn (string $class) => app($class))
            ->filter(fn ($instance) => $instance instanceof AiFeatureContract)
            ->all();

        $this->registerFeatures($instances);
    }

    /**
     * @param AiFeatureContract[] $featureInstances
     */
    public function registerFeatures(array $featureInstances): void
    {
        foreach ($featureInstances as $instance) {
            $this->features[$instance->getKey()] = $instance;
        }
    }

    public function buildFinalPrompt(array $selectedKeys, string $bgColor, bool $useSolid): string
    {
        $promptParts = [];

        foreach ($selectedKeys as $key => $isActive) {
            if ($isActive && isset($this->features[$key])) {
                $promptParts[] = $this->features[$key]->getPrompt();
            }
        }

        $background = $useSolid
            ? "ON A PURE FLAT SOLID {$bgColor} BACKGROUND. NO GRADIENTS."
            : 'In a premium commercial studio environment with controlled lighting.';

        return trim(
            'STRICT INSTRUCTION: Keep product identity identical. '
            . $background . ' '
            . implode(' ', $promptParts)
            . ' Professional commercial photography look.'
        );
    }

    public function getAvailableFeatures(): Collection
    {
        return collect($this->features)
            ->map(fn (AiFeatureContract $feature) => [
                'key' => $feature->getKey(),
                'category' => $feature->getCategory(),
            ]);
    }
}
