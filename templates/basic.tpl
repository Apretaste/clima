<h1>El Clima</h1>
<p><small>{$smarty.now|date_format:"%A, %B %e, %Y"}</small></p>

{space15}

{foreach $weather as $w}
	<h2>{$w->location}</h2>
	<table border="0" width="100%" cellpadding="10" cellspacing="0">
		<tr>
			<td align="center" valign="top" width="100" bgcolor="#F2F2F2">
				<b>Hoy</b>
				{space5}
				{img src="{$w->icon}" width="60"}
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
 {*
	{space10}

	<table border="0" width="100%" cellpadding="5">
		<tr>
			{foreach $w->days as $d}
			<td align="center">
				<b>{$d->weekday}</b>
				{space5}
				{img src="{$d->icon}" width="40"}
				{space10}
				<small>
					{$d->description}<br/>
					Viento: {$d->windDirection}, {$d->windSpeed}<br/>
					Max: <span style="color: red;">{$d->tempMax}</span>
					Min: <span style="color: blue;">{$d->tempMin}</span>
				</small>
			</td>		
			{/foreach}
		</tr>
	</table>
*}
	{if not $w@last}
		{space10}
		<hr/>
		{space10}
	{/if}
{/foreach}

{space30}

<h1>Otras m&eacute;tricas</h1>
<ul>
	<li>{link href = "CLIMA satelite" caption = "Imagen del sat&eacute;lite"}</li>
	<li>{link href = "CLIMA nasa" caption = "Imagen de la NASA"}</li>
	<li>{link href = "CLIMA caribe" caption = "El Caribe</a>"}</li>
	<li>{link href = "CLIMA radar" caption = "Radar</a>"}</li>
	<li>{link href = "CLIMA sector" caption = "Sector visible"}</li>
	<li>{link href = "CLIMA infrarroja" caption = "Infrarroja"}</li>
	<li>{link href = "CLIMA vapor" caption = "Vapor de Agua"} </li>
	<li>{link href = "CLIMA temperatura" caption = "Temperatura del mar"}</li>
	<li>{link href = "CLIMA superficie" caption = "Superficie del Atl&aacute;ntico y el Caribe"}</li>
	<li>{link href = "CLIMA atlantico" caption = "Estado del Atl&aacute;ntico"}</li>
	<li>{link href = "CLIMA polvo" caption = "Polvo del desierto"}</li>
	<li>{link href = "CLIMA presion superficial" caption = "Presi&oacute;n superficial"}</li> 
</ul>
