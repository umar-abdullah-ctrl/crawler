<?php

$searchTerm = "japan";
$resultLimit = 100;

$outputDirectory = "html/";
$documentObjects = [];

$htmlFilesList = glob($outputDirectory . '/*.html');
foreach ($htmlFilesList as $htmlFile) {
    $document = new DOMDocument;
    libxml_use_internal_errors(true);
    $document->loadHTMLFile($htmlFile);
    libxml_clear_errors();
    $documentObjects[] = $document;
}

$documentData = [];

foreach ($documentObjects as $document) {
    $titleElement = $document->getElementsByTagName('title')->item(0);
    $headings = $document->getElementsByTagName('h1');
    $paragraphs = $document->getElementsByTagName('p');
    $spans = $document->getElementsByTagName('span');
    $anchors = $document->getElementsByTagName('a');
    $listItems = $document->getElementsByTagName('li');
    $tableCells = $document->getElementsByTagName('td');
    $labels = $document->getElementsByTagName('label');
    $buttons = $document->getElementsByTagName('button');

    $title = $titleElement ? $titleElement->textContent : 'No title found';
    $headingTexts = [];
    foreach ($headings as $heading) {
        $headingTexts[] = $heading->textContent;
    }
    $paragraphTexts = [];
    foreach ($paragraphs as $paragraph) {
        $paragraphTexts[] = $paragraph->textContent;
    }
    $spanTexts = [];
    foreach ($spans as $span) {
        $spanTexts[] = $span->textContent;
    }
    $anchorTexts = [];
    foreach ($anchors as $anchor) {
        $anchorTexts[] = $anchor->textContent;
    }
    $listItemTexts = [];
    foreach ($listItems as $listItem) {
        $listItemTexts[] = $listItem->textContent;
    }
    $cellTexts = [];
    foreach ($tableCells as $cell) {
        $cellTexts[] = $cell->textContent;
    }
    $labelTexts = [];
    foreach ($labels as $label) {
        $labelTexts[] = $label->textContent;
    }
    $buttonTexts = [];
    foreach ($buttons as $button) {
        $buttonTexts[] = $button->textContent;
    }

    $documentData = array_merge($documentData, $headingTexts, $paragraphTexts, $spanTexts, $anchorTexts, $listItemTexts, $cellTexts, $labelTexts, $buttonTexts);
}

$documentData = array_filter(array_map('trim', $documentData), function($value) {
    return $value !== '';
});

$searchTermVector = array_count_values(str_split($searchTerm));

$documentVectors = [];

foreach ($documentData as $document) {
    $documentVector = array_count_values(str_split($document));
    $documentVectors[$document] = $documentVector;
}

$scores = [];

foreach ($documentVectors as $document => $documentVector) {
    $dotProduct = 0;
    $magnitudeA = 0;
    $magnitudeB = 0;

    foreach ($searchTermVector as $key => $value) {
        if (isset($documentVector[$key])) {
            $dotProduct += $value * $documentVector[$key];
        }

        $magnitudeA += pow($value, 2);
    }

    foreach ($documentVector as $value) {
        $magnitudeB += pow($value, 2);
    }

    $magnitudeA = sqrt($magnitudeA);
    $magnitudeB = sqrt($magnitudeB);

    $score = ($magnitudeA == 0 || $magnitudeB == 0) ? 0 : $dotProduct / ($magnitudeA * $magnitudeB);
    $scores[$document] = $score;
}

arsort($scores);
$topResults = array_slice($scores, 0, $resultLimit, true);

$answer = implode('. ', array_keys($topResults));
$lowercaseAnswer = strtolower($answer);
$lowercaseSearchTerm = strtolower($searchTerm);

$highlightedAnswer = preg_replace_callback(
    "/$lowercaseSearchTerm/",
    function ($match) use ($searchTerm) {
        return '<u><i>' . substr($match[0], 0, strlen($searchTerm)) . '</i></u>' . substr($match[0], strlen($searchTerm));
    },
    $answer
);

print_R($highlightedAnswer);

?>