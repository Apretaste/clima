<?php
//Import the weather API for OpenWeatherMap
use Cmfcmf\OpenWeatherMap;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;
require_once(__DIR__.'/weatherCache.php');

class Clima extends Service
{
	public $apiKey = "fdad9949d0a347811e8b84867ccd9707";

	/**
	 * Gets the most current weather forecast for Cuba
	 *
	 * @param Request
	 * @return Response
	 */
	public function _main(Request $request)
	{
		$cache = new OWMCache();
		$cache->setTempPath($this->utils->getTempDir());


		$owm = new OpenWeatherMap($this->apiKey, null, $cache, 3600*4); //Cache in seconds
		$lang='es';
		$units = 'metric';

		$province = ['PINAR_DEL_RIO' => '3544091',
								'LA_HABANA' => '3553478',
								'ARTEMISA' => '3568312',
								'MAYABEQUE' => '3539560', //San Jose de las Lajas
								'MATANZAS' => '3547398',
								'VILLA_CLARA' => '3537906', //Santa Clara
								'CIENFUEGOS' => '3564124',
								'SANCTI_SPIRITUS' => '3540667',
								'CIEGO_DE_AVILA' => '3564178',
								'CAMAGUEY' => '3566067',
								'LAS_TUNAS' => '3550598',
								'HOLGUIN' => '3556969',
								'GRANMA' => '3547600',
								'SANTIAGO_DE_CUBA' => '3536729',
								'GUANTANAMO' => '3557689',
								'ISLA_DE_LA_JUVENTUD' => '3545867']; //Nueva Gerona

		$dtz = new DateTimeZone("America/Havana"); //Your timezone
		$now = new DateTime(date("d-m-Y"), $dtz);

		if ($request->query != Null) {
			$code=$province[str_replace(" ", "_", $request->query)];
		}
		else {
			$person = $this->utils->getPerson($request->email);
			if ($person->province!= Null) {
				$code=$province[$person->province];
			}
			else {
				$code=$province['LA_HABANA'];
			}
		}

		try {
			$weather = $owm->getWeather($code, $units, $lang);
			$forecast = $owm->getWeatherForecast($code, $units, $lang, '', 1);

			$data= ['temperature' => $weather->temperature,
							'windDirection' => $this->traduce('direction',$weather->wind->direction->getDescription()),
							'windSpeed' => $weather->wind->speed,
							'precipitation' => $this->traduce('precipitation',$weather->precipitation->getDescription()),
							'humidity' => $weather->humidity,
							'pressure' => $weather->pressure,
							'sunrise' => $date = ((new DateTime('@' . $weather->sun->rise->getTimestamp()))->setTimezone($dtz))->format('h:m a'),
							'sunset' => $date = ((new DateTime('@' . $weather->sun->set->getTimestamp()))->setTimezone($dtz))->format('h:m a'),
							'clouds' => $this->traduce('clouds',$weather->clouds->getDescription()),
							'lastUpdate' => $date = ((new DateTime('@' . $weather->lastUpdate->getTimestamp()))->setTimezone($dtz))->format('h:m d/M/Y'),
							'city' => $weather->city->name,
							'now' => $now->format("d").' de '.$this->traduce('month',$now->format("F")).' del '.$now->format("Y"),
							'icon' => $this->traduce('icon',$weather->weather->icon),//$this->pathToService.'/images/'.$weather->weather->icon.'.png'
							'environment' => $request->environment
		];
		if ($data['city']=='Havana') {$data['city']="La Habana";}
		$fcast=array();
		foreach ($forecast as $w) {
			$fcast[]=['from' => $date = (new DateTime('@' . $w->time->from->getTimestamp()))->setTimezone($dtz),
											'to' => $date = (new DateTime('@' . $w->time->to->getTimestamp()))->setTimezone($dtz),
											'clouds' => $this->traduce('clouds',$w->clouds->getDescription()),
											'temperature' =>$w->temperature,
											'precipitation' => $this->traduce('precipitation',$w->precipitation->getDescription()),
											'icon' => $this->traduce('icon',$w->weather->icon)];//$this->pathToService.'/images/'.$w->weather->icon.'.png'];
		}
		} catch(OWMException $e) {
			$this->utils->createAlert('OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').',"ERROR");
			$response = new Response();
			$response->setResponseSubject("Error en peticion");
			$response->createFromText("Lo siento pero hemos tenido un error inesperado. Enviamos una peticion para corregirlo. Por favor intente nuevamente mas tarde.");
			return $response;
			//echo 'OpenWeatherMap exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
		} catch(\Exception $e) {
			$this->utils->createAlert('General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').',"ERROR");
			$response = new Response();
			$response->setResponseSubject("Error en peticion");
			$response->createFromText("Lo siento pero hemos tenido un error inesperado. Enviamos una peticion para corregirlo. Por favor intente nuevamente mas tarde.");
			return $response;
			//echo 'General exception: ' . $e->getMessage() . ' (Code ' . $e->getCode() . ').';
		}

		$response = new Response();
		$response->setCache(4);
		$response->setResponseSubject('El Clima Actual');
		$response->createFromTemplate('basic.tpl', array('data'=>$data,'fcast'=>$fcast));
		return $response;
	}

	/**
	 *
	 * @param String
	 * @param String
	 * @return String
	 */

	 public function traduce(String $type, String $text){

		 $clouds=['clear sky' => 'Cielo despejado',
			 				'scattered clouds' => 'Nubes dispersas',
							'few clouds' => 'Pocas nubes',
							'broken clouds' => 'Nubes fragmentadas',
							'overcast clouds' => 'Nublado'];

		 $direction=['Southwest' => 'Suroeste',
		 						 'SouthEast' => 'Sureste',
								 'Northwest' => 'Noroeste',
								 'NorthEast' => 'Noreste',
								 'East' => 'Este',
								 'South' => 'Sur',
								 'North' => 'Norte',
								 'West' => 'Oeste',
								 'North-northeast' => 'Norte-noreste',
								 'North-northwest' => 'Norte-noroeste',
								 'South-southeast' => 'Sur-sureste',
								 'South-southwest' => 'Sur-suroeste',
								 'West-southwest' => 'Oeste-noroeste',
								 'West-northwest' => 'Oeste-suroeste',
								 'East-southeast' =>'Este-sureste',
								 'East-southwest' =>'Este-suroeste',
							 	 'East-northeast' => 'Este-noreste',
							 	 'East-northwest' => 'Este-noroeste'];

				$icon=['01d' => '&#9728;',
							 '02d' => '&#9925;',
							 '03d' => '&#9729;',
							 '04d' => '&#9729;',
							 '09d' => '&#9748;',
							 '10d' => '&#9748;',
							 '11d' => '&#9928;',
							 '50d' => '&#9776;',
							 '01n' => '&#9790;',
							 '02n' => '&#9729;',
							 '03n' => '&#9729;',
							 '04n' => '&#9729;',
							 '09n' => '&#9748;',
							 '10n' => '&#9748;',
							 '11n' => '&#9928;',
							 '50n' => '&#9776;'];

				$month=['Jan' => 'Enero',
								'Feb' => 'Febrero',
								'Mar' => 'Marzo',
								'Apr' => 'Abril',
								'May' => 'Mayo',
								'Jun' => 'Junio',
								'Jul' => 'Julio',
								'Aug' => 'Agosto',
								'Sep' => 'Septiembre',
								'Oct' => 'Octubre',
								'Nov' => 'Noviembre',
								'Dec' => 'Diciembre'];

		 switch ($type) {
		 	case 'clouds':
		 		return $clouds[$text];
		 		break;

			case 'direction':
				return $direction[$text];
				break;

			case 'precipitation':
				if($text=='rain') {return 'Lluvioso';}
				else {return 'no';}
				break;

			case 'icon':
				return $icon[$text];
				break;

			case 'month':
				return $month[$text];
				break;

		 	default:
		 		# code...
		 		break;
		 }
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
		$url = "http://images.intellicast.com/WxImages/Satellite/hicbsat.gif";
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
			if ($this->getUrl($urlx) !== false)
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
		return $this->commonImageResponse("Imagen del Caribe (Weather Channel)", "http://sirocco.accuweather.com/sat_mosaic_640x480_public/ei/isaecar.gif");
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
		"http://images.intellicast.com/WxImages/CustomGraphic/HurTrack3.gif"
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
	 * Remote get contents
	 *
	 * @param $url
	 * @param array $info
	 * @return mixed
	 */
	private function getUrl($url, &$info = [])
	{
		$url = str_replace("//", "/", $url);
		$url = str_replace("http:/","http://", $url);
		$url = str_replace("https:/","https://", $url);

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);

		$default_headers = [
			"Cache-Control" => "max-age=0",
			"Origin" => "{$url}",
			"User-Agent" => "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36",
			"Content-Type" => "application/x-www-form-urlencoded"
		];

		$hhs = [];
		foreach ($default_headers as $key => $val)
			$hhs[] = "$key: $val";

		curl_setopt($ch, CURLOPT_HTTPHEADER, $hhs);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

		$html = curl_exec($ch);
		$info = curl_getinfo($ch);

		if (isset($info['redirect_url']) && $info['redirect_url'] != $url && !empty($info['redirect_url']))
			return $this->getUrl($info['redirect_url'], $info);

		curl_close($ch);

		return $html;
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
		$filePath = "$wwwroot/temp/" . $this->utils->generateRandomHash(); //. ".jpg";
		$info = [];
		$content = $this->getUrl($url, $info);

		if ($content == false) return false;

		$sinfo = serialize($info);
		$ext = 'jpg';

		if (stripos($sinfo, 'image/gif') !== false) $ext = 'gif';
		if (stripos($sinfo, 'image/webp') !== false) $ext = 'webp';
		if (stripos($sinfo, 'image/bmp') !== false) $ext = 'bmp';
		if (stripos($sinfo, 'image/png') !== false) $ext = 'png';

		$filePath .= ".$ext";
		file_put_contents($filePath, $content);

		$type = $this->getFileType($filePath);
		if (strtolower(trim(substr($type,0,6) != 'image/'))) return false;

		// return the path to the image
		return $filePath;
	}

	/**
	 * Return file info
	 *
	 * @param $filename
	 * @return array|mixed|string
	 */
	public function getFileType($filename)
	{
		$finfo = finfo_open(FILEINFO_MIME); // return mime type ala mimetype extension

		if (!$finfo) return '';

		/* get mime-type for a specific file */
		$type = finfo_file($finfo, $filename);

		$type = explode(";", $type);
		$type = trim($type[0]);

		/* close connection */
		finfo_close($finfo);

		return $type;
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
