<?php

declare(strict_types=1);

namespace Vspj\PlatebniBrana\Comgate\Base;

class ComgateReturnRoute
{
    private string $symfonyRoute;

    private array $symfonyRouteParameters;

    /**
     * @param string $symfonyRoute #Route
     * @param Object[] $symfonyRouteParameters
     */
    public function __construct(string $symfonyRoute, array $symfonyRouteParameters = [])
    {
        $this->symfonyRoute = $symfonyRoute;
        $this->symfonyRouteParameters = $symfonyRouteParameters;
    }

    public function getSymfonyRoute(): string
    {
        return $this->symfonyRoute;
    }

    /**
     * @return Object[]
     */
    public function getSymfonyRouteParameters(): array
    {
        return $this->symfonyRouteParameters;
    }
}
