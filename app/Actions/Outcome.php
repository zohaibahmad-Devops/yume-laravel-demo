<?php

namespace App\Actions;

/** A refusal is a normal result here, not an exception — the caller shows it. */
readonly class Outcome
{
    private function __construct(
        public bool $ok,
        public string $message,
    ) {}

    public static function applied(string $message): self
    {
        return new self(true, $message);
    }

    public static function refused(string $message): self
    {
        return new self(false, $message);
    }
}
