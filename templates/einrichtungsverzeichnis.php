<?= $message ?>
<div class="topic"><b>Personensuche</b></div>
<form method="post" action="<?=$link?>">
<input type="hidden" name="action" value="search" />
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr class="steel1">
		<td width="100">Vorname:</td>
		<td><input type="text" name="vorname" style="width: 300px;" value="<?=$vorname?>" /></td>
	</tr>
	<tr class="steelgraulight">
		<td>Nachname:</td>
		<td><input type="text" name="nachname" style="width: 300px;" value="<?=$nachname?>" /></td>
	</tr>
	<tr class="steel2">
		<td></td>
		<td><?=makeButton('suchen', 'input', 'Person suchen', 'search')?></td>
	</tr>
</table>
</form>
<br/>

<? if($users): ?>
<br /> <br />
<div class="topic"><b>Gefundene Personen</b></div>
<table border="0" width="100%" cellpadding="2" cellspacing="0">
	<tr>
	    <th></th>
		<th align="left">Name, Vorname</th>
		<th align="left">Raum</th>
		<th align="left">Telefon</th>
		<th align="left">Institute</th>
		<th align="right">Funktion</th>
	</tr>
<? foreach ($users as $index=>$user): ?>
	<tr class="<?=($index%2==0)?'cycle_odd':'cycle_even'?>">
	    <td><? echo Avatar::getAvatar($user["user_id"])->getImageTag(Avatar::SMALL); ?></td>
		<td nowrap="nowrap">
            <a href="<?= URLHelper::getLink('about.php?username='.$user['username'])?>">
                <?=$user['nachname']?>, <?=$user['vorname']?>
            </a>
		</td>
		<td><?=$user['zusatz'][0]['raum']?></td>
		<td nowrap="nowrap"><?=$user['zusatz'][0]['telefon']?></td>
		<td>
    		<? if(!empty($user['zusatz'])):?>
                <? foreach ($user['zusatz'] as $key=>$institut): ?>
                    <?=($key>0 && !empty($institut['institut']))?', ':''?>
                    <?=(!empty($institut['institut']))?$institut['institut']:''?>
                <? endforeach; ?>
            <? endif; ?>
        </td>
		<td align="right">
			<a href="<?=$link?>?bearbeiten=<?=$user['user_id']?>&institut_id=<?=$user['zusatz'][0]['institut_id']?>">
                <?=makebutton('bearbeiten', 'img', 'bearbeiten')?>
            </a>
		</td>
	</tr>
<? endforeach; ?>
</table>
<? endif; ?>
