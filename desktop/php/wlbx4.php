<!-- CONTROLE ET DECLARATION INITIALE OBLIGATOIRE --> 
<?php
if (!isConnect('admin')) {throw new Exception('{{401 - Accès non autorisé}}');}
$plugin = plugin::byId('wlbx4');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());
?>
	
<div class="row row-overflow">

<!-- MENU DE RECHERCHE APPAISSANT SUR LE COTE --> 
  <div class="col-lg-2">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <a class="btn btn-default eqLogicAction" style="width : 100%;margin-top : 5px;margin-bottom: 5px;" data-action="add">
          <i class="fa fa-plus-circle">
          </i> {{Ajouter}}
        </a>
        <li class="filter" style="margin-bottom: 5px;">
          <input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/>
        </li>
		<?php
			foreach ($eqLogics as $eqLogic) {
				echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
			}
		?>
      </ul>
    </div>
  </div>
  
  <!-- FENETRE DE CREATION ET D'ACCES AUX COMMANDES  -->  
  <div class="col-xs-12 eqLogicThumbnailDisplay" style="border-left: solid 1px #EEE; padding-left: 25px;">
    <legend>
      <i class="fas fa-cog">
      </i>  {{Gestion}}
    </legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction logoPrimary" data-action="add">
        <i class="fas fa-plus-circle">
        </i>
        <br>
        <span>{{Ajouter}}
        </span>
      </div>
      <div class="cursor eqLogicAction logoSecondary" data-action="gotoPluginConf">
        <i class="fas fa-wrench">
        </i>
        <br>
        <span>{{Configuration}}
        </span>
      </div>
    </div>
    <legend>
      <i class="fas fa-hdd-o">
      </i> {{Livebox}}
    </legend>
    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />
    <div class="eqLogicThumbnailContainer">
      <?php
		foreach ($eqLogics as $eqLogic) {
			$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
			echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
			echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
			echo '<br>';
			echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
			echo '</div>';
		}
	  ?>
    </div>
  </div>
  
  <div class="col-xs-12 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
    <!-- BOUTONS ACTIONS  --> 
    <div class="input-group pull-right" style="display:inline-flex;">
      <span class="input-group-btn">
        <a class="btn btn-default btn-sm eqLogicAction roundedLeft" data-action="configure" style="margin: 5px;">
          <i class="fa fa-cogs">
          </i> {{Configuration avancée}}
        </a>
        <a class="btn btn-default btn-sm eqLogicAction" data-action="copy" style="margin: 5px;">
          <i class="fas fa-copy">
          </i> {{Dupliquer}}
        </a>
        <a class="btn btn-sm btn-success eqLogicAction" data-action="save" style="margin: 5px;">
          <i class="fas fa-check-circle">
          </i> {{Sauvegarder}}
        </a>
        <a class="btn btn-danger btn-sm eqLogicAction roundedRight" data-action="remove" style="margin: 5px;">
          <i class="fas fa-minus-circle">
          </i> {{Supprimer}}
        </a>
      </span>
    </div>
	
	<!-- ONGLETS RETOUR/CONFIGURATIONS/COMMANDES  --> 
    <ul class="nav nav-tabs" role="tablist">
      <li role="presentation">
        <a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay">
          <i class="fa fa-arrow-circle-left">
          </i>
        </a>
      </li>
      <li role="presentation" class="active">
        <a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab">
          <i class="fa fa-tachometer">
          </i> {{Livebox}}
        </a>
      </li>
      <li role="presentation">
        <a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab">
          <i class="fa fa-list-alt">
          </i> {{Commandes}}
        </a>
      </li>
    </ul>
	
	<!-- CONTENU DE LA PAGE DE CONFIGURATION  --> 
    <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
      <div role="tabpanel" class="tab-pane active" id="eqlogictab">
        <br/>
		 
        <form class="form-horizontal">
          <fieldset>
		  
			<!-- PARTIE SYSTEME  -->
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom de la livebox}}
              </label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement template}}"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label" >{{Objet parent}}
              </label>
              <div class="col-sm-3">
                <select id="sel_object" class="eqLogicAttr form-control" data-l1key="object_id">
                  <option value="">{{Aucun}}
                  </option>
                  <?php
					foreach (object::all() as $object) {
						echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
					}
				  ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Catégorie}}
              </label>
              <div class="col-sm-9">
                <?php
					foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
						echo '<label class="checkbox-inline">';
						echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
						echo '</label>';
					}
				?>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">
              </label>
              <div class="col-sm-9">
                <label class="checkbox-inline">
                  <input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}
                </label>
                <label class="checkbox-inline">
                  <input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}
                </label>

              </div>
            </div>
			
			<!-- PARTIE SPECIFIQUE AU PLUGIN --> 
            <div class="form-group">
              <label class="col-sm-3 control-label">{{IP de la livebox}}
              </label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="ip"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Nom administrateur de la livebox}}
              </label>
              <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="username"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Mot de passe administrateur}}
              </label>
              <div class="col-sm-3">
                <input type="password" class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="password"/>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-3 control-label">{{Apparence}}
              </label>
              <div class="col-sm-3">
                  <select class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="wwidget">
                  <option value="wwdefault">{{Par défaut}}</option>
				  <option value="wwpersonal">{{Personalisée}}</option>
				  </select>
              </div>
            </div>
          </fieldset>
        </form>
      </div>
	  
	  <!-- CONTENU DE LA PAGE DES COMMANDES  -->
      <div role="tabpanel" class="tab-pane" id="commandtab">
	  
		<!-- BOUTON AJOUTER COMMANDE (Non disponible pour mon plugin !)
        <a class="btn btn-success btn-sm cmdAction pull-right" data-action="add" style="margin-top:5px;">
          <i class="fa fa-plus-circle">
          </i> {{Commandes}}
        </a>
        <br/>
		-->	
		
        <br/>
        <table id="table_cmd" class="table table-bordered table-condensed">
          <thead>
            <tr>
              <th style="width: 50px;">#
              </th>
              <th>{{Nom}}
              </th>
              <th style="width: 120px;">{{Icône-action}}
              </th>
			  <!-- Colonne Type/Sous-type (Non disponible pour mon plugin !)  
              <th style="width: 120px;">{{Sous-Type}}
              </th>
			  -->  
              <th style="width: 120px;">{{Paramètres}}
              </th>
              <th style="width: 100px;">{{Action}}
              </th>
            </tr>
          </thead>
          <tbody>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- INCLUDE OBLIGATOIRES  --> 
<!-- Spécifique plugin  -->
<?php include_file('desktop', 'wlbx4', 'js', 'wlbx4');?>
<!-- Système  -->
<?php include_file('core', 'plugin.template', 'js');?>
