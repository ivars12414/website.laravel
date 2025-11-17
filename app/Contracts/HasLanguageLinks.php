<?php

namespace App\Contracts;

interface HasLanguageLinks
{
    public function getLanguageLink(int $langId): ?string;

    public function getLangId(): int;

    public function getHash(): string;
}
