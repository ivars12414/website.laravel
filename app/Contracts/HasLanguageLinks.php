<?php

namespace App\Contracts;

interface HasLanguageLinks
{
    public function getLanguageLink(int $langId): ?string;
}
