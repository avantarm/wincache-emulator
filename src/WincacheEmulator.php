<?php

namespace Avantarm {

    /**
     * Wincache emulator for user data caching functions.
     *
     * @package Avantarm\WincacheEmulator
     */
    class WincacheEmulator
    {
        /** @var array Cached data. */
        public static $data;

        /** @var array Data lifetimes. */
        public static $ttl;

        /**
         * @param  mixed $key
         * @param  mixed $value
         * @param  int   $ttl
         * @return mixed
         * @see wincache_ucache_add()
         */
        public static function add($key, $value, $ttl = 0)
        {
            if (!\is_array($key)) {
                return static::exists($key) ? false : static::set($key, $value, $ttl);
            }

            $failed = [];

            foreach ($key as $key0 => $value0) {
                if (static::exists($key0)) {
                    $failed[$key0] = -1;
                } else {
                    static::set($key0, $value0, $ttl);
                }
            }

            return \count($failed) === \count($key) ? false : $failed;
        }

        /**
         * @param  string $key
         * @param  int    $old_value
         * @param  int    $new_value
         * @return bool
         * @see wincache_ucache_cas()
         */
        public static function cas($key, $old_value, $new_value)
        {
            /** @noinspection NotOptimalIfConditionsInspection */
            if (!\is_int($old_value) || !\is_int($new_value) || static::get($key,
                    $success) !== $old_value || !$success) {
                return false;
            }

            // Set value directly to keep existing TTL.
            static::$data[$key] = $new_value;

            return true;
        }

        /**
         * @return  bool
         * @see wincache_ucache_clear()
         */
        public static function clear()
        {
            static::$data = [];
            static::$ttl = [];

            return true;
        }

        /**
         * @param  string $key
         * @param  int    $dec_by
         * @param  bool   $success
         * @return mixed
         * @see wincache_ucache_dec()
         */
        public static function dec($key, $dec_by = 1, &$success = null)
        {
            return static::inc($key, 0 - \abs($dec_by), $success);
        }

        /**
         * @param  string|array $key
         * @return mixed
         * @see wincache_ucache_delete()
         */
        public static function delete($key)
        {
            if (!\is_array($key)) {
                if (\array_key_exists($key, static::$data)) {
                    unset(static::$data[$key], static::$ttl[$key]);

                    return true;
                }

                return false;
            }

            $deleted = [];

            /** @var array $key */
            foreach ($key as $key0) {
                if (\array_key_exists($key0, static::$data)) {
                    unset(static::$data[$key0], static::$ttl[$key0]);

                    $deleted[] = $key0;
                }
            }

            return $deleted ?: false;
        }

        /**
         * @param  string $key
         * @return bool
         * @see wincache_ucache_exists()
         */
        public static function exists($key)
        {
            if (\array_key_exists($key, static::$data)) {
                // Check expiration.
                if (!empty(static::$ttl[$key]) && static::$ttl[$key] < \time()) {
                    static::delete($key);

                    return false;
                }

                return true;
            }

            return false;
        }

        /**
         * @param  mixed $key
         * @param  bool  $success
         * @return mixed
         * @see wincache_ucache_get()
         */
        public static function get($key, &$success = null)
        {
            if (!\is_array($key)) {
                $success = static::exists($key);

                return $success ? static::$data[$key] : false;
            }

            // Multiple keys, success is always true.
            $success = true;

            $results = [];

            foreach ($key as $key0) {
                if (static::exists($key0)) {
                    $results[$key0] = static::$data[$key0];
                }
            }

            return $results;
        }

        /**
         * @param  string $key
         * @param  int    $inc_by
         * @param  bool   $success
         * @return mixed
         * @see wincache_ucache_inc()
         */
        public static function inc($key, $inc_by = 1, &$success = null)
        {
            if (!static::exists($key)) {
                /** @noinspection UselessReturnInspection */
                return $success = false;
            }

            $value = static::get($key) + $inc_by;

            static::set($key, $value);

            $success = true;

            return $value;
        }

        /**
         * Retrieves information about data stored in the user cache.
         *
         * @param  bool   $summaryonly
         * @param  string $key
         * @return array
         * @see wincache_ucache_info()
         */
        public static function info($summaryonly = false, $key = null)
        {
            $info = [
                'total_cache_uptime' => 0,
                'is_local_cache'     => false, // user cache is always global
                'total_item_count'   => \count(static::$data),
                'total_hit_count'    => 0,
                'total_miss_count'   => 0,
            ];

            if (!$summaryonly) {
                $info['ucache_entries'] = [];

                if ($key !== null) {
                    if (\array_key_exists($key, static::$data)) {
                        $info['ucache_entries'][] = [
                            'key_name'    => $key,
                            'value_type'  => \gettype(static::$data[$key]),
                            'is_session'  => 0,
                            'ttl_seconds' => static::$ttl[$key] ?? 0,
                            'age_seconds' => 0,
                            'hitcount'    => 0,
                        ];
                    }
                } else {
                    foreach (static::$data as $k => $value) {
                        $info['ucache_entries'][] = [
                            'key_name'    => $k,
                            'value_type'  => \gettype($value),
                            'is_session'  => 0,
                            'ttl_seconds' => static::$ttl[$k] ?? 0,
                            'age_seconds' => 0,
                            'hitcount'    => 0,
                        ];
                    }
                }
            }

            return $info;
        }

        /**
         * @return array|false
         */
        public static function meminfo()
        {
            return [
                'memory_total'    => 0, // amount of memory in bytes allocated for the user cache
                'memory_free'     => 0, // amount of free memory in bytes available for the user cache
                'num_used_blks'   => 1, // number of memory blocks used by the user cache
                'num_free_blks'   => 1, // number of free memory blocks available for the user cache
                'memory_overhead' => 0, // amount of memory in bytes used for the user cache internal structures
            ];
        }

        /**
         * @param  mixed $key
         * @param  mixed $value
         * @param  int   $ttl
         * @return mixed
         * @see wincache_ucache_set()
         */
        public static function set($key, $value, $ttl = 0)
        {
            if (!\is_array($key)) {
                static::$data[$key] = $value;

                if ($ttl) {
                    static::$ttl[$key] = \time() + $ttl;
                }

                return true;
            }

            foreach ($key as $key0 => $value0) {
                static::set($key0, $value0, $ttl);
            }

            return [];
        }
    }
}
