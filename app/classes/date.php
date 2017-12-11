<?php

/**
 * ClassesDate is a class that stores a date and provides logic to manipulate
 * and render that date in a variety of formats and language.
 */
class ClassesDate extends DateTime
{
    const DAY_ABBR   = "\x021\x03";
    const DAY_NAME   = "\x022\x03";
    const MONTH_ABBR = "\x023\x03";
    const MONTH_NAME = "\x024\x03";

    /**
     * @var		DateTimeZone	Placeholder for a DateTimeZone object with GMT as the time zone.
     */
    protected static $datetimezone_gmt;

    /**
     * @var		DateTimeZone	Placeholder for a DateTimeZone object with the default time zone.
     */
    protected static $datetimezone_server;

    /**
     * Generic function to do a new RsDate().

     *
     * @param string    $date   String in a format accepted by {@link http://php.net/manual/function.strtotime.php strtotime()}, defaults to "now".
     * @param mixed     $tz     Time zone to be used for the date (List of supported {@link http://php.net/manual/timezones.php timezones}).
     *
     * @return  self
     * @since 1.0
     */
    public static function getInstance($date = 'now', $format = null, $tz = null)
    {
        $c = get_called_class();

        if ($format) {
            return call_user_func_array(array($c, 'createFromFormat'), array($format, $date, $tz));
        }

        return new $c($date, $tz);
    }

    /**
     * Init
     *
     * @since 1.0
     */
    protected static function _init()
    {
        if (empty(self::$datetimezone_gmt) || empty(self::$datetimezone_server)) {
            self::$datetimezone_gmt = new DateTimeZone('GMT');
            self::$datetimezone_server = new DateTimeZone(date_default_timezone_get());
        }
    }

    /**
     * Constructor.
     *
     * @param string    $date       String in a format accepted by strtotime(), defaults to "now".
     * @param string    $format     The format that the passed in string should be in. See the formatting options below. In most cases, the same letters as for the {@link http://php.net/manual/function.date.php date()} can be used.
     * @param mixed     $tz         Time zone to be used for the date. Might be a string or a DateTimeZone object (List of supported {@link http://php.net/manual/timezones.php timezones}).
     *
     * @since 1.0
     */
    public function __construct($date = 'now', $tz = null)
    {
        self::_init();

        // If the time zone object is not set, attempt to build it.
        if (!($tz instanceof DateTimeZone)) {
            if ($tz === null) {
                $tz = new DateTimeZone(self::$datetimezone_server->getName());
            } elseif (is_string($tz)) {
                $tz = new DateTimeZone($tz);
            }
        }

        // If the date is numeric assume a unix timestamp and convert it.
        $is_timestamp = false;
        if (is_numeric($date)) {
            $date = '@'.$date;
            $is_timestamp = true;
        }

        // Create the object
        parent::__construct($date, $tz);
        // Set the timezone if timestamp
        if($is_timestamp) {
            $this->setTimezone($tz);
        }
    }

    /**
     * @param string $format
     * @param string $date
     * @param null $tz
     * @return bool|DateTime|mixed
     * @throws Exception
     */
    public static function createFromFormat($format, $date, $tz = null)
    {
        $c = get_called_class();

        if (method_exists('DateTime', 'createFromFormat')) {
            self::_init();

            // If the time zone object is not set, attempt to build it.
            if (!($tz instanceof DateTimeZone)) {
                if ($tz === null) {
                    $tz = new DateTimeZone(self::$datetimezone_server->getName());
                } elseif (is_string($tz)) {
                    $tz = new DateTimeZone($tz);
                }
            }

            $date = parent::createFromFormat($format, $date, $tz);

            if ($date === false) {
                throw new Exception("RsDate::createFromFormat() - The date or the format is not fine.");
            }

            $date = $date->format('Y-m-d H:i:s');
        } else {
            $date = date_parse_from_format($format, $date);

            if ($date === false or !empty($date['error_count'])) {
                throw new Exception("RsDate::createFromFormat() - The date or the format is not fine.");
            }

            $date = date('Y-m-d H:i:s', mktime($date['hour'], $date['minute'], $date['second'], $date['month'], $date['day'], $date['year']));
        }

        return new $c($date, $tz);
    }

    /**
     * Gets the date as a formatted string based on locale.
     *
     * <b>Notes</b>
     * Note that you must define a locale to change the language with {@link http://php.net/manual/function.setlocale.php setlocale()}.
     *
     * @param string    $format     The date format specification string (see {@link http://php.net/manual/function.date.php date})
     * @param boolean   $local      True to return the date string in the local time zone or DateTimeZone if defined, false to return it in GMT.
     *
     * @return  string   The date string in the specified format format.
     * @since 1.0
     */
    public function format($format, $local = true)
    {
        // Do string replacements for date format options that can be translated.
        $format = preg_replace('/(^|[^\\\])D/', "\\1".self::DAY_ABBR, $format);
        $format = preg_replace('/(^|[^\\\])l/', "\\1".self::DAY_NAME, $format);
        $format = preg_replace('/(^|[^\\\])M/', "\\1".self::MONTH_ABBR, $format);
        $format = preg_replace('/(^|[^\\\])F/', "\\1".self::MONTH_NAME, $format);

        // If the returned time should not be local use GMT.
        if ($local == false) {
            // Get a tmp version of timezone
            $tz = $this->getTimezone();
            // Force the timezone of datetime in GMT
            $this->setTimezone(self::$datetimezone_gmt);
            // Force the default timezone for strftime with timestamp
            date_default_timezone_set('UTC');
        }

        // Format the date.
        $return = parent::format($format);

        // Manually modify the month and day strings in the formatted time.
        if (strpos($return, self::DAY_ABBR) !== false) {
            $return = str_replace(self::DAY_ABBR, strftime('%a', parent::format('U')), $return);
        }
        if (strpos($return, self::DAY_NAME) !== false) {
            $return = str_replace(self::DAY_NAME, strftime('%A', parent::format('U')), $return);
        }
        if (strpos($return, self::MONTH_ABBR) !== false) {
            $return = str_replace(self::MONTH_ABBR, strftime('%b', parent::format('U')), $return);
        }
        if (strpos($return, self::MONTH_NAME) !== false) {
            $return = str_replace(self::MONTH_NAME, strftime('%B', parent::format('U')), $return);
        }

        if ($local == false) {
            // Reset the timezone of datetime in default
            $this->setTimezone($tz);
            // Reset the default timezone with the value of the server
            date_default_timezone_set(self::$datetimezone_server->getName());
        }

        return $return;
    }

    /**
     * Gets the date as UNIX time stamp.
     *
     * @return integer      The date as a UNIX timestamp.
     * @since 1.0
     */
    public function getTimestamp()
    {
        if (method_exists('DateTime', 'getTimestamp')) {
            return parent::getTimestamp();
        }

        return $this->toUnix();
    }

    /**
     * Alter the timestamp of a RsDate object by incrementing or decrementing in a format accepted by {@link http://php.net/manual/function.strtotime.php strtotime()}
     *
     * @param string    $modify     A date/time string.
     *
     * @return self
     * @since 1.0
     */
    public function modify($modify)
    {
        parent::modify($modify);

        return $this;
    }

    /**
     * Resets the current date of the RsDate object to a different date.
     *
     * @param int   $year   Year of the date.
     * @param int   $month  Month of the date.
     * @param int   $day    Day of the date.
     *
     * @return self
     * @since 1.0
     */
    public function setDate($year, $month, $day)
    {
        parent::setDate($year, $month, $day);

        return $this;
    }

    /**
     * Set a date according to the ISO 8601 standard - using weeks and day offsets rather than specific dates.
     *
     * @param int   $year   Year of the date.
     * @param int   $week   Week of the date.
     * @param int   $day    Offset from the first day of the week.
     *
     * @return self
     * @since 1.0
     */
    public function setISODate($year, $week, $day = 1)
    {
        parent::setISODate($year, $week, $day);

        return $this;
    }

    /**
     * Resets the current time of the self object to a different time.
     *
     * @param  int  $hour       Hour of the time.
     * @param  int  $minute     Minute of the time.
     * @param  int  $second     Second of the time.
     *
     * @return self
     * @since 1.0
     */
    public function setTime($hour, $minute, $second = 0, $microseconds = null)
    {
        parent::setTime($hour, $minute, $second, $microseconds);

        return $this;
    }

    /**
     * Sets the date and time based on an Unix timestamp
     *
     * @param int   $unixtimestamp  Unix timestamp representing the date.
     *
     * @return self
     * @since 1.0
     */
    public function setTimestamp($unixtimestamp)
    {
        if (method_exists('DateTime', 'setTimestamp')) {
            return parent::setTimestamp($unixtimestamp);
        }

        date_default_timezone_set('UTC');

        $date = getdate((int) $unixtimestamp);
        $this->setDate($date['year'], $date['mon'], $date['mday']);
        $this->setTime($date['hours'], $date['minutes'], $date['seconds']);

        date_default_timezone_set(self::$datetimezone_server->getName());

        return $this;
    }

    /**
     * Method to wrap the setTimezone() function and set the internal time zone object.
     *
     * @param mixed     $tz     Time zone to be used for the date (List of supported {@link http://php.net/manual/timezones.php timezones}).
     *
     * @return self
     * @since 1.0
     */
    public function setTimezone($tz)
    {
        // If the time zone object is not set, attempt to build it.
        if (!($tz instanceof DateTimeZone)) {
            $tz = new DateTimeZone($tz);
        }

        parent::setTimezone($tz);

        return $this;
    }

    /**
     * Gets the date as an ISO 8601 string.  IETF RFC 3339 defines the ISO 8601 format
     * and it can be found at the IETF Web site.
     *
     * @param boolean   $local  True to return the date string in the local time zone, false to return it in GMT.
     *
     * @return string   The date string in ISO 8601 format.
     * @since 1.0
     * @link http://www.ietf.org/rfc/rfc3339.txt
     */
    public function toISO8601($local = true)
    {
        return $this->format(self::RFC3339, $local);
    }

    /**
     * Gets the date as an SQL datetime string.
     *
     * @param boolean   $local  True to return the date string in the local time zone, false to return it in GMT.
     *
     * @return string   The date string in SQL datetime format.
     * @since 1.0
     * @link http://dev.mysql.com/doc/refman/5.0/en/datetime.html
     */
    public function toSql($local = true)
    {
        return $this->format('Y-m-d H:i:s', $local);
    }

    /**
     * Gets the date as an RFC 822 string.  IETF RFC 2822 supercedes RFC 822 and its definition
     * can be found at the IETF Web site.
     *
     * @param boolean   $local  True to return the date string in the local time zone, false to return it in GMT.
     *
     * @return string   The date string in RFC 822 format.
     * @since 1.0
     * @link http://www.ietf.org/rfc/rfc2822.txt
     */
    public function toRFC822($local = true)
    {
        return $this->format(self::RFC2822, $local);
    }

    /**
     * Gets the date as UNIX time stamp.
     *
     * @return integer  The date as a UNIX timestamp.
     * @since 1.0
     */
    public function toUnix()
    {
        return (int) $this->format('U');
    }
}