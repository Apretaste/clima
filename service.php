<?php

class Clima extends Service
{
	public $apiKey = "1790da4c17644e238be34332170508";

	/**
	 * Gets the most current weather forecast for Cuba
	 *
	 * @param Request
	 * @return Response
	 */
	public function _main(Request $request)
	{
		// include the weather channel library
		include_once "{$this->pathToService}/lib/WeatherForecast.php";

		$argument = $request->query;
		$argument = trim($argument);
		$argument = trim(strtolower($argument));

		$weather = array();
		$images = array();
		$imagesya = array();

		$places_cuba = explode(",","La Habana,Pinar del Rio,Artemisa,Batabano,Varadero,Matanzas,Santa Clara,Cienfuegos,Sancti Spiritus,Trinidad,Camaguey,Ciego de Avila,Las Tunas,Holguin,Bayamo,Santiago de Cuba,Guantanamo");

		$places = $places_cuba;
		$country = 'Cuba';

		if (trim($argument) != '')
		{
			$arr = explode(',', $argument);
			$arr[0] = trim($arr[0]);

			if (isset($arr[1])) $arr[1] = trim($arr[1]); else $arr[1] = '';

			$city = $arr[0];
			$country = $arr[1];

			if ("$country" == '') $country = false;

			if ("$city" == '' && "$country" != '')
			{
				$city = $country;
				$country = false;
			}

			$places = array($city);
		}


		if ($country != 'Cuba') {
			$r = new WeatherForecast($this->apiKey);
			$x = $r->setRequest($places[0], $country, 3);
			if ($x === false) {
				$places = $places_cuba;
				$country = 'Cuba';
			}
		}

		$i = 0;

		// get the weather information for each province
		foreach ($places  as $place)
		{
			// get the weather forecast
			$r = new WeatherForecast($this->apiKey);
			$r->setRequest($place, $country, 3);
			$r->setUSMetric(false);
			$r = @$r->getLocalWeather();

			if ( ! $r) continue;

			$i++;
			
			// get weather details for today
			$today = new stdClass();
			$today->location = $place;
			$today->time = $r->weather_now['weatherTime'];
			$today->temperature = $r->weather_now['weatherTemp'];
			$today->description = $this->getDescriptionBasedOnCode($r->weather_now['weatherCode']);
			$today->icon = $this->getImageBasedOnCode($r->weather_now['weatherCode']);

			if (!isset($imagesya[$today->icon]))
			{
				$imagesya[$today->icon] = true;
				$images[] = $today->icon;
			}

			$today->windDirection = $r->weather_now['windDirection'];
			$today->windSpeed = $r->weather_now['windSpeed'];
			$today->precipitation = $r->weather_now['precipitation'];
			$today->humidity = $r->weather_now['humidity'];
			$today->visibility = $r->weather_now['visibility'];
			$today->pressure = $r->weather_now['pressure'];
			$today->cloudcover = $r->weather_now['cloudcover'];

			// get weather details for next 3 days
			$days = array();
			foreach ($r->weather_forecast as $w)
			{
				$day = new stdClass();
				$day->date = $w['weatherDate'];
				$day->weekday = $this->translate($w['weatherDay']);
				$day->description = $this->getDescriptionBasedOnCode($w['weatherCode']);
				$day->icon = $this->getImageBasedOnCode($w['weatherCode']);

				if (!isset($imagesya[$day->icon])){
					$imagesya[$day->icon] = true;
					$images[] = $day->icon;
				}
				$day->windDirection = $w['windDirection'];
				$day->windSpeed = $w['windSpeed'];
				$day->tempMax = $w['tempMax'];
				$day->tempMin = $w['tempMin'];
				$days[] = $day;
			}

			// add days to the final result
			$today->days = $days;
			$weather[] = $today;
		}

		if ($i == 0) {
			return $this->_huracan($request);
		}
		
		// create the date of today
		$d = date("d/m/Y h:i a");
		$d = str_replace(["/0", " 0"], ["/", " "], $d);
		if ($d[0] == "0") $d = substr($d, 1);

		// return response
		$response = new Response();
		$response->setCache("day");
		$response->setResponseSubject("El Clima");
		$response->createFromTemplate("basic.tpl", array("weather"=>$weather, "today" => $d), $images);
		return $response;
	}

	/**
	 * Subservice satelite
	 *
	 * @param Request
	 * @return Response
	 */
	public function _satelite(Request $request)
	{
		/*
		// get the url to the image
		$url = false;
		foreach (array("gif","jpg","png","jpeg") as $ext)
		{
			$f = date("Ymd") . '1.' . $ext;
			$url = "http://tiempo.cuba.cu/images/$f";
			$img = @file_get_contents($url);

			if ($img === false)
			{
				$f = date("Ymd", time() - 60 * 60 * 24) . '1.' . $ext;
				$url = "http://tiempo.cuba.cu/images/$f";
				$img = @file_get_contents($url);

				if ($img === false)
				{
					$f = date("Ymd", time() - 60 * 60 * 24 * 2) . '1.' . $ext;
					$url = "http://tiempo.cuba.cu/images/$f";
					$img = @file_get_contents($url);
				}
			}
			if ($img !== false) break;
		}

		// TODO: save last radar image on cache for future problems?
		if ($url === false){
			$response = new Response();
			$response->setCache("day");
			$response->setResponseSubject("Clima: no se pudo obtener la imagen del sat&eacute;lite");
			$response->createFromText("No se pudo obtener la imagen del sat&eacute;lite, intente m&aacute;s tarde");
			return $response;
		}
		*/
		$url = "http://images.intellicast.com/WxImages/Satellite/hiatlsat.gif";
		return $this->commonImageResponse("Imagen del sat&eacute;lite", $url);
	}


	/**
	 * Subservice radar
	 *
	 * @param Request
	 * @return Response
	 */
	public function _radar(Request $request)
	{
		$radares = array(
		"http://www.met.inf.cu/Radar/NacComp200Km.gif", // mosaico
		"http://www.met.inf.cu/Radar/03Cienfuegos/psjMAXw01a.gif", // Pico san juan
		"http://www.met.inf.cu/Radar/04Camaguey/cmwMAXw01a.gif", // Camaguey
		"http://www.met.inf.cu/Radar/05Pilon/plnMAXw01a.gif", // Pilon,
		"http://www.met.inf.cu/Radar/00Pinar%20del%20Rio/lbjMAXw01a.gif" // Pinar del rio
		);

		$url = false;

		foreach ($radares as $urlx)
			if (@file_get_contents($urlx) !== false)
			{
				$url = $urlx;
				break;
			}

		// TODO: save last radar image on cache for future problems?
		if ($url === false)
		{
			$response = new Response();
			$response->setCache("day");
			$response->setResponseSubject("Clima: No se pudo obtener la imagen del radar");
			$response->createFromText("No se pudo obtener la imagen del radar, intente m&aacute;s tarde");
			return $response;
		}

		return $this->commonImageResponse("Imagen del radar", $url);
	}


	/**
	 * Subservice nasa
	 *
	 * @param Request
	 * @return Response
	 */
	public function _nasa(Request $request)
	{
		return $this->commonImageResponse("Imagen de la NASA", "http://goes.gsfc.nasa.gov/goescolor/goeseast/hurricane2/color_med/latest.jpg");
	}


	/**
	 * Subservice temperatura
	 *
	 * @param Request
	 * @return Response
	 */
	public function _temperatura(Request $request)
	{
		return $this->commonImageResponse("An&aacute;lisis de la temperatura del mar (NOAA/NHC)","http://polar.ncep.noaa.gov/sst/ophi/nwatl_sst_ophi0.png");
	}


	/**
	 * Subservice superficie
	 *
	 * @param Request
	 * @return Response
	 */
	public function _superficie(Request $request)
	{
		return $this->commonImageResponse("An&aacute;lisis de superficie del Atl&aacute;ntico y el Caribe (NOAA/NHC)","http://dadecosurf.com/images/tanal.1.gif");
	}


	/**
	 * Subservice atlantico
	 *
	 * @param Request
	 * @return Response
	 */
	public function _atlantico(Request $request)
	{
		return $this->commonImageResponse("An&aacute;lisis del estado del Atl&aacute;ntico (NOAA/NHC)", "http://www.nhc.noaa.gov/tafb_latest/atlsea_latestBW.gif");
	}


	/**
	 * Subservice caribe
	 *
	 * @param Request
	 * @return Response
	 */
	public function _caribe(Request $request)
	{
		return $this->commonImageResponse("Imagen del Caribe (Weather Channel)", "http://image.weather.com/images/sat/caribsat_600x405.jpg");
	}

	/**
	 * Subservice sector
	 *
	 * @param Request
	 * @return Response
	 */
	public function _sector(Request $request)
	{
		return $this->commonImageResponse("Imagen del Sector Visible", "http://www.goes.noaa.gov/GIFS/HUVS.JPG");
	}


	/**
	 * Subservice infrarroja
	 *
	 * @param Request
	 * @return Response
	 */
	public function _infrarroja(Request $request)
	{
		return $this->commonImageResponse("Imagen infrarroja", "http://www.goes.noaa.gov/GIFS/HUIR.JPG");
	}


	/**
	 * Subservice vapor
	 *
	 * @param Request
	 * @return Response
	 */
	public function _vapor(Request $request)
	{
		return $this->commonImageResponse("Imagen del Vapor de Agua", "http://www.goes.noaa.gov/GIFS/HUWV.JPG");
	}


	/**
	 * Subservice polvo
	 *
	 * @param Request
	 * @return Response
	 */
	public function _polvo(Request $request)
	{
		return $this->commonImageResponse("Imagen del Polvo del desierto", "http://tropic.ssec.wisc.edu/real-time/sal/splitEW.jpg");
	}


	/**
	 * Subservice presion
	 *
	 * @param Request
	 * @return Response
	 */
	public function _presion(Request $request)
	{
		return $this->commonImageResponse("Presi&oacute;n superficial", "http://www.nhc.noaa.gov/tafb_latest/WATL_latest.gif");
	}

	
	public function _huracan(Request $request) 
	{
		return $this->commonImageResponse("Cono de trayectoria huracan", //"http://www.met.inf.cu/Pronostico/Aviso/cono.jpg"
		"http://images.intellicast.com/WxImages/CustomGraphic/HurTrack2.gif"
		);		
	}
	/**
	 * Common response
	 *
	 * @author kuma
	 * @param string $title
	 * @param string $url
	 * @return Response
	 */
	private function commonImageResponse($title, $url)
	{
		$response = new Response();

		// download and prepare the image
		$image = $this->downloadAndPrepareImage($url);

		if ($image === false)
		{
			$response->setResponseSubject("Clima: Hubo problemas al atender tu solicitud");
			$response->createFromText("No hemos podido resolver su solicitud: <b>{$title}</b>. Intente m&aacute;s tarde y si el problema persiste contacta con el soporte t&eacute;cnico.");
			return $response;
		}

		// create response
		$response->setCache("day");
		$response->setResponseSubject("Clima: ".html_entity_decode($title));
		$response->createFromTemplate("image.tpl", array("title" => $title, "image" => "cid:$image"), array($image));
		return $response;
	}


	/**
	 * Returns the description in Spanish, based on the code
	 *
	 */
	private function getDescriptionBasedOnCode($code)
	{
		$description = array(
			395 => 'Nieve moderada o fuerte en area con truenos',
			392 => 'Nieve moderada tormentosas',
			389 => 'Lluvia moderada o fuerte en area con truenos',
			386 => 'Intervalos de lluvias tormentosas',
			377 => 'Lluvias moderadas o fuerte de granizo',
			374 => 'Lluvias ligeras de granizos de hielo',
			371 => 'Nieve moderada o fuerte',
			368 => 'Lluvias ligeras',
			365 => 'Aguanieve moderada o fuerte',
			362 => 'Aguanieve ligera',
			359 => 'Torrencial lluvia',
			356 => 'Lluvia moderada o abundante',
			353 => 'Moderada o fuerte lluvia',
			350 => 'Granizos de hielo',
			338 => 'Fuertes nevadas',
			335 => 'Nubes y nieve pesada',
			332 => 'Nieve moderada',
			329 => 'Nubes y nieve moderada',
			326 => 'Poca nieve',
			323 => 'Nieve moderada',
			320 => 'Aguanieve moderada o fuerte',
			317 => 'Aguanieve',
			314 => 'Lluvia moderada o fuerte de congelaci&oacute;n',
			311 => 'Lluvia helada Luz',
			308 => 'Fuertes lluvias',
			305 => 'Lluvia ligera, a veces',
			302 => 'Lluvia moderada',
			299 => 'Lluvia ligera, a veces',
			296 => 'Lluvia ligera',
			293 => 'Lluvia moderada irregular',
			284 => 'Llovizna de congelaci&oacute;n fuerte',
			281 => 'Llovizna helada',
			266 => 'Llovizna ligera ',
			263 => 'Llovizna moderada',
			260 => 'Niebla de congelaci&oacute;n',
			248 => 'Niebla',
			230 => 'Ventisca',
			227 => 'Chubascos de nieve',
			200 => 'Brotes de lluvia moderada',
			185 => 'Llovizna de congelación y nubes en las inmediaciones',
			182 => 'Nubes y aguanieve en las inmediaciones',
			179 => 'Nubes y nieve en las inmediaciones',
			176 => 'Lluvia moderada en las inmediaciones',
			143 => 'Neblina',
			122 => 'Nublado',
			119 => 'Nublado',
			116 => 'Parcialmente nublado',
			113 => 'Despejado'
		);
		if (!isset($description[$code]))
			return "";

		return $description[$code];
	}


	/**
	 * Returns the image based on the code
	 *
	 */
	private function getImageBasedOnCode($code)
	{
		/*
		$images = array(
			395 => 'wsymbol_0011_light_snow_showers.jpg',
			392 => 'wsymbol_0011_light_snow_showers.jpg',
			389 => 'wsymbol_0009_light_rain_showers.jpg',
			386 => 'wsymbol_0010_heavy_rain_showers.jpg',
			377 => 'wsymbol_0015_heavy_hail_showers.jpg',
			374 => 'wsymbol_0014_light_hail_showers.jpg',
			371 => 'wsymbol_0012_heavy_snow_showers.jpg',
			368 => 'wsymbol_0009_light_rain_showers.jpg',
			365 => 'wsymbol_0013_sleet_showers.jpg',
			362 => 'wsymbol_0013_sleet_showers.jpg',
			359 => 'wsymbol_0010_heavy_rain_showers.jpg',
			356 => 'wsymbol_0009_light_rain_showers.jpg',
			353 => 'wsymbol_0009_light_rain_showers.jpg',
			350 => 'wsymbol_0014_light_hail_showers.jpg',
			338 => 'wsymbol_0036_cloudy_with_heavy_snow_night.jpg',
			335 => 'wsymbol_0036_cloudy_with_heavy_snow_night.jpg',
			332 => 'wsymbol_0011_light_snow_showers.jpg',
			329 => 'wsymbol_0035_cloudy_with_light_snow_night.jpg',
			326 => 'wsymbol_0011_light_snow_showers.jpg',
			323 => 'wsymbol_0011_light_snow_showers.jpg',
			320 => 'wsymbol_0013_sleet_showers.jpg',
			317 => 'wsymbol_0013_sleet_showers.jpg',
			314 => 'wsymbol_0016_thundery_showers.jpg',
			311 => 'wsymbol_0013_sleet_showers.jpg',
			308 => 'wsymbol_0010_heavy_rain_showers.jpg',
			305 => 'wsymbol_0009_light_rain_showers.jpg',
			302 => 'wsymbol_0009_light_rain_showers.jpg',
			299 => 'wsymbol_0009_light_rain_showers.jpg',
			296 => 'wsymbol_0009_light_rain_showers.jpg',
			293 => 'wsymbol_0009_light_rain_showers.jpg',
			284 => 'wsymbol_0013_sleet_showers.jpg',
			281 => 'wsymbol_0013_sleet_showers.jpg',
			266 => 'wsymbol_0009_light_rain_showers.jpg',
			263 => 'wsymbol_0009_light_rain_showers.jpg',
			260 => 'wsymbol_0036_cloudy_with_heavy_snow_night.jpg',
			248 => 'wsymbol_0004_black_low_cloud.jpg',
			230 => 'wsymbol_0016_thundery_showers.jpg',
			227 => 'wsymbol_0013_sleet_showers.jpg',
			200 => 'wsymbol_0009_light_rain_showers.jpg',
			185 => 'wsymbol_0009_light_rain_showers.jpg',
			182 => 'wsymbol_0035_cloudy_with_light_snow_night.jpg',
			179 => 'wsymbol_0035_cloudy_with_light_snow_night.jpg',
			176 => 'wsymbol_0009_light_rain_showers.jpg',
			143 => 'wsymbol_0007_fog.jpg',
			122 => 'wsymbol_0004_black_low_cloud.jpg',
			119 => 'wsymbol_0004_black_low_cloud.jpg',
			116 => 'wsymbol_0002_sunny_intervals.jpg',
			113 => 'wsymbol_0001_sunny.jpg'
		);
*/
			$images = array(
			395 => 9928,
			392 => 9731,
			389 => 9928,
			386 => 9928,
			377 => 9748,
			374 => 9748,
			371 => 9731,
			368 => 9748,
			365 => 9748,
			362 => 9748,
			359 => 9928,
			356 => 9928,
			353 => 9748,
			350 => 9731,
			338 => 9731,
			335 => 9731,
			332 => 9731,
			329 => 9731,
			326 => 9731,
			323 => 9731,
			320 => 9731,
			317 => 9731,
			314 => 9925,
			311 => 9731,
			308 => 9748,
			305 => 9730,
			302 => 9748,
			299 => 9730,
			296 => 9730,
			293 => 9730,
			284 => 9731,
			281 => 9731,
			266 => 9730,
			263 => 9730,
			260 => 9730,
			248 => 9729,
			230 => 9748,
			227 => 9731,
			200 => 9730,
			185 => 9748,
			182 => 9729,
			179 => 9729,
			176 => 9748,
			143 => 9729,
			122 => 9729,
			119 => 9729,
			116 => 9925,
			113 => 9728
		);
		if (!isset($images[$code]))
		//	return "{$this->pathToService}/images/wsymbol_0001_sunny.jpg";
			return 9728;

		return $images[$code];
	}


	 /**
	 * Download, resize and optimize the image
	 *
	 * @param String $url url of the image
	 * @return String path to the image
	 */
	private function downloadAndPrepareImage($url)
	{
		$di = \Phalcon\DI\FactoryDefault::getDefault();
		$wwwroot = $di->get('path')['root'];

		// save image to the temp folder
		$filePath = "$wwwroot/temp/" . $this->utils->generateRandomHash() . ".jpg";
		$content = @file_get_contents($url);
		if ($content == false) return false;
		file_put_contents($filePath, $content);

		// optimize the image
		$this->utils->optimizeImage($filePath, 400);

		// return the path to the image
		return $filePath;
	}

	/**
	  * Translate user interface
	  *
	  * @author kuma
	  * @param string $word
	  */
	private function translate($word)
	{
		$i18n = array(
			'MONDAY' => 'Lunes',
			'TUESDAY' => 'Martes',
			'WEDNESDAY' => 'Mi&eacute;rcoles',
			'THURSDAY' => 'Jueves',
			'FRIDAY' => 'Viernes',
			'SATURDAY' => 'S&aacute;bado',
			'SUNDAY' => 'Domingo'
		);

		if (isset($i18n[$word])) return $i18n[$word];
		if (isset($i18n[strtoupper($word)])) return $i18n[strtoupper($word)];
		if (isset($i18n[strtolower($word)])) return $i18n[strtolower($word)];
		return $word;
	}
}
