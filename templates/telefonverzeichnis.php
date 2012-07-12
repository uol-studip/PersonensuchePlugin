<script src="<?=$path?>/js/move.js" type="text/javascript"></script>

	<table cellpadding="2" cellspacing="0" width="100%">
        <tr>
            <td class="topic" colspan="2"><b>Telefonverzeichnis</b></td>
            <? if($anzeigen=="yes"): ?>
                <td class="topic" colspan="2">
                    <a href="<?=$rtfexportlink?>?faksinst=<?=$faksinst_id?>">
                        <img src="<?= Assets::image_path('icons/16/blue/file-generic.png')?>" title="RTF export generieren" alt="RTF export generieren"/>
                    </a>
                </td>
            <? endif; ?>
			</tr>
			</table>
<form method="POST" action="<?=$link?>">
<input type="hidden" name="anzeigen" value="yes" />
<table border="0" cellpadding="2" cellspacing="0" width="100%">
	<tr class="steel1">
		<td width="100">Einrichtung:</td>
		<td>
		<select name="faksinst">
		<? foreach ($faks as $fak): ?>
			<option style="font-weight: bold;" value="<?=$fak['Institut_id']?>" <?=($fak['Institut_id']==$faksinst_id)?'selected="selected"':''?>><?=$fak['Name']?></option>
			<? foreach ($fak['institute'] as $institut): ?>
				<option value="<?=$institut['Institut_id']?>" <?=($institut['Institut_id']==$faksinst_id)?'selected="selected"':''?>>&nbsp;&nbsp;&nbsp;<?=$institut['Name']?></option>
			<? endforeach; ?>
		<? endforeach; ?>
		</select>
		<?=makeButton('anzeigen', 'input', 'anzeigen', 'search');?></td>
	</tr>
</table>
</form>
<br/>

<? $statKey = 0;
if ($teleliste) :
foreach ($teleliste as $instKey => $Institut) : ?>
	<table cellspacing="0" cellpadding="2" width="100%">
		<tr class="topic">
			<td><b><?=$Institut['name']?></b></td>
			<td align="right">
                <img src="<?= Assets::image_path('icons/16/blue/arr_eol-down.png')?>" alt="Tabelle aufklappen oder zuklappen" border="0" onClick="javascript:toggletable(<?=$instKey?>,false)" style="cursor: pointer;" /></td>
		</tr>
	</table>
	<? if (count($Institut['statusgruppe']) > 0) : ?>
	<table width="100%" cellpadding="1" cellspacing="0">
		<tr>
			<th width="17%" align="left">Nachname, Vorname</th>
			<th width="25%" align="left">Sprechzeiten</th>
			<th width="32%" align="left">Raum</th>
			<th width="13%" align="left">Telefon</th>
			<th width="13%" align="left">Fax</th>
		</tr>
	</table>
	<div id="table_<?=$instKey?>">
		<? foreach ($Institut['statusgruppe'] as $Statusgruppe) : ?>
		<table cellspacing="0" cellpadding="2" width="100%">
			<tr>
				<td class="printhead"><b><?=$Statusgruppe['statusgruppe']['name']?></b></td>
				<td class="printhead" align="right"><img src="<?= Assets::image_path('icons/16/blue/arr_eol-down.png')?>" alt="Tabelle aufklappen oder zuklappen" border="0" onClick="javascript:toggletable(<?=$statKey?>,true)" style="cursor: pointer;" /></td>
			</tr>
		</table>
		<table width="100%" id="subtable_<?=$statKey?>" cellpadding="1" cellspacing="0">
			<? if (count($Statusgruppe['personen'])>0) : ?>
			<? foreach ($Statusgruppe['personen'] as $persKey => $Person) : ?>
				<tr class="<?= TextHelper::cycle('cycle_odd', 'cycle_even') ?>" onClick="edit('<?=$Person['user_id']?>', '<?=$Person['Institut_id']?>', '<?=$pluginpath?>');" title="Klicken Sie auf die Zeile, um diese Person zu bearbeiten." style="cursor: pointer;">
					<td width="17%"><?=$Person['Nachname']?>, <?=$Person['Vorname']?></td>
					<td width="25%"><?=$Person['sprechzeiten']?></td>
					<td width="32%"><?=$Person['raum']?></td>
					<td width="13%"><?=$Person['Telefon']?></td>
					<td width="13%"><?=$Person['Fax']?></td>
					<td width="13%">
                        <a href="<?=$link_edit?>?bearbeiten=<?=$Person['user_id']?>&institut_id=<?=$Person['Institut_id']?>">
                            <?=makebutton('bearbeiten', 'img', 'bearbeiten')?>
                        </a>

                    </td>
				</tr>
			<? endforeach; ?>
			<? else : ?>
			<tr>
				<td class="cycle_odd">Keine Einträge vorhanden.</td>
			</tr>
			<? endif;?>
		</table>
		<br/>
		<? $statKey++;
		endforeach; ?>
	</div>
	<? else: ?>
	<div class="steel1">Es sind keine Einträge vorhanden.</div>
	<? endif; ?>
	<br/>
<? endforeach;
endif; ?>