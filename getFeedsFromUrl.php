<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");

if (!isset($_GET['url']) || !filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    echo json_encode(['error' => 'Invalid or missing URL']);
    exit;
}

$url = $_GET['url'];

$feedContent = @file_get_contents($url);
if ($feedContent === false) {
    echo json_encode(['error' => 'Unable to fetch RSS feed']);
    exit;
}

libxml_use_internal_errors(true);
$xml = simplexml_load_string($feedContent);
if ($xml === false) {
    echo json_encode(['error' => 'Failed to parse RSS feed']);
    exit;
}

$items = [];
$namespaces = $xml->getNamespaces(true);
$feedItems = $xml->channel->item ?? $xml->entry;

foreach ($feedItems as $item) {
    $title = (string) ($item->title ?? '');
    $description = (string) ($item->description ?? $item->summary ?? '');
    $date = (string) ($item->pubDate ?? $item->updated ?? '');
    $link = (string) ($item->link ?? '');
    $image = '';
    
    // Check <enclosure> tag
    if (isset($item->enclosure) && (string) $item->enclosure['type'] === 'image/jpeg') {
        $image = (string) $item->enclosure['url'];
    }

    // Check <media:thumbnail> or <media:content>
    if (empty($image) && isset($namespaces['media'])) {
        $media = $item->children($namespaces['media']);
        if (isset($media->thumbnail)) {
            $image = (string) $media->thumbnail->attributes()->url;
        } elseif (isset($media->content)) {
            $image = (string) $media->content->attributes()->url;
        }
    }

    // Fallback: extract from <description> using regex
    if (empty($image) && preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $description, $matches)) {
        $image = $matches[1];
    }

    $items[] = [
        'title' => strip_tags($title),
        'description' => strip_tags($description),
        'image' => $image,
        'date' => $date,
        'url' => $link,
    ];
}

echo json_encode($items);
