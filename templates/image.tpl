<head>
	<style type="text/css">
	#return a{
		background-color: #5EBB47;
		border: 1px solid #5dbd00;
		border-radius: 3px;
		color: #FFFFFF;
		/*display: inline-block;*/
		font-family: sans-serif;
		font-size: 16px;
		line-height: 44px;
		text-align: center;
		width: 150px;
		padding: 1%;
		text-decoration: none;
	}
	</style>
</head>
<h1>{$title}</h1>
<p><small>{$smarty.now|date_format:"%A, %B %e, %Y"}</small></p>
{img width="100%" src="{$image}" alt="Foto de {$title}"}
{space30}

<h1>Otras m&eacute;tricas</h1>
<ul>
	<li>{link href = "CLIMA huracan" caption = "Cono de trayectoria del hurac&aacute;n"}</li>
	<li>{link href = "CLIMA satelite" caption = "Imagen del sat&eacute;lite"}</li>
	<li>{link href = "CLIMA caribe" caption = "El Caribe</a>"}</li>
	<li>{link href = "CLIMA radar" caption = "Radar</a>"}</li>
	<li>{link href = "CLIMA temperatura" caption = "Temperatura del mar"}</li>
	<li>{link href = "CLIMA superficie" caption = "Superficie del Atl&aacute;ntico y el Caribe"}</li>
	<li>{link href = "CLIMA atlantico" caption = "Estado del Atl&aacute;ntico"}</li>
	<li>{link href = "CLIMA polvo" caption = "Polvo del desierto"}</li>
	<li>{link href = "CLIMA presion superficial" caption = "Presi&oacute;n superficial"}</li>
</ul>
<p id="return" style="text-align:center;">
	{link href="CLIMA" caption="Volver al Clima Actual"}
</p>
