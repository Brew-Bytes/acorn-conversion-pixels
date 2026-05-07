<?php

declare(strict_types=1);

namespace BrewAndBytes\AcornConversionPixels\Contracts;

interface Module
{
    /**
     * Handle the module.
     */
    public function handle(): void;
}
