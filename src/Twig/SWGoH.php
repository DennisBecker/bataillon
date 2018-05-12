<?php

namespace Bataillon\Twig;

class SWGoH extends \Twig_Extension
{
    public function getFunctions()
    {
        return [
            new \Twig_Function('getReadinessColor', [$this, 'getReadinessColor']),
        ];
    }

    public function getReadinessColor($required, $actual)
    {
        if ($actual === 0) {
            return 'red';
        }

        if ($actual >= $required) {
            return 'green';
        }

        return 'yellow';
    }
}