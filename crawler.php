<?php

$targetUrl = "https://en.wikipedia.org/wiki/Napoleon";
$depthLimit = 2;

$outputDirectory = "html/";
$filesList = glob($outputDirectory . '*');
foreach ($filesList as $file) {
    if (is_file($file)) {
        unlink($file);
    }
}

$parsedUrl = parse_url($targetUrl);

if (isset($parsedUrl['scheme']) && isset($parsedUrl['host'])) {
    $baseURL = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
    $robotsTxtUrl = rtrim($baseURL, '/') . '/robots.txt';

    $curlHandler = curl_init($robotsTxtUrl);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
    $robotsTxtContent = curl_exec($curlHandler);
    curl_close($curlHandler);

    if ($robotsTxtContent === false) {
        $disallowedLinks = [];
    } else {
        $disallowedLinks = [];
        $lines = explode("\n", $robotsTxtContent);

        foreach ($lines as $line) {
            $line = trim($line);

            if (strpos($line, 'Disallow:') === 0) {
                $disallowedPath = trim(substr($line, strlen('Disallow:')));
                $disallowedLink = rtrim($baseURL, '/') . $disallowedPath;
                $disallowedLinks[] = $disallowedLink;
            }
        }

        $disallowedUrls = $disallowedLinks;
    }
}

$originalUrl = $targetUrl;
$urlsToScrape = [$targetUrl];
$completedUrls = [];

while ($depthLimit > 0 && !empty($urlsToScrape)) {
    $currentUrl = array_pop($urlsToScrape);
    if (in_array($currentUrl, $completedUrls)) {
        continue;
    }
    $curlHandler = curl_init($currentUrl);
    curl_setopt($curlHandler, CURLOPT_RETURNTRANSFER, true);
    $htmlContent = curl_exec($curlHandler);

    if ($htmlContent === false) {
        continue;
    }

    if (trim($htmlContent) == "" || stripos($htmlContent, 'PAGE NOT FOUND') !== false || curl_getinfo($curlHandler, CURLINFO_HTTP_CODE) == 404) {
        continue;
    }

    curl_close($curlHandler);

    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML($htmlContent);
    libxml_use_internal_errors(false);

    $htmlContent = $dom->saveHTML();
    file_put_contents($outputDirectory . $depthLimit . ".html", $htmlContent);

    $hrefs = array();

    $anchorTags = $dom->getElementsByTagName('a');

    foreach ($anchorTags as $anchor) {
        $href = $anchor->getAttribute('href');
        $hrefs[] = $href;
    }

    $moreUrls = $hrefs;
    $moreUrlsFiltered = [];

    foreach ($moreUrls as $url) {
        if (strpos($url, '#') === 0) {
            continue;
        }

        if (strpos($url, '/') === 0) {
            $url = rtrim($baseURL, '/') . $url;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            continue;
        }

        if (in_array($url, $completedUrls)) {
            continue;
        }

        $allowed = true;
        foreach ($disallowedUrls as $disallowedLink) {
            $regexPattern = str_replace(['', '/'], ['.', '\/'], $disallowedLink);

            if (preg_match('/^' . $regexPattern . '$/', $url)) {
                $allowed = false;
                break;
            }
        }

        if (!$allowed) {
            continue;
        }

        $moreUrlsFiltered[] = $url;
    }

    $moreUrlsFiltered = array_unique($moreUrlsFiltered);
    $urlsToScrape = array_merge($urlsToScrape, $moreUrlsFiltered);
    $depthLimit--;
    $completedUrls[] = $currentUrl;
}
?>