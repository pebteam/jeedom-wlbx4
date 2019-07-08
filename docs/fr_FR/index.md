DESCRIPTION
===========
Ce plugin permet d’allumer ou éteindre le wifi de la livebox 4 d’orange.

CONFIGURATION DU PLUGIN
=======================
Après avoir installé le plugin, il vous suffit de l’activer. Ce plugin n’a aucune configuration particulière.

CONFIGURATION DES EQUIPEMENTS
=============================
La configuration des équipements est accessible à partir du menu plugin : 
Plugins / Communication / Wlbx4

Pour créer un équipement, cliquez sur ```Ajouter``` puis donnez lui un nom (Dans la suite du présent document, la référence au nom donné sera [Livebox]).     
Vous retrouvez alors toute la configuration de votre équipement.

ONGLET Livebox
--------------
- **Nom de l’équipement** : nom donné lors de la création de l’équipement.
- **Objet parent** : indique l’objet parent auquel appartient l’équipement.
- **Catégorie** : les catégories de l’équipement (il peut appartenir à plusieurs catégories).
- **Activer** : permet de rendre l’équipement actif.
- **Visible** : permet de rendre l’équipement visible sur le dashboard.
- **IP de la livebox** : Adresse IP de la livebox (En général : 192.168.1.1)
- **Nom administrateur de la livebox** : Nom de passe pour accéder à la page d’administration de la livebox (En général : admin)
- **Mot de passe administrateur** : Mot de pase pour accéder à la page d’administration de la livebox (En général : huit premiers caractères - en MAJUSCULE et sans espace - de la clé de sécurité)
- **Apparence** : Mode d’affichage du plugin sur le dashboard

ONGLET Commandes
----------------
Affiche les actions ou informations disponible pour le plugin.
Ces commandes sont générées automatiquement lors de la création de l’équipement. Il n’est pas possible de les supprimer ou d’en ajouter d’autre.

Cinq actions ou informations sont disponibles :
- **State** (Information – nom interne : wstate) : Retourne l’état actuel du wifi (0 = éteint – 1 = allumé).
- **On** (Action – nom interne : won) : Allumer le wifi.
- **Off** (Action – nom interne : woff) : Eteindre le wifi.
- **Reverse** (Action – nom interne : wswitch) : Inverser (allumer/éteindre) le wifi.
- **Rafraichir** (Action – nom interne : wrefresh) : Vérifier l’état actuel du wifi.

Les paramètres suivants peuvent être modifiés par l’utilisateur :
- **Nom** : Le nom de la commande qui apparaitra dans le widget sur le dashbord.
- **Icône-action** : L’icône de la commande qui apparaitra dans le widget sur le dashbord.
- **Afficher** : Permet de rendre la commande visible dans le widget.
- **« Roues crantées »** : Affiche les options avancées. Permet notamment de modifier et personnaliser l’apparence du widget sur le dashboard.

UTILISATION
==========

Utilisation de base
-------------------
Une fois l’équipement créé et configuré, celui-ci est directement accessible (sous réserve que l’option visible ait été choisie !) sur le dashboard choisi comme parent.
Il suffit alors de cliquer sur le bouton correspondant pour allumer ou éteindre le wifi de la livebox.

Création d’un bouton unique (Au moyen d’un virtuel)
--------------------------------------------------
Il est possible d’afficher l’équipement sous la forme d’un unique bouton permettant d’allumer ou d’éteindre le wifi de la livebox et retournant en même temps son état.
Pour y parvenir, il convient d’abord de créer un widget puis de créer un virtuel :

### Création d’un widget :
A partir du menu : 
Plugins / Programmation / Widget
Choisissez « Ajouter un widget ».

Complétez les paramètres :
- **Nom du widget** : Wlbx_Toggle
- **Version** : Dashboard
- **Type** : Action
- **Sous-type** : Défaut

Dans la zone d’édition, insérez le code suivant :

``` HTML
<div class="wlbxX-#id# cmd tooltips cmd-widget #history# cursor" title="" data-type="action" data-subtype="other" data-cmd_id="#id#" data-cmd_uid="#uid#">
    <center>
        <span style="font-size: 3.5em;" class="action iconCmd#uid#" id="iconCmd#id#"></span>
    </center>
    <style>
    </style>
    <script>
	jeedom.cmd.update['#id#'] = function(_options){
    	$('#iconCmd#id#').empty();
        if (_options.display_value == '1' || _options.display_value == 1 || _options.display_value == 'on') {
			$('#iconCmd#id#').append('<i class="icon jeedom2-fdp1-signal5"></i>');
        }else{
        	$('#iconCmd#id#').append('<i class="icon jeedom2-fdp1-signal0"></i>');   
        }
    }      
	jeedom.cmd.update['#id#']({display_value:'#state#'});
	$('.cmd[data-cmd_id=#id#] .action').on('click', function () {jeedom.cmd.execute({id: '#id#'});});
	</script>
</div>
```
Puis sauvegardez.

> Vous pouvez personaliser le widget en remplacant [<i class="icon *"></i>] par un lien vers l’image de votre choix.

### Création d’un virtuel : 
A partir du menu : 
Plugins / Programmation / Virtuel
Choisissez « Ajouter» puis donnez un nom au nouveau virtuel.

**Dans l’onglet équipement :**
Déterminez l’objet parent (Le nom du dashboard par exemple) puis sélectionnez « Activer » et « Visible ».

**Dans l’onglet commandes :**
Exécutez « Ajouter une info virtuelle ».

Complétez les paramètres : 
- Nom : Etat
- Sous-type : Binaire
- Valeur : A côté de la zone de texte, exécutez « Recherche équipement » et indiquez la commande « Reverse » de l’équipement [Livebox] (Ou tout autre nom que vous aurez donné lors de la création initiale de l’équipement) de l’objet auquel l’équipement est rattaché.
- Paramètres : Décochez afficher
Exécutez « Ajouter une commande virtuelle ».
Complétez les paramètres : 
- Nom : Inverser
- Sous-type : Défaut
- valeur : 
. A côté de la première zone de texte (Nom information), exécutez « Recherche équipement » et indiquez la commande « Reverse » de l’équipement [Livebox] (Ou tout autre nom que vous aurez donné lors de la création initiale de l’équipement) de l’objet auquel l’équipement est rattaché.
. A côté de la deuxième zone de texte (Valeur), exécutez « Recherche équipement » et indiquez la commande « State » de l’équipement [Livebox] (Ou tout autre nom que vous aurez donné lors de la création initiale de l’équipement) de l’objet auquel l’équipement est rattaché.

Puis sauvegardez.

En dessous du nom de la commande virtuelle (Inverser dans  notre exemple), sélectionnez dans la zone de liste l’info virtuelle (Etat dans notre exemple).
Enfin, exécutez la « roue crantée » de la commande virtuelle, et dans l’onglet « Affichage », au paramètre widget/dashboard : sélectionnez le widget « Wlbx_Toggle » créé précédemment.

Création d’un scénario
-----------------------
Vous pouvez utiliser un scénario pour programme l’allumage ou l’extinction du wifi.

A titre d’exemple, il est indiqué maintenant comment créer un scénario qui étendra automatique le wifi à 22H30 si celui-ci est encore allumé :

A partir du menu: 
Outils / Scénarios
Choisissez « Ajouter» puis donnez un nom au nouveau scénario.

**Dans l’onglet général :**
Dans la zone de liste du « mode de scénario », sélectionnez « Programmé ».
Exécutez « +Programmation ».
Dans la zone de texte venant de s’ajouter, cliquez sur le point d’interrogation. Un assistant s’ouvre.
Complétez les paramètres : 
- A exécuter : récurrent
- Every : Day at 22 : 30

**Dans l’onglet scénario :**
Exécutez « +Ajouter bloc » et choisissez « Si/Alors/Sinon ».
Dans le bloc venant de s’ajouter, exécutez « Ajouter » et sélectionnez « action ».
Dans la première zone de texte : indiquez 1=1 ; Puis dans la deuxième zone de texte : exécutez « Sélectionner la commande » et indiquez la commande « Off » de l’équipement [Livebox] (Ou tout autre nom que vous aurez donné lors de la création initiale de l’équipement) de l’objet auquel l’équipement est rattaché.

Puis sauvegardez.
