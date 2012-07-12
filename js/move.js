/*function move_group()
{
id=document.getElementById("move_objekt").selectedIndex;
new_group=document.getElementById("move_objekt").options[id].text;
NeuerEintrag = new Option(new_group, "", false, true);

if(document.getElementById("current_objekt").length==0)
{
     document.getElementById("current_objekt").options[document.getElementById("current_objekt").length] = NeuerEintrag;
}
var check=true;
  for (i = 0; i < document.getElementById("current_objekt").length; ++i)
  {
   if(document.getElementById("current_objekt").options[i].text==new_group)
   {
   check=false;
   }
  }
 if(check)
   {
    document.getElementById("current_objekt").options[document.getElementById("current_objekt").length] = NeuerEintrag;
   }

}

function save_groups()
{
var groups="";
	for (i = 0; i < document.getElementById("current_objekt").length; ++i)
  {
  groups+=document.getElementById("current_objekt").options[i].text+";";
  }
document.getElementById("write_groups").value = groups;
}
*/
function turn(from, to) {

 var offered = new Array();
 var choosed = new Array();
 var entries = new Object(); // Assoziatives Array

 for(var i = 0; i < from.length; i++) {
  entries[from[i].text] = from[i].value;
  if(from[i].selected == true) { // Selektierte Eintraege suchen
   choosed[choosed.length] = from[i].text; // Ans Array anhaengen
  }
  else {
   offered[offered.length] = from[i].text;
  }
 }

 for(i = 0; i < to.length; i++) {
  entries[to[i].text] = to[i].value;
  choosed[choosed.length] = to[i].text;
 }

 from.length = 0; // to- und from-options loeschen
 to.length = 0;

 offered.sort(); // Temporaere Listen sortieren
 choosed.sort();

 for(var j = 0; j < offered.length; j++) { // from-Liste neu aufbauen
  from[j] = new Option(offered[j], entries[offered[j]]);
 }

 for(j = 0; j < choosed.length; j++) { // to-Liste neu aufbauen
  to[j] = new Option(choosed[j], entries[choosed[j]]);
 }
}


function allToOther(from, to) {

 for(var j = 0; j < from.length; j++) {
  from[j].selected = true; // Alle Eintraege selektieren und
 }
 turn(from, to); // der Funktion turn zum Verschieben uebergeben
}
function toggletable(test,sub)
				{
				if(sub)
				{
				 var table = "#subtable_"+test;
				}
				else
				{
				 var table = "#table_"+test;
				}
					/*if(document.getElementById(table).style.display == 'block')
					{
					 document.getElementById(table).style.display ='none';
					}
					else
					{
					document.getElementById(table).style.display ='block';
					}
					return false;*/
					$(table).toggle();
				}

function rlysubmit(forms)
{
 var cnf = confirm("Beachten Sie, beim Löschen gehen die universitären Daten verloren.");
 if(cnf)
 {
 forms.submit();
 }
 else
 {
  return false;
 }
}

function edit(id,institut,path)
{
	location=path+"&bearbeiten="+id+"&institut_id="+institut;
	//alert(path+"&bearbeiten="+id+"&institut_id="+institut);
}

function hover(row)
{
row.className="";
row.style.backgroundColor = "#99C85B";
}

function out (row,klasse)
{
row.style.backgroundColor = "#F5F5F0";
row.className=klasse;
}
function send() {

var groups="";
	for (i = 0; i < document.form.list2.length; ++i)
  {
  groups+=document.form.list2.options[i].value+";";
  }


document.form.Items.value = groups;
}

