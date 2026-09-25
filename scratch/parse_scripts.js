const fs = require('fs');

// Fetch html via curl output or run php test_curl
const { execSync } = require('child_process');
const phpOutput = execSync('php scratch/test_curl.php', { encoding: 'utf-8', maxBuffer: 10 * 1024 * 1024 });

// Let's get docHtml by saving it in test_curl.php
