<?php

use Cmfcmf\OpenWeatherMap;
use Http\Factory\Guzzle\RequestFactory;
use Http\Adapter\Guzzle6\Client as GuzzleAdapter;
use Cmfcmf\OpenWeatherMap\Exception as OWMException;
use Apretaste\Request;
use Apretaste\Response;
use Apretaste\Challenges;
use Apretaste\Notifications;
use Framework\Alert;
use Framework\Utils;
use Framework\Config;
use Framework\Crawler;
use Framework\Database;

class Service
{
	/**
	 * Gets the most current weather forecast for Cuba
	 */
	public function _main(Request $request, Response $response)
	{
		// create the Open Weather Map class
		$httpRequestFactory = new RequestFactory();
		$httpClient = GuzzleAdapter::createWithConfig([]);
		$apiKey = Config::pick('openweather')['apikey'];
		$owm = new OpenWeatherMap($apiKey, $httpClient, $httpRequestFactory, null, 3600 * 4);

		// prepare province codes
		$province = [
			'PINAR_DEL_RIO' => '3544091',
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
			'GRANMA' => '3567597',
			'SANTIAGO_DE_CUBA' => '3536729',
			'GUANTANAMO' => '3557689',
			'ISLA_DE_LA_JUVENTUD' => '3545867', // Nueva Gerona
		];

		// your timezone
		$dtz = new DateTimeZone('America/Havana'); 
		
		// set default province
		$customProvince = 'LA_HABANA';
		if (isset($province[$request->person->provinceCode])) $customProvince = $request->person->provinceCode;
		$customProvince = $request->input->data->query->province ?? $customProvince; // change if user select another
		$customProvince = strtoupper(str_replace(' ', '_', $customProvince)); // normalize the value
		$code = $province[$customProvince] ?? $province['LA_HABANA'];

		try {
			// get weather and forecast
			$weather = $owm->getWeather($code, 'metric', 'es');
			$forecast = $owm->getWeatherForecast($code, 'metric', 'es', '', 1);

			// create details for today
			$data = [
				'temperature' => $weather->temperature->getFormatted(),
				'windDirection' => $this->translate('direction', $weather->wind->direction->getDescription()),
				'windSpeed' => $weather->wind->speed->getFormatted(),
				'precipitation' => $this->translate('precipitation', $weather->precipitation->getDescription()),
				'humidity' => $weather->humidity->getFormatted(),
				'pressure' => $weather->pressure->getFormatted(),
				'sunrise' => $date = (new DateTime('@'.$weather->sun->rise->getTimestamp()))->setTimezone($dtz)->format('h:m a'),
				'sunset' => $date = (new DateTime('@'.$weather->sun->set->getTimestamp()))->setTimezone($dtz)->format('h:m a'),
				'clouds' => $this->translate('clouds', $weather->clouds->getDescription()),
				'lastUpdate' => $date = (new DateTime('@'.$weather->lastUpdate->getTimestamp()))->setTimezone($dtz)->format('h:m d/M/Y'),
				'city' => $weather->city->name,
				'icon' => $this->translate('icon', $weather->weather->icon),
			];

			// rename city
			if ($data['city'] === 'Havana') {
				$data['city'] = 'La Habana';
			}

			// create forecast
			$fCast = [];
			foreach ($forecast as $w) {
				$fCast[] = [
					'from' => $date = (new DateTime('@'.$w->time->from->getTimestamp()))->setTimezone($dtz),
					'to' => $date = (new DateTime('@'.$w->time->to->getTimestamp()))->setTimezone($dtz),
					'clouds' => $this->translate('clouds', $w->clouds->getDescription()),
					'temperature' => $w->temperature->getFormatted(),
					'precipitation' => $this->translate('precipitation', $w->precipitation->getDescription()),
					'icon' => $this->translate('icon', $w->weather->icon),
				];
			}
		} catch (OWMException | Exception $e) {
			return $response->setTemplate('message.ejs', [
				'header' => html_entity_decode('Error en petición'),
				'icon' => 'sentiment_dissatisfied',
				'text' => 'Hemos tenido un error inesperado y enviamos una petición para corregirlo. Por favor intente nuevamente más tarde.',
				'button' => ['href'=>'CLIMA', 'caption'=>'Regresar']
			]);
		}

		// challenges
		Challenges::complete("view-clima", $request->person->id);

		// create content for the view
		$content = [
			'data' => $data,
			'fcast' => $fCast,
			'icon' => 'umbrella',
			'provinces' => array_keys($province)
		];

		// send data to the view
		$response->setLayout('clima.ejs');
		$response->setTemplate('basic.ejs', $content);
	}

	/**
	 * Subservice satelite
	 */
	public function _satelite(Request $request, Response $response)
	{
		$this->commonImageResponse(
			'Imagen del satélite', 
			'http://images.intellicast.com/WxImages/Satellite/hicbsat.gif', 
			'satellite', 
			$response, 
			'SATELITE');
	}

	/**
	 * Subservice atlantico
	 */
	public function _atlantico(Request $request, Response $response)
	{
		$this->commonImageResponse(
			'Análisis del estado del Atlántico (NOAA/NHC)', 
			'http://www.nhc.noaa.gov/tafb_latest/atlsea_latestBW.gif', 
			'water', 
			$response, 
			'ATLANTICO');
	}

	/**
	 * Subservice caribe
	 */
	public function _caribe(Request $request, Response $response)
	{
		$this->commonImageResponse(
			'Imagen del Caribe (Weather Channel)', 
			'http://sirocco.accuweather.com/sat_mosaic_640x480_public/ei/isaecar.gif', 
			'tree', 
			$response, 
			'CARIBE');
	}

	/**
	 * Subservice presion
	 */
	public function _presion(Request $request, Response $response)
	{
		$this->commonImageResponse(
			'Presión superficial', 
			'http://www.nhc.noaa.gov/tafb_latest/WATL_latest.gif', 
			'thermometer-three-quarters', 
			$response, 
			'PRESION');
	}

	/**
	 * Subservice huracan
	 */
	public function _huracan(Request $request, Response $response)
	{
		$this->commonImageResponse(
			'Cono de trayectoria huracán',
			'http://images.intellicast.com/WxImages/CustomGraphic/HurTrack1.gif',
			'wind',
			$response,
			'HURACAN');
	}

	/**
	 * Common response
	 *
	 * @param string $title
	 * @param string $url
	 * @param string $floatIcon
	 * @param \Apretaste\Response $response
	 * @param string $command
	 * @return void
	 * @throws Alert
	 * @author kuma
	 */
	private function commonImageResponse($title, $url, $floatIcon='cloud_queue', Response $response, $command='')
	{
		// download and prepare the image
		$image = $this->downloadAndPrepareImage($url);

		// error if fails
		if ($image === false) {
			return $response->setTemplate('message.ejs', [
				'header' => html_entity_decode('Hubo problemas al atender tu solicitud'),
				'icon' => '',
				'text' => html_entity_decode("No hemos podido resolver su solicitud: <b>{$title}</b>. Intente más tarde y si el problema persiste contacta con el soporte t&eacute;cnico."),
				'button' => ['href' => 'clima', 'caption' => 'Regresar']
			]);
		}

		// create content for the view
		$content = [
			'title' => $title, 
			'image' => basename("$image"), 
			'icon' => $floatIcon, 
			'command' => $command
		];

		// send data to the view
		$response->setLayout('clima.ejs');
		$response->setTemplate('image.ejs', $content, [$image]);
	}

	/**
	 * Translate
	 *
	 * @param String
	 * @param String
	 * @return String
	 */
	public function translate(String $type, String $text): string
	{
		$clouds = [
			'clear sky' => 'Cielo despejado',
			'scattered clouds' => 'Nubes dispersas',
			'few clouds' => 'Pocas nubes',
			'broken clouds' => 'Nubes fragmentadas',
			'overcast clouds' => 'Nublado',
		];

		$direction = [
			'Southwest' => 'Suroeste',
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
			'East-southeast' => 'Este-sureste',
			'East-southwest' => 'Este-suroeste',
			'East-northeast' => 'Este-noreste',
			'East-northwest' => 'Este-noroeste',
		];

		$icon = [
			'01d' => 'sun',
			'02d' => 'cloud-sun',
			'03d' => 'cloud',
			'04d' => 'smog',
			'09d' => 'cloud-rain',
			'10d' => 'cloud-showers-heavy',
			'11d' => 'poo-storm',
			'13d' => 'snowflake',
			'50d' => 'water',
			'01n' => 'sun',
			'02n' => 'cloud-sun',
			'03n' => 'cloud',
			'04n' => 'smog',
			'09n' => 'cloud-rain',
			'10n' => 'cloud-showers-heavy',
			'11n' => 'poo-storm',
			'13n' => 'snowflake',
			'50n' => 'water',
		];

		$month = [
			'January' => 'Enero',
			'February' => 'Febrero',
			'March' => 'Marzo',
			'April' => 'Abril',
			'May' => 'Mayo',
			'June' => 'Junio',
			'July' => 'Julio',
			'August' => 'Agosto',
			'September' => 'Septiembre',
			'October' => 'Octubre',
			'November' => 'Noviembre',
			'December' => 'Diciembre',
		];

		switch ($type) {
			case 'clouds':
				if (isset($clouds[$text])) {
					return $clouds[$text];
				}
				break;

			case 'direction':
				if (isset($direction[$text])) {
					return $direction[$text];
				}
				break;

			case 'precipitation':
				return ($text == 'rain') ? 'Lluvioso' : 'no';
				break;

			case 'icon':
				return isset($icon[$text]) ? $icon[$text] : 'question';
				break;

			case 'month':
				if (isset($month[$text])) {
					return $month[$text];
				}
				break;

			default:
				# code...
				break;
		}

		return $text;
	}

	/**
	 * Download, resize and optimize the image
	 *
	 * @param String $url url of the image
	 * @return String path to the image
	 * @throws \Exception
	 */
	private function downloadAndPrepareImage($url)
	{
		// save image to the temp folder
		$filePath = TEMP_PATH . Utils::randomHash();
		$info = [];
		$content = Crawler::get($url, 'GET', null, [], [], $info);

		if ($content === false) {
			return false;
		}

		$ext = pathinfo($url, PATHINFO_EXTENSION);
		$sinfo = serialize($info);

		if (stripos($sinfo, 'image/gif') !== false) {
			$ext = 'gif';
		}
		if (stripos($sinfo, 'image/webp') !== false) {
			$ext = 'webp';
		}
		if (stripos($sinfo, 'image/bmp') !== false) {
			$ext = 'bmp';
		}
		if (stripos($sinfo, 'image/png') !== false) {
			$ext = 'png';
		}

		$filePath .= ".$ext";
		file_put_contents($filePath, $content);

		$type = $this->getFileType($filePath);
		if (strtolower(trim(strpos($type, 'image/') !== 0))) {
			return false;
		}

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

		if (!$finfo) {
			return '';
		}

		/* get mime-type for a specific file */
		$type = finfo_file($finfo, $filename);

		$type = explode(';', $type);
		$type = trim($type[0]);

		/* close connection */
		finfo_close($finfo);

		return $type;
	}
}
