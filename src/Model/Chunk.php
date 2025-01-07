<?php

declare(strict_types=1);

namespace Dayploy\DaypiaBundle\Model;

use Symfony\Component\Uid\Uuid;

readonly class Chunk
{
    public function __construct(
        private Uuid $id,
        private string $text,
        private float $similarity,
    ) {
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function getSimilarity(): float
    {
        return $this->similarity;
    }
}
