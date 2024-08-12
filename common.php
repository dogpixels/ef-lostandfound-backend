<?php
/**
 * Lost and Found Page Backend, Common Functions
 * draconigen@dogpixels.net, GPLv3
 */

function report(array $telegram, string $message): void
{
    print($message . "\n");

    $data = [
        'chat_id' => $telegram['admin'],
        'text' => $message,
        'parse_mode' => 'MarkdownV2'
    ];

    $curl = curl_init($telegram['api']);

    curl_setopt_array($curl, [
            CURLOPT_POST => true,                           // set post request method
            CURLOPT_POSTFIELDS => http_build_query($data),  // attach url-encoded post data
            CURLOPT_RETURNTRANSFER => true                  // return response, rather than printing it to stdout
        ]
    );

    $response = curl_exec($curl);

    if (!json_decode($response)->ok)
    {
        print("\nAdditionally to the error above, telegram error reporting failed with the following reponse from Telegram Bot API:\n{$response}");
    }
}
