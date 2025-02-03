<?php

namespace UnasOnline\UnasConnect\Utils;

class Arrays
{
    /**
     * Helper function to return a default value when an array key doesn't exist
     *
     * @param array  $array   the array to retrieve data from
     * @param string $key     key for the requested value
     * @param mixed  $default default value
     *
     * @return mixed
     */
    public static function get(array $array, string $key, mixed $default = null): mixed
    {
        if (array_key_exists($key, $array)) {
            return $array[$key];
        }

        return $default;
    }
}
