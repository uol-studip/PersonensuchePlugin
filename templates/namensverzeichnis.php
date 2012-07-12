<table width="100%" cellpadding="0" cellspacing="0">
	<tr>
		<td valign="top">

<form method="POST" action="<?=$link?>">
<table width="100%" cellpadding="2" cellspacing="0">
    <tr>
        <td class="topic"><b>Namensverzeichnis erstellen</b></td>
    </tr>
    <tr class="steel1">
        <td>
 		<input type="radio" name="type" value="alle" onclick="document.getElementById('semester').style.display = 'none'; document.getElementById('generale').style.display = 'none'" checked>Alle Mitarbeiter ausgeben<br>
		<input type="radio" name="type" value="lehrende" onclick="document.getElementById('semester').style.display = 'block'; document.getElementById('generale').style.display = 'block'">Nur lehrende Mitarbeiter eines Semesters ausgeben<br>
        <div id="semester" style="display: none; padding: 10px;">Semester auswählen:
            <select name="semester_id">
                <? foreach ($semester as $semester): ?>
                    <option value="<?=$semester['id'] ?>"<? if ($semester['currentsemester'] == true): ?> selected="selected"<? endif; ?>><?=$semester['name'] ?></option>
                <? endforeach; ?>
            </select>
        </div>
        </td>
    </tr>
    <tr class="steelgraulight">
        <td>
	    <input type="checkbox" name="schwerp" value="true"> Mit Schwerpunkten ausgeben<br>
	    <span id="generale" style="display: none;"><input type="checkbox" name="generale" value="yes"> Nur Dozenten die Studium Generale zugestimmt haben</span></p>
        </td>
    </tr>
    <tr>
        <td class="steel2"><?=makeButton('erstellen', 'input', 'Namensliste als RTF erzeugen');?></td>
    </tr>
</table>
</form>

		</td>
		<td width="270" align="center" valign="top">
		<? print_infobox(array(
                array(
                    'kategorie'=>_('Hinweis').':',
                    'eintrag'=>array(
                        array(
                            'icon'=>'icons/16/black/info.png',
                            'text'=>_('Das Namensverzeichnis wird als RTF-Datei erstellt und zum Download angeboten.<br>Die Erzeugung kann unter Umständen längern dauern. Haben Sie bitte Geduld.')
                        )
                    )
                )
            ), 'infobox/wiki.jpg'); ?>
		</td>
	</tr>
</table>