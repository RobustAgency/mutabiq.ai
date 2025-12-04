<?php

namespace App\Services;

class ChecksumService
{
    public function generateChecksum(string $algorithm, string $filePath): string
    {
        return hash($algorithm, $filePath);
    }
}
