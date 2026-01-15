<?php

namespace App\View\Components;

use Illuminate\View\Component;

class Heroicon extends Component
{
    public string $name;
    public string $class;
    public string $svg;

    /**
     * Cache SVG contents per request to avoid repeated disk reads.
     *
     * @var array<string, string>
     */
    protected static array $cache = [];

    public function __construct(string $name, string $class = 'w-5 h-5')
    {
        $this->name = $name;
        $this->class = $class;
        $this->svg = $this->loadSvg($name);
    }

    public function render()
    {
        return view('components.heroicon');
    }

    protected function loadSvg(string $name): string
    {
        if (isset(self::$cache[$name])) {
            return self::$cache[$name];
        }

        $path = base_path("node_modules/heroicons/24/outline/{$name}.svg");
        if (!is_file($path)) {
            self::$cache[$name] = '';
            return '';
        }

        self::$cache[$name] = file_get_contents($path) ?: '';
        return self::$cache[$name];
    }
}
