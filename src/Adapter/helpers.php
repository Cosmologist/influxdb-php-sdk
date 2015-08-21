<?php
namespace InfluxDB\Adapter;

use DateTime;

function message_to_line_protocol(array $message)
{
    if (!array_key_exists("points", $message)) {
        return;
    }

    $unixepoch = (int)(microtime(true) * 1e9);
    if (array_key_exists("time", $message)) {
        $dt = new DateTime($message["time"]);
        $unixepoch = (int)($dt->format("U") * 1e9);
    }

    $lines = [];
    foreach ($message["points"] as $point) {
        $tags = array_key_exists("tags", $message) ? $message["tags"] : [];
        if (array_key_exists("tags", $point)) {
            $tags = array_replace_recursive($tags, $point["tags"]);
        }

        $tagLine = "";
        if ($tags) {
            $tagLine = sprintf(",%s", list_to_string($tags));
        }

        $lines[] = sprintf(
            "%s%s %s %d", $point["measurement"], $tagLine, list_to_string($point["fields"], true), $unixepoch
        );
    }

    return implode("\n", $lines);
}

function list_to_string(array $elements, $escape = false)
{
    array_walk($elements, function(&$value, $key) use ($escape) {
        if ($escape && is_string($value)) {
            $value = "\"{$value}\"";
        }

        if (is_bool($value)) {
            $value = ($value) ? "true" : "false";
        }

        $value = "{$key}={$value}";
    });

    return implode(",", $elements);
}
