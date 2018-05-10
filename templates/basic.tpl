<head>
	<style type="text/css">
	#content a{
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
	#content, h1, #sub, #forecast
	{
		text-align: center;
	}
	</style>
</head>
<h1>El Clima Actual</h1>
<p id="sub"><small>{$data['now']}</small></p>
<div id="content">
	<h2>{$data['city']}</h2>
	{link href="CLIMA" caption="Cambiar provincia" desc="m:Cambiar provincia [PINAR DEL RIO,LA HABANA,ARTEMISA,MAYABEQUE,MATANZAS,VILLA CLARA,CIENFUEGOS,SANCTI SPIRITUS,CIEGO DE AVILA,CAMAGUEY,
	LAS TUNAS,HOLGUIN,GRANMA,SANTIAGO DE CUBA,GUANTANAMO,ISLA DE LA JUVENTUD]*" popup="true" wait="true"}
	<table border="0" width="100%" cellpadding="10" cellspacing="2">
		<tr>
			<td>
				Temperatura: {$data['temperature']}<br/>
				Viento: Hacia el {$data['windDirection']}, a {$data['windSpeed']}<br/>
				Precipitaciones: {$data['precipitation']}<br/>
				Humedad: {$data['humidity']}<br/>
				Presi&oacute;n: {$data['pressure']}<br/>
				Amanecer: {$data['sunrise']}<br/>
				Anochecer: {$data['sunset']}<br/>
				Nubosidad: {$data['clouds']}<br/>
				Actualizado: {$data['lastUpdate']}<br/>
			</td>
			<td style="font-weight: bold;font-size: 6em; width: 40%;">{$data['icon']}</td>
			<!--<td>{img src="{$data['icon']}" alt="Actual" width="100px"}</td>-->
		</tr>
	</table>
	{space10}
</div>
<div id="forecast">
	<h2>Pronostico del clima</h2>
	<table border="0" width="100%" cellpadding="10" cellspacing="0">
		<tr>
			{$cont=0}
			{foreach $fcast as $w}
				{if ($data['environment']!='app' and $cont==4) or ($data['environment']=='app' and ($cont % 2)==0)}
				</tr>
				<tr>
				{/if}
					<td style="width: 25%; height: 50%;">
						<span style="font-weight: bold;font-size: 2em;">{$w['icon']}</span><br/>
						<!--{img src="{$w['icon']}" alt="Pronostico {$cont+1}" width="50px"}-->
						{$w['from']->format('H:i')} a {$w['to']->format('H:i')}<br/>
						{$w['temperature']}<br/>
						{$w['clouds']}<br/>
						{if $w['precipitation']!="no"}
						{$w['precipitation']}<br/>
						{else}
						Sin lluvias
						{/if}
					</td>
					{$cont=$cont+1}
			{/foreach}
		</tr>
	</table>
</div>

{space15}

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
