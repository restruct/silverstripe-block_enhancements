<?php

if (!defined('BLOCKS_ENH_DIR')) {
    define('BLOCKS_ENH_DIR', rtrim(basename(dirname(__FILE__))));
}

// Versioning can optionally be removed from Blocks
// Note remove 'Published' lines 40 & 60 from GridFieldConfig_BlockManager as well
// Config::inst()->remove('Block', 'extensions', Config::anything(), "Versioned('Stage', 'Live')");
// OR something like: Block::remove_extension('Versioned');

// OR: Improve the isPublished status representation in your own Base Block class;

//public function isPublishedNice()
//{
//    if ($this->isPublished() && $this->stagesDiffer('Stage', 'Live')) { return '✔ (edited)'; }
//    if ($this->isPublished()) { return '✔'; }
//    return "✘";
//}

// Include these here already so the below js requirements dont choke
Requirements::javascript(THIRDPARTY_DIR . '/jquery/jquery.js');
Requirements::javascript(THIRDPARTY_DIR . '/jquery-entwine/dist/jquery.entwine-dist.js');
// Block some crappy tagfield requirements
Requirements::block('tagfield/css/select2.min.css');
Requirements::block('tagfield/css/TagField.css');
Requirements::block('tagfield/js/select2.js');
//Requirements::javascript(TAG_FIELD_DIR . '/js/TagField.js');

// include select2 from CDN
//Requirements::javascript('//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/js/select2.min.js');
//Requirements::css('//cdnjs.cloudflare.com/ajax/libs/select2/4.0.1/css/select2.min.css');
Requirements::javascript('components/select2/dist/js/select2.js');
Requirements::css('components/select2/dist/css/select2.css');

// Parts of this module have been isolated from Milkyway ss-mwm and ss-gridfield-utils
// This was done in order to use just the EditableRows without extending/replacing half the framework


// These functions are required by some mwm code:
if (!function_exists('array_set')) {
    /**
     * Set an array item to a given value using "dot" notation.
     *
     * If no key is given to the method, the entire array will be replaced.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $value
     * @return array
     */
    function array_set(&$array, $key, $value)
    {
        if (is_null($key)) {
            return $array = $value;
        }
        $keys = explode('.', $key);
        while (count($keys) > 1) {
            $key = array_shift($keys);
            // If the key doesn't exist at this depth, we will just create an empty array
            // to hold the next value, allowing us to create the arrays to hold final
            // values at the correct depth. Then we'll keep digging into the array.
            if (!isset($array[$key]) || !is_array($array[$key])) {
                $array[$key] = [];
            }
            $array =& $array[$key];
        }
        $array[array_shift($keys)] = $value;
        return $array;
    }
}
if (!function_exists('array_get')) {
    /**
     * Get an item from an array using "dot" notation.
     *
     * @param  array $array
     * @param  string $key
     * @param  mixed $default
     * @return mixed
     */
    function array_get($array, $key, $default = null)
    {
        if (is_null($key)) {
            return $array;
        }
        if (isset($array[$key])) {
            return $array[$key];
        }
        foreach (explode('.', $key) as $segment) {
            if (!is_array($array) || !array_key_exists($segment, $array)) {
                return value($default);
            }
            $array = $array[$segment];
        }
        return $array;
    }
}