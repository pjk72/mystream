#!/bin/bash
# Copyright © 2008 Olivier Mengué
# ./tv.sh tv.2008-02-14T23:00:00.xml 2008-02-14T23:00:00
#
# 2008-03-02
#   Correction du nom de fichier de sortie par défaut.
#   Mise en évidence du paramétrage de la liste des chaînes à récupérer.
# 2008-02-14
#   Version initiale.

# Liste des codes des chaines
# Voir le source de la page http://television.telerama.fr/tele/grille.php
#
# Hertzien
#chaines="192,4,80,34,47,111,118"
# TNT
chaines="192,4,80,34,47,111,118,445,458,78,482,444,446,195,119,76,439,237,15"


a=
[[ -n "$2" ]] && a="--date=$2"
d="$( LC_TIME=fr_FR date '+%Y-%m-%d %H:00:00' $a )"
#echo "$d"
me="$(basename "$0")"
home="$(dirname "$0")"

output="${1:-${me%%.sh}.xml}"
[[ "x$output" = "x/" ]] || output="$home/$output"

curl --data "xajax=chargerProgramme&xajaxr=$(date +%s)&xajaxargs[]=$d&xajaxargs[]=$chaines" 'http://television.telerama.fr/tele/grille.php' | xsltproc "$home/leprogramme.xslt" - | perl -MJSON -npe '
if (m@^ *<div id="(data_[^"]*)" style="display:none;">([^}]*})</div>@) {
    ($id,$data)=($1,from_json($2));
    $data = join("\n", map { qq|<div class="$_">$data->{$_}</div>| } keys %$data);
    $_ = "<div id=\"$id\">$data</div>";
    s@<br>@<br/>@g
}
s/&([^;]{8})/&amp;$1/g;' | xsltproc "$home/xmltv.xslt" - > "$output"
