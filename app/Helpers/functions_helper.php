<?php

function generate_short_id(int $length = 11): string
{
    $bytes = random_bytes(8);
    $encoded = base64_encode($bytes);
    
    $urlSafe = str_replace(['+', '/', '='], ['-', '_', ''], $encoded);
    
    return substr($urlSafe, 0, $length);
}

function resolveUniqueTitle(string $baseTitle, array $existingTitles): string {
    $exactMatchFound = false;
    $baseMatchFound = false;
    $usedSuffixes = [];

    foreach ($existingTitles as $title) {
        $existingTitle = $title;

        if ($existingTitle === $baseTitle) {
            $baseMatchFound = true;
            $exactMatchFound = true;
            continue;
        }

        if (preg_match('/ \((\d+)\)$/', $existingTitle, $matches)) {
            $number = (int)$matches[1];
            $suffixLength = strlen(' (' . $number . ')');
            $prefix = substr($existingTitle, 0, -$suffixLength);

            if ($prefix === $baseTitle) {
                $usedSuffixes[$number] = true;
            }
        }
    }

    if (!$exactMatchFound) {
        return $baseTitle;
    }

    $nextSuffix = 1;
    while (isset($usedSuffixes[$nextSuffix])) {
        $nextSuffix++;
    }

    return $baseTitle . ' (' . $nextSuffix . ')';
}