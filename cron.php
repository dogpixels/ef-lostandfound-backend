<?php
/**
 * Lost and Found Page Live Data Update Script
 * draconigen@dogpixels.net, GPLv3
 */

include_once("config.php");
include_once("common.php");

// read data from lassie api
$data = json_decode(
    file_get_contents(
        $lassie['api'], false, stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/x-www-form-urlencoded',
                'content' => http_build_query([
                    'request' => 'lostandfounddb',
                    'apikey' => $lassie['key']
                ])
            ]
        ])
    ),
    true
);

// check data
if (!(isset($data['data'])) || empty($data['data']))
{
    report($telegram, sprintf(
        "LostAndFound Backend Error\nMalformed or empty Data\nSource: `%s:%d`\n\nData:\n```json\n%s\n```",
        __FILE__,
        __LINE__ - 5, // point to line: if (!$data->ok)
        json_encode($data, JSON_PRETTY_PRINT)
    ));

    exit(1);
}

// save api output to cache file
if (file_put_contents("{$basepath}/data.cache.json", json_encode($data, 0)) === false)
{
    report($telegram, sprintf(
        "LostAndFound Backend Error\nFailed to write to `{$basepath}/data\.cache\.json`\nSource: `%s:%d`",
        __FILE__,
        __LINE__ - 5 // point to line: if (file_put_content(...) === false)
    ));

    exit(1);
}

// move cache to prod
if (!rename("{$basepath}/data.cache.json", "{$basepath}/data.json"))
{
    report($telegram, sprintf(
        "LostAndFound Backend Error\nFailed to move cache to `{$basepath}/data\.json`\nSource: `%s:%d`",
        __FILE__,
        __LINE__ - 5 // point to line: if (!rename(...))
    ));
    
    exit(1);
}

// download images
foreach ($data['data'] as $entry)
{
    // perform the same for image and thumbnail (both keys in $data['data'])
    foreach (['image', 'thumb'] as $key)
    {
        // image may be "null", signaling placeholder usage
        if ($entry[$key] === null)
            continue;

        $path = "{$basepath}/{$key}/" . basename($entry[$key]);

        // skip downloading file if it already exists
        if (file_exists($path))
            continue;

        // file does not exist yet, retrieve contents
        $file_contents = file_get_contents($entry[$key]);

        // check file contents
        if (strlen($file_contents) === 0)
        {
            report($telegram, sprintf(
                "LostAndFound Backend Error\nZero file length downloaded from API for `{$path}`\nSource: `%s:%d`",
                __FILE__,
                __LINE__ - 5 // point to line: if (strlen($file_contents) === 0)
            ));

            continue;
        }

        // write to file
        if (file_put_contents($path, $file_contents) === false)
        {
            report($telegram, sprintf(
                "LostAndFound Backend Error\nFailed to write to `{$path}`\nSource: `%s:%d`",
                __FILE__,
                __LINE__ - 5 // point to line: if (file_put_contents(...) === false)
            ));

            continue;
        }
    }
}
