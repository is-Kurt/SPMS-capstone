<?php

function generate_short_id(int $length = 11): string
{
    $bytes = random_bytes(8);
    $encoded = base64_encode($bytes);
    
    // Remove padding (=) and swap URL-unsafe chars
    $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
    
    return substr($urlSafe, 0, $length);
}