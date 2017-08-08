<?php
/**
 * The WeatherForecast class allows you to access current weather conditions and the next 5 days of accurate and reliable weather forecast. 
 * The Local Weather API returns weather elements such as temperature, precipitation (rainfall), weather description, weather icon and wind speed.
 * 
 * Credits to World Weather Online <http://www.worldweatheronline.com>
 * 
 * @category Weather Forecast
 * @author Giovanni Ramos <giovannilauro@gmail.com>
 * @copyright 2012, Giovanni Ramos
 * @since 2012-10-08
 * @version 1.1
 * 
 */
class WeatherForecast
{
    /**
     * API call
     * 
     * @access private static
     * @var array
     * 
     * */
    private static $request = array(
        'local' => 'http://api.worldweatheronline.com/premium/v1/weather.ashx'
    );
    /**
     * US metric
     * 
     * @access private static
     * @var boolean
     * 
     * */
    private static $us_metric = true;
    /**
     * Display error status
     * 
     * @access private static
     * @var boolean
     * 
     * */
    private static $display_error = true;
    /**
     * API Key
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $key;
    /**
     * Stores the return request
     * 
     * @access private static
     * @var object
     * 
     * */
    private static $doc;
    /**
     * City name
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $city;
    /**
     * Country name
     * 
     * @access private static
     * @var string
     * 
     * */
    private static $country;
    /**
     * Number of days to display
     * 
     * @access private static
     * @var int
     * 
     * */
    private static $num_of_days;
    /**
     * If the request has a weather forecast, is set to TRUE
     * 
     * @access public static
     * @var boolean
     * 
     * */
    public static $has_response = false;
    /**
     * Register to get your Key to access the API in:
     * http://developer.worldweatheronline.com/member/register/
     *
     * @param string $key API Key
     */
    public function __construct($key = null)
    {
        if ($key == 'YOUR_API_KEY')
            echo 'You have not defined an API key to get climate data.';
        self::setKey($key);
    }
    /**
     * Defines the API Key
     * 
     * @param string $key API Key
     */
    private static function setKey($key)
    {
        self::$key = (string) $key;
    }
    /**
     * Returns the API Key
     * 
     * @return string
     */
    private static function getKey()
    {
        return self::$key;
    }
    /**
     * Defines the US unit of measurement
     * 
     * @param boolean $us_metric US metric (default: TRUE)
     */
    public static function setUSMetric($us_metric = true)
    {
        self::$us_metric = (boolean) $us_metric;
    }
    /**
     * Defines the display of the error message on failure
     * 
     * @param boolean $display_error Display error status (default: TRUE)
     */
    public static function setDisplayError($display_error = true)
    {
        self::$display_error = (boolean) $display_error;
    }
    /**
     * Defines the name of the city
     * 
     * @param string $city City name
     */
    private static function setCity($city)
    {
        self::$city = (string) self::slug($city);
    }
    /**
     * Defines the name of the country
     * 
     * @param string $country Country name
     */
    private static function setCountry($country)
    {
        self::$country = (string) self::slug($country);
    }
    /**
     * Defines the number of days to display
     * 
     * @param int $num_of_days Number of days
     */
    private static function setNumOfDays($num_of_days)
    {
        self::$num_of_days = (int) $num_of_days;
    }
    /**
     * Defines the request parameters
     * 
     * @param string $city City name
     * @param string $country Country name
     * @param int $num_of_days Number of days to display: 1 to 5
     */
    public static function setRequest($city = null, $country = null, $num_of_days = 1)
    {
        // Sets the request parameters
        self::setCity($city);
        self::setCountry($country);
        self::setNumOfDays($num_of_days);
    }
    /**
     * Returns data from the weather
     * 
     * @param string $type_of_request Type of the URL of the request
     * @return SimpleXMLIterator Object
     */
    private function getRequest($type_of_request = 'local')
    {
				
        // Gets the unique key of the API
        $key = self::getKey();
        if (empty($key)) {
            echo 'The API key is missing.';
            return false;
        }
        // Gets the City name
        $city = self::$city;
        // Gets the Country name
        $country = self::$country ? ',' . self::$country : null;
        // Gets the Number of days to display
        $num_of_days = self::$num_of_days;
        // Checks if has been set the city
        if (empty($city)) {
            echo 'You can specify the location for any of these options: City and Town Name, IP Address, UK Postcode, Canada Postal Code, US Zipcode and Latitude and Longitude (in decimal).';
            return false;
        }
        // Stores the request URL
        $request = self::$request[$type_of_request] . '?q=' . $city . $country . '&format=xml&num_of_days=' . $num_of_days . '&key=' . $key;
        // Lifetime of the cache file, in seconds (1 hour = 3600)
        $cache_life = '0';
        // Cache file
        $cache_file = '../temp/cached-' . (substr($key, 0, 8) . '-' . $city . $country) . '.xml';
		
        // Generates the cache file if it does not exist or has expired life time
        $filemtime = @filemtime($cache_file);
        if (!$filemtime || (time() - $filemtime >= $cache_life)) {
            // Starts the request to cache
            $contents = @file_get_contents($request);
            // Reads the request and checks for errors
            $doc = simplexml_load_string($contents);
            // Does not make the file cache on error
            if (isset($doc->error)) {
                $doc_error = $doc->error;
                $msg = !empty($doc_error->msg) ? $doc_error->msg : 'Data could not be retrieved.';
                if (self::$display_error) {
                    echo '<strong style="color:red;">' . $msg . '</strong>';
                    return false;
                }
            } else {
                file_put_contents($cache_file, $contents);
            }
        }
        // Initiates the request
        if (filesize($cache_file) > 0) {
            $doc = simplexml_load_file($cache_file);
            self::$has_response = true;
            return self::$doc = $doc;
        } else {
            return null;
        }
    }
    /**
     * Returns the request
     * 
     * @return mixed
     */
    public function getLocalWeather()
    {
        $doc = self::getRequest('local');
        // Displays a message if no data returned
        if (!isset($doc->request)) {
            return false;
        }
        $locality = $doc->request->query;
        $current = $doc->current_condition;
        $new_doc = new stdClass();
        $new_doc->locality = (string) $locality . ' Weather in ' . date('d/m/Y', time());
        $new_doc->weather_now = array(
            "weatherTime" => (string) $current->observation_time,
            "weatherTemp" => (self::$us_metric ? $current->temp_F : $current->temp_C) . '&#176',
            "weatherDesc" => (string) $current->weatherDesc,
            "weatherCode" => (string) $current->weatherCode,
            "weatherIcon" => (string) $current->weatherIconUrl,
            "windDirection" => (string) $current->winddir16Point,
            "windSpeed" => (self::$us_metric ? $current->windspeedMiles . ' mph' : $current->windspeedKmph . 'km/h'),
            "precipitation" => (string) $current->precipMM, // Precipitation in millimetres
            "humidity" => (string) $current->humidity . '%', // Humidity in percentage
            "visibility" => (string) $current->visibility, // Visibility in kilometres
            "pressure" => (string) $current->pressure, // Atmospheric pressure in millibars
            "cloudcover" => (string) $current->cloudcover // Cloud cover in percentage
        );
        foreach ($doc->weather as $weather) {
            $ndate = strtotime($weather->date);
            $year = date('Y', $ndate);
            $month = date('M', $ndate);
            $day = date('d', $ndate);
            $weather_date = $month . ' ' . $day;
            $weather_day = mb_strtoupper(date('l', $ndate));
            $weather_desc = (string) $weather->weatherDesc;
            $weather_code = (string) $weather->weatherCode;
            $weather_icon = (string) $weather->weatherIconUrl;
            $weather_wdir = (string) $weather->winddir16Point;
            $weather_wind = (self::$us_metric ? $weather->windspeedMiles . ' mph' : $weather->windspeedKmph . 'km/h');
            $weather_max = (self::$us_metric ? $weather->tempMaxF : $weather->tempMaxC) . '&#176';
            $weather_min = (self::$us_metric ? $weather->tempMinF : $weather->tempMinC) . '&#176';
            $arr_weather[] = array(
                "weatherDate" => $weather_date,
                "weatherDay" => $weather_day,
                "weatherDesc" => $weather_desc,
                "weatherCode" => $weather_code,
                "weatherIcon" => $weather_icon,
                "windDirection" => $weather_wdir,
                "windSpeed" => $weather_wind,
                "tempMax" => $weather_max,
                "tempMin" => $weather_min
            );
            $new_doc->weather_forecast = (object) $arr_weather;
        }
        return $new_doc;
    }
    /**
     * Converts a string of special characters
     * 
     * @param string $term The input string
     * @return string
     */
    protected static function normalize($term)
    {
        if (is_array($term)) {
            foreach ($term as $value) {
                return normalize($value);
            }
        }
        $chars = array(
            'a' => '/Ã |Ã¡|Ã¢|Ã£|Ã¤|Ã¥|Ã¦/',
            'e' => '/Ã¨|Ã©|Ãª|Ã«/',
            'i' => '/Ã¬|Ã­|Ã®|Ä©|Ã¯/',
            'o' => '/Ã²|Ã³|Ã´|Ãµ|Ã¶|Ã¸/',
            'u' => '/Ã¹|Ãº|Ã»|Å±|Ã¼|Å¯/',
            'A' => '/Ã€|Ã�|Ã‚|Ãƒ|Ã„|Ã…|Ã†/',
            'E' => '/Ãˆ|Ã‰|ÃŠ|Ã‹/',
            'I' => '/ÃŒ|Ã�|ÃŽ|Ä¨|Ã�/',
            'O' => '/Ã’|Ã“|Ã”|Ã•|Ã–|Ã˜/',
            'U' => '/Ã™|Ãš|Ã›|Å¨|Ãœ|Å®/',
            '_' => '/`|Â´|\^|~|Â¨|Âª|Âº|Â©|Â®/',
            'c' => '/Ä‡|Ä‰|Ã§/',
            'C' => '/Ä†|Äˆ|Ã‡/',
            'd' => '/Ã°/',
            'D' => '/Ã�/',
            'n' => '/Ã±/',
            'N' => '/Ã‘/',
            'r' => '/Å•/',
            'R' => '/Å”/',
            's' => '/Å¡/',
            'S' => '/Å /',
            'y' => '/Ã½|Å·|Ã¿/',
            'Y' => '/Ã�|Å¶|Å¸/',
            'z' => '/Å¾/',
            'Z' => '/Å½/',
        );
        return preg_replace($chars, array_keys($chars), $term);
    }
    /**
     * Filters a string into a "friendly" string for use in URL's
     * 
     * @param string $term The input string
     * @return string
     */
    protected static function slug($term)
    {
        $term = mb_strtolower($term, 'UTF-8');
        $term = preg_replace('~[\s]+~', '+', $term);
        $term = self::normalize($term);
        return $term;
    }
    /**
     * Converts a string from Fahrenheit to Celsius
     * 
     * @param string $term The input string
     * @return string
     */
    protected function convertFtoC($term)
    {
        $term = ceil((($term - 32) / 9) * 5);
        return $term;
    }
}