<h1>El Clima</h1>
<p><small>{$today}</small></p>

{space15}

{foreach $weather as $w}
	<h2>{$w->location}</h2>
	<table border="0" width="100%" cellpadding="10" cellspacing="0">
		<tr>
			<td align="center" valign="top" width="100" bgcolor="#F2F2F2">
				<b>Hoy</b>
				{space5}
				&#{$w->icon}; 
				{space5}
				<small>{$w->description}</small> 
			</td>
			<td bgcolor="#F2F2F2">
				Temperatura: {$w->temperature}<br/>
				Viento: Hacia el {$w->windDirection}, a {$w->windSpeed}<br/>
				Precipitaciones: {$w->precipitation}<br/>
				Humedad: {$w->humidity}<br/>
				Visibilidad: {$w->visibility}<br/>
				Presi&oacute;n: {$w->pressure}<br/>
				Nubosidad: {$w->cloudcover}<br/>
				Actualizado: {$w->time}
			</td>
		</tr>
	</table>
	{space10}
{/foreach}

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
