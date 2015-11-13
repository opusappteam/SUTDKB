<?php
/*
 * @version $Id: knowbaseitem.class.php 23230 2014-11-14 10:32:46Z yllen $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/**
 * KnowbaseItem Class
**/
class KnowbaseItem extends CommonDBTM {

   // For visibility checks
   protected $users     = array();
   protected $groups    = array();
   protected $profiles  = array();
   protected $entities  = array();

   const KNOWBASEADMIN = 1024;
   const READFAQ       = 2048;
   const PUBLISHFAQ    = 4096;

   static $rightname   = 'knowbase';
   

   static function getTypeName($nb=0) {
      return __('Knowledge base');
   }


   /**
    * @see CommonGLPI::getMenuShorcut()
    *
    * @since version 0.85
   **/
   static function getMenuShorcut() {
      return 'b';
   }

   /**
    * @see CommonGLPI::getMenuName()
    *
    * @since version 0.85
   **/
   static function getMenuName() {
      if (!Session::haveRight('knowbase', READ)) {
         return __('FAQ');
      } else {
         return static::getTypeName(2);
      }
   }


   static function canCreate() {

      return Session::haveRightsOr(self::$rightname, array(CREATE, self::PUBLISHFAQ));
   }


   /**
    * @since version 0.85
   **/
   static function canUpdate() {
      return Session::haveRightsOr(self::$rightname, array(UPDATE, self::KNOWBASEADMIN));
   }


   static function canView() {
      global $CFG_GLPI;

      return (Session::haveRightsOr(self::$rightname, array(READ, self::READFAQ))
              || ((Session::getLoginUserID() === false) && $CFG_GLPI["use_public_faq"]));
   }


   function canViewItem() {
      global $CFG_GLPI;

      if ($this->fields['users_id'] == Session::getLoginUserID()) {
         return true;
      }
      if (Session::haveRight(self::$rightname, self::KNOWBASEADMIN)) {
         return true;
      }

      if ($this->fields["is_faq"]) {
         return ((Session::haveRightsOr(self::$rightname, array(READ, self::READFAQ))
                  && $this->haveVisibilityAccess())
                 || ((Session::getLoginUserID() === false) && $this->isPubliclyVisible()));
      }
      return (Session::haveRight(self::$rightname, READ) && $this->haveVisibilityAccess());
   }


   function canUpdateItem() {

      // Personal knowbase or visibility and write access
      return (Session::haveRight(self::$rightname, self::KNOWBASEADMIN)
              || ($this->fields['users_id'] == Session::getLoginUserID())
              || ((($this->fields["is_faq"] && Session::haveRight(self::$rightname, self::PUBLISHFAQ))
                   || (!$this->fields["is_faq"]
                       && Session::haveRight(self::$rightname, UPDATE)))
                  && $this->haveVisibilityAccess()));
   }


   /**
    * Get the search page URL for the current classe
    *
    * @since version 0.84
    *
    * @param $full path or relative one (true by default)
   **/
   static function getSearchURL($full=true) {
      global $CFG_GLPI;

      $dir = ($full ? $CFG_GLPI['root_doc'] : '');

      if (isset($_SESSION['glpiactiveprofile'])
          && ($_SESSION['glpiactiveprofile']['interface'] == "central")) {
         return "$dir/front/knowbaseitem.php";
      }
      return "$dir/front/helpdesk.faq.php";
   }


   function defineTabs($options=array()) {

      $ong = array();
   
    

     
    $this->addStandardTab(__CLASS__, $ong, $options);
	  $this->addStandardTab('KnowbaseItemTranslation',$ong, $options);
	$this->addStandardTab('Document_Item', $ong, $options);
    
      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
global $DB;
      if (!$withtemplate) {
         switch ($item->getType()) {
            case __CLASS__ :
               $ong[1] = $this->getTypeName(1);
               if ($item->canUpdateItem()) {
                  
                 
				 //akk
				 
				 
				 $query="Select * from glpi_entities_knowbaseitems where knowbaseitems_id=". $item->getID();
		 
		 
		 
		 if ($result = $DB->query($query)) {
         if ($DB->numrows($result)) {
		 
		 //published
		 
		 //4 for super user and 7 for supervisor can change target
		 if ($_SESSION["glpiactiveprofile"]['id']==4||$_SESSION["glpiactiveprofile"]['id']==7)
		 {
		  if ($_SESSION['glpishow_count_on_tabs']) {
                     $nb = $item->countVisibilities();
                     $ong[2] = self::createTabEntry(_n('Target','Targets',$nb),
                                                    $nb);
                  } else {
                     $ong[2] = _n('Target','Targets',2);
                  }
		 
		 }
		 
		 
		 
		 }
		 else
    		 {
			 //  not published
			 
			 
			 if ($_SESSION['glpishow_count_on_tabs']) {
                     $nb = $item->countVisibilities();
                     $ong[2] = self::createTabEntry(_n('Target','Targets',$nb),
                                                    $nb);
                  } else {
                     $ong[2] = _n('Target','Targets',2);
                  }

			$ong[3] = __('Edit'); 
			 
		 }
		  
		 
		 }
				 
                 

				
              




			  }
               return $ong;
         }
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         switch($tabnum) {
            case 1 :
               $item->showFull();
               break;

            case 2 :
               $item->showVisibility();
			   
               break;

            case 3 :
               $item->showForm($item->getID());
			   
               break;
         }
      }
      return true;
   }


   /**
    * Actions done at the end of the getEmpty function
    *
    *@return nothing
   **/
   function post_getEmpty() {

      if (Session::haveRight(self::$rightname, self::PUBLISHFAQ)
          && !Session::haveRight("knowbase", UPDATE)) {
         $this->fields["is_faq"] = 1;
      }
   }


   /**
    * @since version 0.85
    * @see CommonDBTM::post_addItem()
   **/
   function post_addItem() {

      if (isset($this->input["_visibility"])
          && isset($this->input["_visibility"]['_type'])
          && !empty($this->input["_visibility"]["_type"])) {

         $this->input["_visibility"]['knowbaseitems_id'] = $this->getID();
         $item                                           = NULL;

         switch ($this->input["_visibility"]['_type']) {
            case 'User' :
               if (isset($this->input["_visibility"]['users_id'])
                   && $this->input["_visibility"]['users_id']) {
                  $item = new KnowbaseItem_User();
               }
               break;

            case 'Group' :
               if (isset($this->input["_visibility"]['groups_id'])
                   && $this->input["_visibility"]['groups_id']) {
                  $item = new Group_KnowbaseItem();
               }
               break;

            case 'Profile' :
               if (isset($this->input["_visibility"]['profiles_id'])
                   && $this->input["_visibility"]['profiles_id']) {
                  $item = new KnowbaseItem_Profile();
               }
               break;

            case 'Entity' :
               $item = new Entity_KnowbaseItem();
               break;
         }
         if (!is_null($item)) {
            $item->add($this->input["_visibility"]);
            Event::log($this->getID(), "knowbaseitem", 4, "tools",
                     //TRANS: %s is the user login
                     sprintf(__('%s adds a target'), $_SESSION["glpiname"]));
         }
      }
   }


   /**
    * @since version 0.83
   **/
   function post_getFromDB() {

      // Users
      $this->users    = KnowbaseItem_User::getUsers($this->fields['id']);

      // Entities
      $this->entities = Entity_KnowbaseItem::getEntities($this->fields['id']);

      // Group / entities
      $this->groups   = Group_KnowbaseItem::getGroups($this->fields['id']);

      // Profile / entities
      $this->profiles = KnowbaseItem_Profile::getProfiles($this->fields['id']);
   }


   /**
    * @see CommonDBTM::cleanDBonPurge()
    *
    * @since version 0.83.1
   **/
   function cleanDBonPurge() {

      $class = new KnowbaseItem_User();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
      $class = new Entity_KnowbaseItem();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
      $class = new Group_KnowbaseItem();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
      $class = new KnowbaseItem_Profile();
      $class->cleanDBonItemDelete($this->getType(), $this->fields['id']);
   }



   /**
    * @since version 0.83
   **/
   function countVisibilities() {

      return (count($this->entities)
              + count($this->users)
              + count($this->groups)
              + count($this->profiles));
   }


   /**
    * Check is this item if visible to everybody (anonymous users)
    *
    * @since version 0.83
    *
    * @return Boolean
   **/
   function isPubliclyVisible() {
      global $CFG_GLPI;

      if (!$CFG_GLPI['use_public_faq']) {
         return false;
      }

      if (isset($this->entities[0])) { // Browse root entity rights
         foreach ($this->entities[0] as $entity) {
            if ($entity['is_recursive']) {
               return true;
            }
         }
      }
      return false;
   }


   /**
    * Is the login user have access to KnowbaseItem based on visibility configuration
    *
    * @since version 0.83
    *
    * @return boolean
   **/
   function haveVisibilityAccess() {

      // No public knowbaseitem right : no visibility check
      if (!Session::haveRightsOr(self::$rightname, array(self::READFAQ, READ))) {
         return false;
      }

      // Author
      if ($this->fields['users_id'] == Session::getLoginUserID()) {
         return true;
      }
      // Admin
      if (Session::haveRight(self::$rightname, self::KNOWBASEADMIN)) {
         return true;
      }
      // Users
      if (isset($this->users[Session::getLoginUserID()])) {
         return true;
      }

      // Groups
      if (count($this->groups)
          && isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"])) {

         foreach ($this->groups as $key => $data) {
            foreach ($data as $group) {
               if (in_array($group['groups_id'], $_SESSION["glpigroups"])) {
                  // All the group
                  if ($group['entities_id'] < 0) {
                     return true;
                  }
                  // Restrict to entities
                  $entities = array($group['entities_id']);
                  if ($group['is_recursive']) {
                     $entities = getSonsOf('glpi_entities', $group['entities_id']);
                  }
                  if (Session::haveAccessToOneOfEntities($entities, true)) {
                     return true;
                  }
               }
            }
         }
      }

      // Entities
      if (count($this->entities)
          && isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {

         foreach ($this->entities as $key => $data) {
            foreach ($data as $entity) {
               $entities = array($entity['entities_id']);
               if ($entity['is_recursive']) {
                  $entities = getSonsOf('glpi_entities', $entity['entities_id']);
               }
               if (Session::haveAccessToOneOfEntities($entities, true)) {
                  return true;
               }
            }
         }
      }

      // Profiles
      if (count($this->profiles)
          && isset($_SESSION["glpiactiveprofile"]) && isset($_SESSION["glpiactiveprofile"]['id'])) {

         if (isset($this->profiles[$_SESSION["glpiactiveprofile"]['id']])) {
            foreach ($this->profiles[$_SESSION["glpiactiveprofile"]['id']] as $profile) {
               // All the profile
               if ($profile['entities_id'] < 0) {
                  return true;
               }
               // Restrict to entities
               $entities = array($profile['entities_id']);
               if ($profile['is_recursive']) {
                  $entities = getSonsOf('glpi_entities', $profile['entities_id']);
               }
               if (Session::haveAccessToOneOfEntities($entities, true)) {
                  return true;
               }
            }
         }
      }

      return false;
   }


   /**
   * Return visibility joins to add to SQL
   *
   * @since version 0.83
   *
   * @param $forceall force all joins (false by default)
   *
   * @return string joins to add
   **/
   static function addVisibilityJoins($forceall=false) {

      $join = '';

      // Users
      $join .= " LEFT JOIN `glpi_knowbaseitems_users`
                     ON (`glpi_knowbaseitems_users`.`knowbaseitems_id` = `glpi_knowbaseitems`.`id`) ";

      // Groups
      if ($forceall
          || (isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"]))) {
         $join .= " LEFT JOIN `glpi_groups_knowbaseitems`
                        ON (`glpi_groups_knowbaseitems`.`knowbaseitems_id`
                              = `glpi_knowbaseitems`.`id`) ";
      }

      // Profiles
      if ($forceall
          || (isset($_SESSION["glpiactiveprofile"])
              && isset($_SESSION["glpiactiveprofile"]['id']))) {
         $join .= " LEFT JOIN `glpi_knowbaseitems_profiles`
                        ON (`glpi_knowbaseitems_profiles`.`knowbaseitems_id`
                              = `glpi_knowbaseitems`.`id`) ";
      }

      // Entities
      if ($forceall
          || !Session::getLoginUserID()
          || (isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"]))) {
         $join .= " LEFT JOIN `glpi_entities_knowbaseitems`
                        ON (`glpi_entities_knowbaseitems`.`knowbaseitems_id`
                              = `glpi_knowbaseitems`.`id`) ";
      }

      return $join;
   }


   /**
    * Return visibility SQL restriction to add
    *
    * @since version 0.83
    *
    * @return string restrict to add
   **/
   static function addVisibilityRestrict() {

      $restrict = '';
      if (Session::getLoginUserID()) {
         $restrict = "(`glpi_knowbaseitems_users`.`users_id` = '".Session::getLoginUserID()."' ";

         // Users
         $restrict .= " OR `glpi_knowbaseitems_users`.`users_id` = '".Session::getLoginUserID()."' ";

         // Groups
         if (isset($_SESSION["glpigroups"]) && count($_SESSION["glpigroups"])) {
            $restrict .= " OR (`glpi_groups_knowbaseitems`.`groups_id`
                                    IN ('".implode("','",$_SESSION["glpigroups"])."')
                               AND (`glpi_groups_knowbaseitems`.`entities_id` < 0
                                    ".getEntitiesRestrictRequest("OR", "glpi_groups_knowbaseitems",
                                                                 '', '', true).")) ";
         }

         // Profiles
         if (isset($_SESSION["glpiactiveprofile"])
             && isset($_SESSION["glpiactiveprofile"]['id'])) {
            $restrict .= " OR (`glpi_knowbaseitems_profiles`.`profiles_id`
                                    = '".$_SESSION["glpiactiveprofile"]['id']."'
                               AND (`glpi_knowbaseitems_profiles`.`entities_id` < 0
                                    ".getEntitiesRestrictRequest("OR", "glpi_knowbaseitems_profiles",
                                                                 '', '', true).")) ";
         }

         // Entities
         if (isset($_SESSION["glpiactiveentities"]) && count($_SESSION["glpiactiveentities"])) {
            // Force complete SQL not summary when access to all entities
            $restrict .= getEntitiesRestrictRequest("OR", "glpi_entities_knowbaseitems", '', '',
                                                    true, true);
         }

         $restrict .= ") ";
      } else {
         $restrict = '1';
      }
      return $restrict;
   }


   /**
    * @see CommonDBTM::prepareInputForAdd()
   **/
   function prepareInputForAdd($input) {

      // set new date if not exists
      if (!isset($input["date"]) || empty($input["date"])) {
         $input["date"] = $_SESSION["glpi_currenttime"];
      }
      // set users_id

      // set title for question if empty
      if (isset($input["name"]) && empty($input["name"])) {
         $input["name"] = __('New item');
      }

      if (Session::haveRight(self::$rightname, self::PUBLISHFAQ)
          && !Session::haveRight(self::$rightname, UPDATE)) {
         $input["is_faq"] = 1;
      }
      if (!Session::haveRight(self::$rightname, self::PUBLISHFAQ)
          && Session::haveRight(self::$rightname, UPDATE)) {
         $input["is_faq"] = 0;
      }
      return $input;
   }


   /**
    * @see CommonDBTM::prepareInputForUpdate()
   **/
   function prepareInputForUpdate($input) {

      // set title for question if empty
      if (isset($input["name"]) && empty($input["name"])) {
         $input["name"] = __('New item');
      }
      return $input;
   }


   /**
    * Print out an HTML "<form>" for knowbase item
    *
    * @param $ID
    * @param $options array
    *     - target for the Form
    *
    * @return nothing (display the form)
   **/
   function showForm($ID, $options=array()) {
      global $CFG_GLPI;

      // show kb item form
      if (!Session::haveRightsOr(self::$rightname,
                                 array(UPDATE, self::PUBLISHFAQ, self::KNOWBASEADMIN))) {
         return false;
      }

      $this->initForm($ID, $options);
      $canedit = $this->can($ID, UPDATE);

      // Load ticket solution
      if (empty($ID)
          && isset($options['item_itemtype']) && !empty($options['item_itemtype'])
          && isset($options['item_items_id']) && !empty($options['item_items_id'])) {

         if ($item = getItemForItemtype($options['item_itemtype'])) {
            if ($item->getFromDB($options['item_items_id'])) {
               $this->fields['name']   = $item->getField('name');
               $this->fields['answer'] = $item->getField('solution');
               if ($item->isField('itilcategories_id')) {
                  $ic = new ItilCategory();
                  if ($ic->getFromDB($item->getField('itilcategories_id'))) {
                     $this->fields['knowbaseitemcategories_id']
                           = $ic->getField('knowbaseitemcategories_id');
                  }
               }
            }
         }
      }
      $rand = mt_rand();

      Html::initEditorSystem('answer');
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Category name')."</td>";
      echo "<td>";
      echo "<input type='hidden' name='users_id' value=\"".Session::getLoginUserID()."\">";
      KnowbaseItemCategory::dropdown(array('value' => $this->fields["knowbaseitemcategories_id"]));
      echo "</td>";
      echo "<td>";
      if ($this->fields["date"]) {
         //TRANS: %s is the datetime of insertion
         printf(__('Created on %s'), Html::convDateTime($this->fields["date"]));
      }
      echo "</td><td>";
      if ($this->fields["date_mod"]) {
         //TRANS: %s is the datetime of update
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
      }
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'  style='display: none;'>";  // Hide FAQ AKK
      if (Session::haveRight(self::$rightname, self::PUBLISHFAQ)) {
         echo "<td>".__('Put this item in the FAQ')."</td>";
         echo "<td>";
         Dropdown::showYesNo('is_faq', $this->fields["is_faq"]);
         echo "</td>";
      } else {
         echo "<td colspan='2'>";
         if ($this->fields["is_faq"]) {
            _e('This item is part of the FAQ');
         } else {
            _e('This item is not part of the FAQ');
         }
         echo "</td>";
      }
      echo "<td>";
      $showuserlink = 0;
      if (Session::haveRight('user', READ)) {
         $showuserlink = 1;
      }
      if ($this->fields["users_id"]) {
         //TRANS: %s is the writer name
         printf(__('%1$s: %2$s'), __('Writer'), getUserName($this->fields["users_id"],
                                                            $showuserlink));
      }
      echo "</td><td>";
      //TRANS: %d is the number of view
      if ($ID) {
         printf(_n('%d view', '%d views', $this->fields["view"]),$this->fields["view"]);
      }
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1' style='display: none;'>"; // Hide FAQ Date AKK
	  
      echo "<td>".__('Visible since')."</td><td>";
      Html::showDateTimeField("begin_date", array('value'       => $this->fields["begin_date"],
                                                  'timestep'    => 1,
                                                  'maybeempty' => true,
                                                  'canedit'    => $canedit));
      echo "</td>";
      echo "<td>".__('Visible until')."</td><td>";
      Html::showDateTimeField("end_date", array('value'       => $this->fields["end_date"],
                                                'timestep'    => 1,
                                                'maybeempty' => true,
                                                'canedit'    => $canedit));
      echo "</td></tr>";



      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Subject')."</td>";
      echo "<td colspan='3'>";
      echo "<textarea cols='100' rows='5' name='name' id='name' required>".$this->fields["name"]."</textarea>";
      echo "</td>";
      echo "</tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Content')."</td>";
      echo "<td colspan='3'>";

      $cols = 100;
      $rows = 30;
      if (isset($options['_in_modal']) && $options['_in_modal']) {
         $rows = 15;
         echo Html::hidden('_in_modal', array('value' => 1));
      }

      echo "<textarea cols='$cols' rows='$rows' id='answer' name='answer' >".$this->fields["answer"];
      echo "</textarea>";
      echo "</td>";
      echo "</tr>\n";

      if ($this->isNewID($ID)) {
         echo "<tr class='tab_bg_1' style='display: none;'>";
         echo "<td>"._n('Target','Targets',1)."</td>";  // AKK to hide Target
         echo "<td>";
         $types   = array('Entity', 'Group', 'Profile', 'User');
         $addrand = Dropdown::showItemTypes('_visibility[_type]', $types);
         echo "</td><td colspan='2'>";
         $params  = array('type'     => '__VALUE__',
                          'right'    => 'knowbase',
                          'prefix'   => '_visibility',
                          'nobutton' => 1);

         Ajax::updateItemOnSelectEvent("dropdown__visibility__type_".$addrand,"visibility$rand",
                                       $CFG_GLPI["root_doc"]."/ajax/visibility.php",
                                       $params);
         echo "<span id='visibility$rand'></span>";
         echo "</td></tr>\n";
      }

      $this->showFormButtons($options);
      return true;
   } // function showForm


   /**
    * Add kb item to the public FAQ
    *
    * @return nothing
   **/
   function addToFaq() {
      global $DB;

      $DB->query("UPDATE `".$this->getTable()."`
                  SET `is_faq` = '1'
                  WHERE `id` = '".$this->fields['id']."'");

      if (isset($_SESSION['glpi_faqcategories'])) {
         unset($_SESSION['glpi_faqcategories']);
      }
   
 // $_SESSION['Knowbaseid'] =$this->fields['id'];
   
   }

   /**
    * Increase the view counter of the current knowbaseitem
    *
    * @since version 0.83
    */
   function updateCounter() {
      global $DB;

      //update counter view
      $query = "UPDATE `glpi_knowbaseitems`
                SET `view` = `view`+1
                WHERE `id` = '".$this->getID()."'";

      $DB->query($query);
//recording KB ID to the session AKK 
	 $_SESSION['Knowbaseid']=$this->getID();
   }
    function updateDeleted($knowbaseID) {
      global $DB;

      //update for delete  AKK
      /* $query = "UPDATE zcustom_glpi_knowbaseitems_deleted
                SET Deleted_user_ID = '" .  $_SESSION["glpiID"] . "' where id =" . $knowbaseID . " "; */
        $query = "UPDATE zcustom_glpi_knowbaseitems_deleted
                SET Deleted_user_ID = '" .  $_SESSION["glpiID"] . "' where id =" . $knowbaseID . "  ";       

      $DB->query($query);
	 
   }
   
   
  


   /**
    * Print out (html) show item : question and answer
    *
    * @param $options      array of options
    *
    * @return nothing (display item : question and answer)
   **/
   function showFull($options=array()) {
      global $DB, $CFG_GLPI;

      if (!$this->can($this->fields['id'], READ)) {
         return false;
      }

	  //echo("sfd");
	  
      $linkusers_id = true;
      // show item : question and answer
      if ($_SESSION["glpiactiveprofile"]["interface"] == "helpdesk"
          || !User::canView()) {
         $linkusers_id = false;
      }

      $this->updateCounter();

      $knowbaseitemcategories_id = $this->fields["knowbaseitemcategories_id"];
      $fullcategoryname          = getTreeValueCompleteName("glpi_knowbaseitemcategories",
                                                            $knowbaseitemcategories_id);

      $tmp = "<a href='".$this->getSearchURL().
             "?knowbaseitemcategories_id=$knowbaseitemcategories_id'>".$fullcategoryname."</a>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_3'><th colspan='4'>".sprintf(__('%1$s: %2$s'), __('Category'), $tmp);
      echo "</th></tr>";

      echo "<tr class='tab_bg_3'><td class='left' colspan='4'><h2>".__('Subject')."</h2>";
      if (KnowbaseItemTranslation::canBeTranslated($this)) {
         echo KnowbaseItemTranslation::getTranslatedValue($this, 'name');
      } else {
         echo $this->fields["name"];
      }

      echo "</td></tr>";
      echo "<tr class='tab_bg_3'><td class='left' colspan='4'><h2>".__('Content')."</h2>\n";

      echo "<div id='kbanswer'>";
      if (KnowbaseItemTranslation::canBeTranslated($this)) {
         $answer = KnowbaseItemTranslation::getTranslatedValue($this, 'answer');
      } else {
         $answer = $this->fields["answer"];
      }
      echo Toolbox::unclean_html_cross_side_scripting_deep($answer);
      echo "</div>";
      echo "</td></tr>";

      echo "<tr><th class='tdkb'  colspan='2'>";
      if ($this->fields["users_id"]) {
         // Integer because true may be 2 and getUserName return array
         if ($linkusers_id) {
            $linkusers_id = 1;
         } else {
            $linkusers_id = 0;
         }

		 
		 // mod by AKK for Knowledge base Article for last update user 
         printf(__('%1$s: %2$s'), __('Last Update by '), getUserName($this->fields["users_id"],
                $linkusers_id));
         echo "<br>";
      }

      
      if ($this->fields["date_mod"]) {
         //TRANS: %s is the datetime of update
         printf(__('Last update on %s'), Html::convDateTime($this->fields["date_mod"]));
		 echo "<br>";
      }
	  /* if ($this->fields["date"]) {
         //TRANS: %s is the datetime of update
         printf(__('Created on %s'), Html::convDateTime($this->fields["date"]));
         echo "<br>";
      } */
	  
	  
	//  KBGET_created_user();
	   //printf(__(''), $this->KBGET_created_user()." by");
         
		 echo $this->KBGET_created_user();
		 echo ("<br>");
	    if ($this->fields["date"]) {
         //TRANS: %s is the datetime of update
         printf(__('Created on %s'), Html::convDateTime($this->fields["date"]));
         echo "<br>";
      }

      echo "</th>";
      echo "<th class='tdkb' colspan='2'>";
      if ($this->countVisibilities() == 0) {
         echo "<span class='red'>".__('Unpublished')."</span><br>";
      }

      printf(_n('%d view', '%d views', $this->fields["view"]), $this->fields["view"]);
      echo "<br>";
      if ($this->fields["is_faq"]) {
         _e('This item is part of the FAQ');
      } else {
         _e('This item is not part of the FAQ');
      }
      echo "</th></tr>";
      echo "</table>";

      return true;
   }


   /**
    * Print out an HTML form for Search knowbase item
    *
    * @param $options   $_GET
    *
    * @return nothing (display the form)
   **/
   function searchForm($options) {
      global $CFG_GLPI;

      if (!$CFG_GLPI["use_public_faq"]
          && !Session::haveRightsOr(self::$rightname, array(READ, self::READFAQ))) {
         return false;
      }

      // Default values of parameters
      $params["contains"]                  = "";
      $params["target"]                    = $_SERVER['PHP_SELF'];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      echo "<div>";
      echo "<form method='get' action='".$this->getSearchURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><td class='right' width='50%'>";
      
	  
	  echo "<input type='text' size='50' name='contains' value=\"".
             Html::cleanInputText(stripslashes($params["contains"]))."\"></td>";
	


	  
			  //  echo "<input type='text' size='50' name='contains'></td>";
      echo "<td class='left'>";
      echo "<input type='submit' value=\""._sx('button','Search')."\" class='submit'></td></tr>";
      echo "</table>";
      if (isset($options['item_itemtype'])
          && isset($options['item_items_id'])) {
         echo "<input type='hidden' name='item_itemtype' value='".$options['item_itemtype']."'>";
         echo "<input type='hidden' name='item_items_id' value='".$options['item_items_id']."'>";
      }
      Html::closeForm();

      echo "</div>";
   }


   /**
    * Print out an HTML "<form>" for Search knowbase item
    *
    * @since version 0.84
    *
    * @param $options   $_GET
    *
    * @return nothing (display the form)
   **/
   function showBrowseForm($options) {
      global $CFG_GLPI;

      if (!$CFG_GLPI["use_public_faq"]
          && !Session::haveRightsOr(self::$rightname, array(READ, self::READFAQ))) {
         return false;
      }

      // Default values of parameters
      $params["knowbaseitemcategories_id"] = "";

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }
      $faq = !Session::haveRight(self::$rightname, READ);

      // Category select not for anonymous FAQ
      if (Session::getLoginUserID()
          && !$faq) {
         echo "<div>";
         echo "<form method='get' action='".$this->getSearchURL()."'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'><td class='right' width='50%'>".__('Category')."&nbsp;";
         KnowbaseItemCategory::dropdown(array('value' => $params["knowbaseitemcategories_id"]));
         echo "</td><td class='left'>";
         echo "<input type='submit' value=\""._sx('button','Post')."\" class='submit'></td>";
         echo "</tr></table>";
         if (isset($options['item_itemtype'])
             && isset($options['item_items_id'])) {
            echo "<input type='hidden' name='item_itemtype' value='".$options['item_itemtype']."'>";
            echo "<input type='hidden' name='item_items_id' value='".$options['item_items_id']."'>";
         }
         Html::closeForm();
         echo "</div>";
      }
   }


   /**
    * Print out an HTML form for Search knowbase item
    *
    * @since version 0.84
    *
    * @param $options   $_GET
    *
    * @return nothing (display the form)
   **/
   function showManageForm($options) {
      global $CFG_GLPI;

      if (!Session::haveRightsOr(self::$rightname,
                                 array(UPDATE, self::PUBLISHFAQ, self::KNOWBASEADMIN))) {
         return false;
      }
      $params['unpublished'] = 'my';
      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }

      $faq = !Session::haveRight(self::$rightname, UPDATE);

      echo "<div>";
      echo "<form method='get' action='".$this->getSearchURL()."'>";
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr class='tab_bg_2'><td class='right' width='50%'>";
      $values = array('myunpublished' => __('My unpublished articles'),
                      'allmy'         => __('All my articles'));
      if (Session::haveRight(self::$rightname, self::KNOWBASEADMIN)) {
         $values['allunpublished'] = __('All unpublished articles');
      }
      Dropdown::showFromArray('unpublished', $values, array('value' => $params['unpublished']));
      echo "</td><td class='left'>";
      echo "<input type='submit' value=\""._sx('button','Post')."\" class='submit'></td>";
      echo "</tr></table>";
      Html::closeForm();
      echo "</div>";
   }


   /**
    * Build request for showList
    *
    * @since version 0.83
    *
    * @param $params array  (contains, knowbaseitemcategories_id, faq)
    * @param $type   string search type : browse / search (default search)
    *
    * @return String : SQL request
   **/
   static function getListRequest(array $params, $type='search') {
      global $DB;

      // Lists kb Items
      $where     = "";
      $order     = "";
      $score     = "";
      $addselect = "";
      $join  = self::addVisibilityJoins(true);

      switch ($type) {
         case 'myunpublished' :
            break;

         case 'allmy' :
            break;

         case 'allunpublished' :
            break;

         default :
            // Build query
            if (Session::getLoginUserID() && $type != 'myunpublished') {
               $where = self::addVisibilityRestrict();
            } else {
               // Anonymous access
               if (Session::isMultiEntitiesMode()) {
                  $where = " (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                              AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
               }
            }
            break;
      }


      if (empty($where)) {
         $where = '1 = 1';
      }

      if ($params['faq']) { // helpdesk
         $where .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      }

      // a search with $contains
      switch ($type) {
         case 'allmy' :
            $where .= " AND `glpi_knowbaseitems`.`users_id` = '".Session::getLoginUserID()."'";
			$order=" order by date_mod desc ";  // akk to display 
            break;

         case 'myunpublished' :
            $where .= " AND `glpi_knowbaseitems`.`users_id` = '".Session::getLoginUserID()."'
                        AND (`glpi_entities_knowbaseitems`.`entities_id` IS NULL
                              AND `glpi_knowbaseitems_profiles`.`profiles_id` IS NULL
                              AND `glpi_groups_knowbaseitems`.`groups_id` IS NULL
                              AND `glpi_knowbaseitems_users`.`users_id` IS NULL) ";
            
			 $order=" order by date_mod desc ";
			
			break;

         case 'allunpublished' :
            // Only published
           /*  $where .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NULL
                              AND `glpi_knowbaseitems_profiles`.`profiles_id` IS NULL
                              AND `glpi_groups_knowbaseitems`.`groups_id` IS NULL
                              AND `glpi_knowbaseitems_users`.`users_id` IS NULL) "; */
							  
							  $where .= " AND `glpi_knowbaseitems`.`id`  not in (select `glpi_entities_knowbaseitems`.`knowbaseitems_id` from `glpi_entities_knowbaseitems` ) ";
         

             $order=" order by date_mod desc ";

     		 break;

         case 'search' :
            if (strlen($params["contains"]) > 0) {
               $search  = Toolbox::unclean_cross_side_scripting_deep($params["contains"]);

               $addscore = '';
               if (KnowbaseItemTranslation::isKbTranslationActive()) {
                  $addscore = ",`glpi_knowbaseitemtranslations`.`name`,
                                 `glpi_knowbaseitemtranslations`.`answer`";
               }
               $score   = " ,MATCH(`glpi_knowbaseitems`.`name`, `glpi_knowbaseitems`.`answer` $addscore)
                           AGAINST('$search' IN BOOLEAN MODE) AS SCORE ";

               $where_1 = $where." AND MATCH(`glpi_knowbaseitems`.`name`,
                                             `glpi_knowbaseitems`.`answer` $addscore)
                          AGAINST('$search' IN BOOLEAN MODE) ";

               // Add visibility date
               $where_1 .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                                   OR `glpi_knowbaseitems`.`begin_date` < NOW())
                             AND (`glpi_knowbaseitems`.`end_date` IS NULL
                                  OR `glpi_knowbaseitems`.`end_date` > NOW()) ";

               $order   = "ORDER BY `SCORE` DESC";

               // preliminar query to allow alternate search if no result with fulltext
               $query_1   = "SELECT COUNT(`glpi_knowbaseitems`.`id`)
                             FROM `glpi_knowbaseitems`
                             $join
                             WHERE $where_1";
             



			 $result_1  = $DB->query($query_1);
               $numrows_1 = $DB->result($result_1,0,0);

               if ($numrows_1 <= 0) {// not result this fulltext try with alternate search
                  $search1 = array(/* 1 */   '/\\\"/',
                                   /* 2 */   "/\+/",
                                   /* 3 */   "/\*/",
                                   /* 4 */   "/~/",
                                   /* 5 */   "/</",
                                   /* 6 */   "/>/",
                                   /* 7 */   "/\(/",
                                   /* 8 */   "/\)/",
                                   /* 9 */   "/\-/");
                  $contains = preg_replace($search1,"", $params["contains"]);
                  $addwhere = '';
                  if (KnowbaseItemTranslation::isKbTranslationActive()) {
                     $addwhere = " OR `glpi_knowbaseitemtranslations`.`name` ".Search::makeTextSearch($contains)."
                                    OR `glpi_knowbaseitemtranslations`.`answer` ".Search::makeTextSearch($contains);
                  }
                  $where   .= " AND (`glpi_knowbaseitems`.`name` ".Search::makeTextSearch($contains)."
                                 OR `glpi_knowbaseitems`.`answer` ".Search::makeTextSearch($contains)."
                                 $addwhere)";
               } else {
                  $where = $where_1;
               }
            }
            break;

         case 'browse' :
            $where .= " AND (`glpi_knowbaseitems`.`knowbaseitemcategories_id`
                           = '".$params["knowbaseitemcategories_id"]."')";
            // Add visibility date
            $where .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                             OR `glpi_knowbaseitems`.`begin_date` < NOW())
                        AND (`glpi_knowbaseitems`.`end_date` IS NULL
                             OR `glpi_knowbaseitems`.`end_date` > NOW()) ";

            $order  = " ORDER BY `glpi_knowbaseitems`.`name` ASC";
            break;
      }

      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                         AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."')";
         $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer ";
      }

      
	  
	  if ($type=='search')
	  {
		  $Check_published="inner join glpi_entities_knowbaseitems K2 on K2.knowbaseitems_id = glpi_knowbaseitems.id";
		  $Check_published1="inner join glpi_entities_knowbaseitems K2 on K2.knowbaseitems_id = glpi_knowbaseitems.id";
		  
	  }
	  else
	  {
		  
		   $Check_published="";
		    $Check_published1="";
	  }
  
	  
	  
	  
	  $query = "SELECT DISTINCT `glpi_knowbaseitems`.*,
                       `glpi_knowbaseitemcategories`.`completename` AS category
                       $addselect
                       $score
                FROM `glpi_knowbaseitems`
                $join
                LEFT JOIN `glpi_knowbaseitemcategories`
                     ON (`glpi_knowbaseitemcategories`.`id`
                           = `glpi_knowbaseitems`.`knowbaseitemcategories_id`)
                $Check_published
				
				
				WHERE $where
                   ";
				
				
				//adding Like query to make effective search AKK
				
				if ($type=='search')
				{
				$query =  $query . "  union SELECT DISTINCT `glpi_knowbaseitems`.*,
                       `glpi_knowbaseitemcategories`.`completename` AS category
                       $addselect
                       $score
                FROM `glpi_knowbaseitems`
                $join
                LEFT JOIN `glpi_knowbaseitemcategories`
                     ON (`glpi_knowbaseitemcategories`.`id`
                           = `glpi_knowbaseitems`.`knowbaseitemcategories_id`)
              $Check_published1

			   WHERE ((glpi_knowbaseitems.name like '%$search%') or (glpi_knowbaseitems.answer like '%$search%'))  
               ";
				}
		
		
		
		
		
		$query=$query.	 $order;  

      
	  
	  
	  
	  return $query;
  

  }

   

   /**
    * Print out list kb item
    *
    * @param $options            $_GET
    * @param $type      string   search type : browse / search (default search)
   **/
   static function showList($options, $type='search') {
      global $DB, $CFG_GLPI;

      // Default values of parameters
      $params['faq']                       = !Session::haveRight(self::$rightname, READ);
      $params["start"]                     = "0";
      $params["knowbaseitemcategories_id"] = "0";
      $params["contains"]                  = "";
      $params["target"]                    = $_SERVER['PHP_SELF'];

      if (is_array($options) && count($options)) {
         foreach ($options as $key => $val) {
            $params[$key] = $val;
         }
      }
      $ki = new self();
      switch ($type) {
         case 'myunpublished' :
            if (!Session::haveRightsOr(self::$rightname, array(UPDATE, self::PUBLISHFAQ))) {
               return false;
            }
            break;

         case 'allunpublished' :
            if (!Session::haveRight(self::$rightname, self::KNOWBASEADMIN)) {
               return false;
            }
            break;

         default :
            break;
      }

      if (!$params["start"]) {
         $params["start"] = 0;
      }

	  
	  
      $query = self::getListRequest($params, $type);
	//echo($query );
	  
      // Get it from database
      if ($result = $DB->query($query)) {
         $KbCategory = new KnowbaseItemCategory();
         $title      = "";
         if ($KbCategory->getFromDB($params["knowbaseitemcategories_id"])) {
            $title = (empty($KbCategory->fields['name']) ?"(".$params['knowbaseitemcategories_id'].")"
                                                         : $KbCategory->fields['name']);
            $title = sprintf(__('%1$s: %2$s'), __('Category'), $title);
         }

         Session::initNavigateListItems('KnowbaseItem', $title);

         $numrows    = $DB->numrows($result);
         $list_limit = $_SESSION['glpilist_limit'];

         $showwriter = in_array($type, array('myunpublished', 'allunpublished', 'allmy'));

         // Limit the result, if no limit applies, use prior result
         if (($numrows > $list_limit)
             && !isset($_GET['export_all'])) {
            $query_limit   = $query ." LIMIT ".intval($params["start"]).", ".intval($list_limit)." ";
            $result_limit  = $DB->query($query_limit);
            $numrows_limit = $DB->numrows($result_limit);

         } else {
            $numrows_limit = $numrows;
            $result_limit  = $result;
         }

         if ($numrows_limit > 0) {
            // Set display type for export if define
            $output_type = Search::HTML_OUTPUT;

            if (isset($_GET["display_type"])) {
               $output_type = $_GET["display_type"];
            }

            // Pager
            $parameters = "start=".$params["start"]."&amp;knowbaseitemcategories_id=".
                          $params['knowbaseitemcategories_id']."&amp;contains=".
                          $params["contains"]."&amp;is_faq=".$params['faq'];

            if (isset($options['item_itemtype'])
                && isset($options['item_items_id'])) {
               $parameters .= "&amp;item_items_id=".$options['item_items_id']."&amp;item_itemtype=".
                               $options['item_itemtype'];
            }

            if ($output_type == Search::HTML_OUTPUT) {
               Html::printPager($params['start'], $numrows,
                                Toolbox::getItemTypeSearchURL('KnowbaseItem'), $parameters,
                                'KnowbaseItem');
            }

            $nbcols = 1;
            // Display List Header
            echo Search::showHeader($output_type, $numrows_limit+1, $nbcols);

            echo Search::showNewLine($output_type);
            $header_num = 1;
            echo Search::showHeaderItem($output_type, __('Subject'), $header_num);

            if ($output_type != Search::HTML_OUTPUT) {
               echo Search::showHeaderItem($output_type, __('Content'), $header_num);
            }

            if ($showwriter) {
               echo Search::showHeaderItem($output_type, __('Writer'), $header_num);
            }
            echo Search::showHeaderItem($output_type, __('Category'), $header_num);

            if (isset($options['item_itemtype'])
                && isset($options['item_items_id'])
                && ($output_type == Search::HTML_OUTPUT)) {
               echo Search::showHeaderItem($output_type, '&nbsp;', $header_num);
            }

            // Num of the row (1=header_line)
            $row_num = 1;
            for ($i=0 ; $i<$numrows_limit ; $i++) {
               $data = $DB->fetch_assoc($result_limit);

               Session::addToNavigateListItems('KnowbaseItem', $data["id"]);
               // Column num
               $item_num = 1;
               $row_num++;
               echo Search::showNewLine($output_type, $i%2);

               $item = new self;
               $item->getFromDB($data["id"]);
               $name   = $data["name"];
               $answer = $data["answer"];
               // Manage translations
               if (isset($data['transname']) && !empty($data['transname'])) {
                  $name   = $data["transname"];
               }
               if (isset($data['transanswer']) && !empty($data['transanswer'])) {
                  $answer = $data["transanswer"];
               }

               if ($output_type == Search::HTML_OUTPUT) {
                  $toadd = '';
                  if (isset($options['item_itemtype'])
                      && isset($options['item_items_id'])) {
                     $href  = " href='#' onClick=\"".Html::jsGetElementbyID('kbshow'.$data["id"]).".dialog('open');\"" ;
                     $toadd = Ajax::createIframeModalWindow('kbshow'.$data["id"],
                                                            $CFG_GLPI["root_doc"].
                                                               "/front/knowbaseitem.form.php?id=".$data["id"],
                                                            array('display' => false));
                  } else {
                     $href = " href=\"".$CFG_GLPI['root_doc']."/front/knowbaseitem.form.php?id=".
                                    $data["id"]."\" ";
                  }

                  echo Search::showItem($output_type,
                                        "<div class='kb'>$toadd<a ".
                                          ($data['is_faq']?" class='pubfaq' ":" class='knowbase' ").
                                          " $href>".Html::resume_text($name, 80)."</a></div>
                                          <div class='kb_resume'>".
                                          Html::resume_text(Html::clean(Toolbox::unclean_cross_side_scripting_deep($answer)),
                                                            600)."</div>",
                                        $item_num, $row_num);
               } else {
                  echo Search::showItem($output_type, $name, $item_num, $row_num);
                  echo Search::showItem($output_type,
                     Html::clean(Toolbox::unclean_cross_side_scripting_deep(html_entity_decode($answer,
                                                                                               ENT_QUOTES,
                                                                                               "UTF-8"))),
                                $item_num, $row_num);
               }

               $showuserlink = 0;
               if (Session::haveRight('user', READ)) {
                  $showuserlink = 1;
               }
               if ($showwriter) {
                  echo Search::showItem($output_type, getUserName($data["users_id"], $showuserlink),
                                           $item_num, $row_num);
               }

               $categ = $data["category"];
               if ($output_type == Search::HTML_OUTPUT) {
                  $cathref = $ki->getSearchURL()."?knowbaseitemcategories_id=".
                              $data["knowbaseitemcategories_id"].'&amp;forcetab=Knowbase$2';
                  $categ   = "<a href='$cathref'>".$categ.'</a>';
               }
               echo Search::showItem($output_type, $categ, $item_num, $row_num);


               if (isset($options['item_itemtype'])
                   && isset($options['item_items_id'])
                   && ($output_type == Search::HTML_OUTPUT)) {

                  $content = "<a href='".Toolbox::getItemTypeFormURL($options['item_itemtype']).
                               "?load_kb_sol=".$data['id']."&amp;id=".$options['item_items_id'].
                               "&amp;forcetab=".$options['item_itemtype']."$2'>".
                               __('Use as a solution')."</a>";
                  echo Search::showItem($output_type, $content, $item_num, $row_num);
               }


               // End Line
               echo Search::showEndLine($output_type);
            }

            // Display footer
            if (($output_type == Search::PDF_OUTPUT_LANDSCAPE)
                || ($output_type == Search::PDF_OUTPUT_PORTRAIT)) {
               echo Search::showFooter($output_type,
                                       Dropdown::getDropdownName("glpi_knowbaseitemcategories",
                                                                 $params['knowbaseitemcategories_id']));
            } else {
               echo Search::showFooter($output_type);
            }
            echo "<br>";
            if ($output_type == Search::HTML_OUTPUT) {
               Html::printPager($params['start'], $numrows,
                                Toolbox::getItemTypeSearchURL('KnowbaseItem'), $parameters,
                                'KnowbaseItem');
            }

         } else {
            echo "<div class='center b'>".__('No item found')."</div>";
         }
      }
   }


   /**
    * Print out list recent or popular kb/faq
    *
    * @param $type      type : recent / popular / not published
    *
    * @return nothing (display table)
   **/
   
   static function showKBDeleted($type) {
      global $DB, $CFG_GLPI;

      //$faq = !Session::haveRight(self::$rightname, READ);

     /*  if ($type == "recent") {
         $orderby = "ORDER BY `date` DESC";
         $title   = __('Recent entries');
      } else if ($type == 'lastupdate') {
         $orderby = "ORDER BY `date_mod` DESC";
         $title   = __('Last updated entries');
      } else {
         $orderby = "ORDER BY `view` DESC";
         $title   = __('Most popular questions');
      } */
	  
	  $title   =('<u><a href="customdeletedknowbaseitem.php"> View Deleted Articles </a> </u> ');
	  
     //$title   =.($_SESSION["glpiactiveprofile"]['id']);
	
	
          
    /*   $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      } */


      /* // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)"; */

     /*  // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) "; */


     /*  if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      } */

/* 
      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."')";
         $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer ";
      } */


      /* $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $addselect
                FROM `glpi_knowbaseitems`
                $join
                $faq_limit
                $orderby
                LIMIT 10"; */
				/* 
				$query = "Select * from glpi_knowbaseitems_deleted";
    


	$result = $DB->query($query);
      $number = $DB->numrows($result); */

      //if ($number > 0) {
     

  	 
        echo($title);

		//echo "<table class='tab_cadrehov'>";
        // echo "<tr class='noHover'><th>".$title."</th></tr>";
        // while ($data = $DB->fetch_assoc($result)) {
            //$name = $data['name'];

           /*  if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            } */
            //echo "<tr class='tab_bg_2'><td class='left'>";
           /*  echo "<a ".($data['is_faq']?" class='pubfaq' ":" class='knowbase' ")." href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."\">".
                  Html::resume_text($name,80)."</a></td></tr>"; */
				  //echo("<a>".Html::resume_text($name,80)."</a>");
         //}
        // echo "</table>";
      //}
   }
   
 
   
   static function Article_deleted_view($type) {
      global $DB, $CFG_GLPI;
	  


      //$faq = !Session::haveRight(self::$rightname, READ);

     /*  if ($type == "recent") {
         $orderby = "ORDER BY `date` DESC";
         $title   = __('Recent entries');
      } else if ($type == 'lastupdate') {
         $orderby = "ORDER BY `date_mod` DESC";
         $title   = __('Last updated entries');
      } else {
         $orderby = "ORDER BY `view` DESC";
         $title   = __('Most popular questions');
      } */
	  
	  $title   =(' Deleted  Articles Details');
	  
	  
	  
	  if ($type=="IDAC") // order by ID ASC
		 {
			  $orderby = "ORDER BY id  asc";
            
			 
		 }
		else if ($type=="IDDC")  // order by ID DESC
		 {
			  $orderby = "ORDER BY id  desc";
            
			 
		 }
		 
		 else if ($type=="SUBASC") // order by Subject ASC
		 {
			  $orderby = "ORDER BY name  asc";
             
			 
		 }
		 
		 else if ($type=="SUBDSC") // order by Subject DSC
		 {
			  $orderby = "ORDER BY name  desc";
             
			 
		 }
		 
		 else if ($type=="CATASC") // order by category ASC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  asc";
             
			 
		 }
		 
		 else if ($type=="CATDSC") // order by category DSC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  desc";
             
			 
		 }
		 
		 else if ($type=="DTCNASC") // order by created on ASC
		 {
			  $orderby = "ORDER BY date_created  asc";
            
			 
		 }
		 
		 else if ($type=="DTCNDSC") // order by created on DSC
		 {
			  $orderby = "ORDER BY date_created  desc";
             
			 
		 }
		 
		 else if ($type=="CBASC") // order by created by ASC
		 {
			  $orderby = "ORDER BY concat(glpi_users_2.realname,' ',glpi_users_2.firstname)  asc";
             
			 
		 }
		 
		 else if ($type=="CBDSC") // order by created by DSC
		 {
			  $orderby = "ORDER BY concat(glpi_users_2.realname,' ',glpi_users_2.firstname)  desc";
            
			 
		 }

		 
		 else if ($type=="LUDASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY date_mod   asc";
             
			 
		 }
		 
		 else if ($type=="LUDDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY date_mod  desc";
             
			 
		 }
		  else if ($type=="LUUASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY concat(glpi_users_1.realname,' ', glpi_users_1.firstname)   asc";
             
			 
		 }
		 
		 else if ($type=="LUUDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_users_1.realname,' ', glpi_users_1.firstname)  desc";
            
		 }
		 
		 
		  else if ($type=="CONASC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY answer  ASC";
            
		 }
		 
		  else if ($type=="CONDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY answer  desc";
            
		 }
		 
		  else if ($type=="DELBYASC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_users.realname,' ',glpi_users.firstname)  ASC";
            
		 }
		 
		  else if ($type=="DELBYDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_users.realname,' ',glpi_users.firstname)  desc";
            
		 }
		 
		  else if ($type=="DELONASC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY Deleted_Date  ASC";
            
		 }
		 
		  else if ($type=="DELONDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY Deleted_Date  desc";
            
		 }
	
	
	  else // default recent entries sort order 
		 {
		 $orderby = "ORDER BY zcustom_glpi_knowbaseitems_deleted.Deleted_Date DESC";
       
         }
	  
    // $title   =($_SESSION["glpiactiveprofile"]['id']);
	
	
          
    /*   $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      } */


      /* // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)"; */

     /*  // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) "; */


     /*  if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      } */

/* 
      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."')";
         $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer ";
      } */


      /* $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $addselect
                FROM `glpi_knowbaseitems`
                $join
                $faq_limit
                $orderby
                LIMIT 10"; */
				
				$query = "
SELECT zcustom_glpi_knowbaseitems_deleted.id,
       zcustom_glpi_knowbaseitems_deleted.knowbaseitemcategories_id,
       zcustom_glpi_knowbaseitems_deleted.name,
       zcustom_glpi_knowbaseitems_deleted.answer,
       zcustom_glpi_knowbaseitems_deleted.is_faq,
       zcustom_glpi_knowbaseitems_deleted.view,
       zcustom_glpi_knowbaseitems_deleted.`date`,
       zcustom_glpi_knowbaseitems_deleted.date_mod,
       zcustom_glpi_knowbaseitems_deleted.begin_date,
       zcustom_glpi_knowbaseitems_deleted.end_date,
       zcustom_glpi_knowbaseitems_deleted.Deleted_Date,
       zcustom_glpi_knowbaseitems_deleted.users_id,
       zcustom_glpi_knowbaseitems_deleted.Deleted_user_ID,
       concat(glpi_users.realname,' ',glpi_users.firstname) AS DeletedUser,
       concat(glpi_users_1.realname,' ', glpi_users_1.firstname)AS Owner,
       zcustom_glpi_knowbaseitems_creationlog.User_id,
       zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID,
       concat(glpi_users_2.realname,' ',glpi_users_2.firstname) AS CreatedBy, glpi_knowbaseitemcategories.completename,Date as CreatedOn,zcustom_glpi_knowbaseitems_deleted.date_mod as LASTUPDATEON  
  FROM (((zcustom_glpi_knowbaseitems_deleted zcustom_glpi_knowbaseitems_deleted
         
          
          
          left JOIN glpi_users glpi_users_1
             ON (zcustom_glpi_knowbaseitems_deleted.users_id =
                    glpi_users_1.id))
        
         left JOIN glpi_users glpi_users
            ON (zcustom_glpi_knowbaseitems_deleted.Deleted_user_ID =
                   glpi_users.id))

       left JOIN
        zcustom_glpi_knowbaseitems_creationlog zcustom_glpi_knowbaseitems_creationlog
           ON (zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID =
                  zcustom_glpi_knowbaseitems_deleted.id))

       left JOIN glpi_users glpi_users_2
          ON (zcustom_glpi_knowbaseitems_creationlog.User_id =
                 glpi_users_2.id)
           
           left JOIN glpi_knowbaseitemcategories glpi_knowbaseitemcategories
          ON (zcustom_glpi_knowbaseitems_deleted.knowbaseitemcategories_id =
                 glpi_knowbaseitemcategories.id)        
                 
 $orderby

";
    


	$result = $DB->query($query);
      $number = $DB->numrows($result); 

      if ($number > 0) {
     

  	 
      //  echo($query);
		
		//KnowbaseItemTranslation::getTranslatedValue($this, 'answer')

		echo "<table class='tab_cadrehov'>";
		
        // echo "<tr ><th>Article ID</th><th>Subject</th> <th>Content</th> <th>Category</th> <th>CreatedOn</th><th>Created By</th><th>Last Update ON</th><th>Last Update by</th><th>Deleted by</th> <th>Deleted on</th> </tr>";
         echo "<tr class='tab_bg_2'><th> ID <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=IDAC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=IDDC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a> </th><th class='left'> Subject <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=SUBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=SUBDSC' ><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th> <th class='left'> Content <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CONASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CONDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Category <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CATASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CATDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DTCNASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DTCNDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=CBDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=LUDASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=LUDDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=LUUASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=LUUDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Deleted by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DELBYASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DELBYDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Deleted on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DELONASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?DelSortorder=DELONDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th>";        


		while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
          //  $Content=$data['answer'];
		  
		  
		 $Content= $data['answer'];
			$deleted_user=$data['UserName'];
			$deleted_date=$data['Deleted_Date'];
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            echo "<tr class='tab_bg_2'> <td>".$data['id']."</td><td class='left'>";
           /*  echo "<a ".($data['is_faq']?" class='pubfaq' ":" class='knowbase' ")." href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."\">".
                  Html::resume_text($name,80)."</a></td></tr>"; */
				  echo("<b>".Html::resume_text($name,80)."</b>");
         
		   echo("</td>");
		   
		   
		   
		    echo("<td class='left'>");
		    //echo($Content);
			echo(Toolbox::unclean_html_cross_side_scripting_deep($Content));
		   echo("</td>");
		   
		    echo("<td class='left'>");
		    echo("".Html::resume_text($data['completename'],80)."");
		   echo("</td>");
		   
		   echo("<td class='left'>");
		    echo("".Html::resume_text($data['CreatedOn'],80)."");
		   echo("</td>");
		   
		   
		    echo("<td class='left'>");
		    echo("".Html::resume_text($data['CreatedBy'],80)."");
		   echo("</td>");
		   
		  echo("<td class='left'>");
		    echo("".Html::resume_text($data['LASTUPDATEON'],80)."");
		   echo("</td>");
		   
		   
		   echo("<td class='left'>");
		    echo("".Html::resume_text($data['Owner'],80)."");
		   echo("</td>");
		   
		   echo("<td class='left'>");
		    echo("".Html::resume_text($data['DeletedUser'],80)."");
		   echo("</td>");
		   
		   
		    echo("<td class='left'>");
		    echo("".Html::resume_text($deleted_date,80)."");
		   echo("</td>");
		 
		 
		 
		 }
         echo "</table>";
      }
   }

   
   
   static function KBGET_created_user($type) {
      global $DB, $CFG_GLPI;

      //$faq = !Session::haveRight(self::$rightname, READ);

     /*  if ($type == "recent") {
         $orderby = "ORDER BY `date` DESC";
         $title   = __('Recent entries');
      } else if ($type == 'lastupdate') {
         $orderby = "ORDER BY `date_mod` DESC";
         $title   = __('Last updated entries');
      } else {
         $orderby = "ORDER BY `view` DESC";
         $title   = __('Most popular questions');
      } */
	  
	  $title   =(' Deleted  Articles Details');
	//  $ID      = KnowbaseID();
	  //$this->KnowbaseID();
	 // echo($_SESSION['Knowbaseid']);
	  
     //$title   =.($_SESSION["glpiactiveprofile"]['id']);
	
	
          
    /*   $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      } */


      /* // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)"; */

     /*  // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) "; */


     /*  if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      } */

/* 
      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."')";
         $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer ";
      } */


      /* $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $addselect
                FROM `glpi_knowbaseitems`
                $join
                $faq_limit
                $orderby
                LIMIT 10"; */
				
			/* 	$query = "SELECT glpi_users.name
  FROM glpidemo.glpi_knowbaseitems glpi_knowbaseitems
       INNER JOIN glpidemo.glpi_users glpi_users
          ON (glpi_knowbaseitems.users_id = glpi_users.id) where glpi_knowbaseitems.id =" .$_SESSION['Knowbaseid']; */
		  
		  
		  
		 	$query = "SELECT glpi_users.name
  FROM zcustom_glpi_knowbaseitems_creationlog
      JOIN glpi_users glpi_users
          ON (zcustom_glpi_knowbaseitems_creationlog.User_id = glpi_users.id) where zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID =". $_SESSION['Knowbaseid'];
    
 //echo($title);

	$result = $DB->query($query);
      $number = $DB->numrows($result); 

      if ($number > 0) {
     

  	 
       

		//echo "<table class='tab_cadrehov'>";
         //echo "<tr class='noHover'><th>Subject</th> <th>Deleted by</th> <th>Deleted on</th> </tr>";
         while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
            $deleted_user=$data['UserName'];
			$deleted_date=$data['date_mod'];
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            //echo "<tr class='tab_bg_2'><td class='left'>";
           /*  echo "<a ".($data['is_faq']?" class='pubfaq' ":" class='knowbase' ")." href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."\">".
                  Html::resume_text($name,80)."</a></td></tr>"; */
				  echo("<b> Created By ".Html::resume_text($name,80)."</b>"); 
         
		   //echo("</td>");  
		   
		   
		   
		 
		 
		 
		 }
         ///echo "</table>";
		 
      }
   }
   
   
   
   
   static function showRecentPopular($type) {
      global $DB, $CFG_GLPI;

      $faq = !Session::haveRight(self::$rightname, READ);

      if ($type == "recent") {
         $orderby = "ORDER BY `date`  DESC";
         $title   = __('Recent entries');
      } else if ($type == 'lastupdate') {
         $orderby = "ORDER BY `date_mod` DESC";
         $title   = __('Last updated entries');
      } else {
         $orderby = "ORDER BY `view` DESC";
         $title   = __('Most popular entries');
      }
	  
	  
	  
	  
	  

      $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      }


      // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)";

      // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) ";


      if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      }
	  
	  	 
		 $join_CreatedLog = "LEFT JOIN zcustom_glpi_knowbaseitems_creationlog
                     ON (glpi_knowbaseitems.id = zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID) 
                      LEFT JOIN glpi_users as glpi_knowbaseitems_users_createlog on     
                       (glpi_knowbaseitems_users_createlog.id =zcustom_glpi_knowbaseitems_creationlog.User_id)
					   LEFT JOIN glpi_users as glpi_knowbaseitems_users_updatelog on     
                       (glpi_knowbaseitems_users_updatelog.id =glpi_knowbaseitems.Users_id)
                          LEFT JOIN glpi_knowbaseitemcategories on (glpi_knowbaseitemcategories.id=glpi_knowbaseitems.knowbaseitemcategories_id)
						  ";


      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."') ";
         
	
		 
		 $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer  ";
      }
	  
	  $AddCustomized_column=",zcustom_glpi_knowbaseitems_creationlog.Date_Created,concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname) as createduser , concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname) as LastUpdateuser,glpi_knowbaseitemcategories.completename as CategoryName";
	  


      $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $AddCustomized_column   $addselect
                FROM `glpi_knowbaseitems`
                 $join  $join_CreatedLog  
                $faq_limit
                $orderby
                LIMIT 30";
				
				 echo($query );
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number > 0) {
		  
		 // echo($CFG_GLPI['root_doc'] );
        

    		echo "<table class='tab_cadrehov'>";
         echo "<tr class='noHover'><th colspan=7>".$title."</th></tr>";
        echo "<tr class='tab_bg_2'><th> ID <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?O=yu'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a> </th><th class='left'> Subject <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th> <th class='left'> Category <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created on <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created by <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update on <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update by <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th>";
		while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
           
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            echo "<tr class='tab_bg_2'><td> " .$data["id"]. "</td><td class='left'>";
            echo "<a ".($data['is_faq']?" class='pubfaq' ":" class='knowbase' ")." href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>";
				  
				  
				 
            echo("<td>" .$data["CategoryName"]. "</td>");
			echo("<td>" .$data["date"]. "</td>");
			  echo("<td>" .$data["createduser"]. "</td>");
			  echo("<td>" .$data["date_mod"]. "</td>");
			  echo("<td>" .$data["LastUpdateuser"]. "</td></tr>");
         
		    
		 }
		 
         echo "</table>";
      }
   }
   
   static function showRecent($SortType) {
      global $DB, $CFG_GLPI;

      $faq = !Session::haveRight(self::$rightname, READ);

       $title   = __('Recent entries');
         
		 if ($SortType=="IDAC") // order by ID ASC
		 {
			  $orderby = "ORDER BY id  asc";
            
			 
		 }
		else if ($SortType=="IDDC")  // order by ID DESC
		 {
			  $orderby = "ORDER BY id  desc";
            
			 
		 }
		 
		 else if ($SortType=="SUBASC") // order by Subject ASC
		 {
			  $orderby = "ORDER BY name  asc";
             
			 
		 }
		 
		 else if ($SortType=="SUBDSC") // order by Subject DSC
		 {
			  $orderby = "ORDER BY name  desc";
             
			 
		 }
		 
		 else if ($SortType=="CATASC") // order by category ASC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  asc";
             
			 
		 }
		 
		 else if ($SortType=="CATDSC") // order by category DSC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  desc";
             
			 
		 }
		 
		 else if ($SortType=="DTCNASC") // order by created on ASC
		 {
			  $orderby = "ORDER BY date_created  asc";
            
			 
		 }
		 
		 else if ($SortType=="DTCNDSC") // order by created on DSC
		 {
			  $orderby = "ORDER BY date_created  desc";
             
			 
		 }
		 
		 else if ($SortType=="CBASC") // order by created by ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  asc";
             
			 
		 }
		 
		 else if ($SortType=="CBDSC") // order by created by DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  desc";
            
			 
		 }

		 
		 else if ($SortType=="LUDASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY date_mod   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUDDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY date_mod  desc";
             
			 
		 }
		  else if ($SortType=="LUUASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUUDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)  desc";
            
		 }
	
	
	  else // default recent entries sort order 
		 {
		 $orderby = "ORDER BY `date`  DESC";
       
         }
	  
	  
	  
	  
      $publish_root_check="";
      $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      }


      // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)";

      // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) ";


      if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      }
	  
	  	 
		 $join_CreatedLog = "LEFT JOIN zcustom_glpi_knowbaseitems_creationlog
                     ON (glpi_knowbaseitems.id = zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID) 
                      LEFT JOIN glpi_users as glpi_knowbaseitems_users_createlog on     
                       (glpi_knowbaseitems_users_createlog.id =zcustom_glpi_knowbaseitems_creationlog.User_id)
					   LEFT JOIN glpi_users as glpi_knowbaseitems_users_updatelog on     
                       (glpi_knowbaseitems_users_updatelog.id =glpi_knowbaseitems.Users_id)
                          LEFT JOIN glpi_knowbaseitemcategories on (glpi_knowbaseitemcategories.id=glpi_knowbaseitems.knowbaseitemcategories_id)
						  ";


      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."') ";
         
	
		 
		 $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer  ";
      }
	  
	  $AddCustomized_column=",zcustom_glpi_knowbaseitems_creationlog.Date_Created,concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname) as createduser , concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname) as LastUpdateuser,glpi_knowbaseitemcategories.completename as CategoryName";
	  $publish_root_check="and glpi_knowbaseitems.id in  (select knowbaseitems_id from glpi_entities_knowbaseitems where entities_id =0)";


      $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $AddCustomized_column   $addselect
                FROM `glpi_knowbaseitems`
                 $join  $join_CreatedLog  
                $faq_limit $publish_root_check
                $orderby
                LIMIT 30";
				
				// echo($query );
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number > 0) {
		  
		 // echo($_GET["Sortorder"] );
        

    		echo "<table class='tab_cadrehov'>";
         echo "<tr class='noHover'><th colspan=7>".$title."</th></tr>";
        echo "<tr class='tab_bg_2'><th> ID <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=IDAC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=IDDC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a> </th><th class='left'> Subject <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=SUBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=SUBDSC' ><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th> <th class='left'> Category <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=CATASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=CATDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=DTCNASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=DTCNDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=CBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=CBDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=LUDASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=LUDDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=LUUASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?RSortorder=LUUDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th>";
		while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
           
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            echo "<tr class='tab_bg_2'><td> " .$data["id"]. "</td><td class='left'>";
          /*     echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")."  href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>"; */
			
			// Tooltip not working akk
			
			
			//Toolbox::unclean_html_cross_side_scripting_deep($Content)
			echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")." data-toggle='tooltip' title='".Toolbox::unclean_html_cross_side_scripting_deep(Html::resume_text(Toolbox::unclean_html_cross_side_scripting_deep($data['answer']),10))."' href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>";
				  
			
				 
            echo("<td>" .$data["CategoryName"]. "</td>");
			echo("<td>" .$data["date"]. "</td>");
			  echo("<td>" .$data["createduser"]. "</td>");
			  echo("<td>" .$data["date_mod"]. "</td>");
			  echo("<td>" .$data["LastUpdateuser"]. "</td></tr>");
         
		    
		 }
		 
         echo "</table>";
      }
   }
   

static function showlastupdate($SortType) {
      global $DB, $CFG_GLPI;

      $faq = !Session::haveRight(self::$rightname, READ);

     
         $title   = __('Last updated entries');
     
      
         if ($SortType=="IDAC") // order by ID ASC
		 {
			  $orderby = "ORDER BY id  asc";
            
			 
		 }
		else if ($SortType=="IDDC")  // order by ID DESC
		 {
			  $orderby = "ORDER BY id  desc";
            
			 
		 }
		 
		 else if ($SortType=="SUBASC") // order by Subject ASC
		 {
			  $orderby = "ORDER BY name  asc";
             
			 
		 }
		 
		 else if ($SortType=="SUBDSC") // order by Subject DSC
		 {
			  $orderby = "ORDER BY name  desc";
             
			 
		 }
		 
		 else if ($SortType=="CATASC") // order by category ASC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  asc";
             
			 
		 }
		 
		 else if ($SortType=="CATDSC") // order by category DSC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  desc";
             
			 
		 }
		 
		 else if ($SortType=="DTCNASC") // order by created on ASC
		 {
			  $orderby = "ORDER BY date_created  asc";
            
			 
		 }
		 
		 else if ($SortType=="DTCNDSC") // order by created on DSC
		 {
			  $orderby = "ORDER BY date_created  desc";
             
			 
		 }
		 
		 else if ($SortType=="CBASC") // order by created by ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  asc";
             
			 
		 }
		 
		 else if ($SortType=="CBDSC") // order by created by DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  desc";
            
			 
		 }

		 
		 else if ($SortType=="LUDASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY date_mod   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUDDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY date_mod  desc";
             
			 
		 }
		  else if ($SortType=="LUUASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUUDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)  desc";
            
		 }
	
	
	  else // default last entries sort order 
		 {
		 $orderby = "ORDER BY `date_mod` DESC";
       
         }
		 
		 
		
	  
	  
	  
	  
	  $publish_root_check="";

      $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      }

	  
	  $publish_root_check="and glpi_knowbaseitems.id in  (select knowbaseitems_id from glpi_entities_knowbaseitems where entities_id =0)";

      // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)";

      // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) ";


      if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      }
	  
	  	 
		 $join_CreatedLog = "LEFT JOIN zcustom_glpi_knowbaseitems_creationlog
                     ON (glpi_knowbaseitems.id = zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID) 
                      LEFT JOIN glpi_users as glpi_knowbaseitems_users_createlog on     
                       (glpi_knowbaseitems_users_createlog.id =zcustom_glpi_knowbaseitems_creationlog.User_id)
					   LEFT JOIN glpi_users as glpi_knowbaseitems_users_updatelog on     
                       (glpi_knowbaseitems_users_updatelog.id =glpi_knowbaseitems.Users_id)
                          LEFT JOIN glpi_knowbaseitemcategories on (glpi_knowbaseitemcategories.id=glpi_knowbaseitems.knowbaseitemcategories_id)
						  ";


      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."') ";
         
	
		 
		 $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer  ";
      }
	  
	  $AddCustomized_column=",zcustom_glpi_knowbaseitems_creationlog.Date_Created,concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname) as createduser , concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname) as LastUpdateuser,glpi_knowbaseitemcategories.completename as CategoryName";
	  


      $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $AddCustomized_column   $addselect
                FROM `glpi_knowbaseitems`
                 $join  $join_CreatedLog  
                $faq_limit $publish_root_check
                $orderby
                LIMIT 30";
				
				 //echo($query );
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number > 0) {
		  
		 // echo($CFG_GLPI['root_doc'] );
        

    		echo "<table class='tab_cadrehov'>";
         echo "<tr class='noHover'><th colspan=7>".$title."</th></tr>";
        echo "<tr class='tab_bg_2'><th> ID <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=IDAC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=IDDC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a> </th><th class='left'> Subject <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=SUBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=SUBDSC' ><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th> <th class='left'> Category <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=CATASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=CATDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=DTCNASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=DTCNDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=CBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=CBDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=LUDASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=LUDDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=LUUASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?LUPSortorder=LUUDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th>";
		while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
           
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            echo "<tr class='tab_bg_2'><td> " .$data["id"]. "</td><td class='left'>";
            /*     echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")."  href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>"; */
			
			// Tooltip not working akk
			
			
			//Toolbox::unclean_html_cross_side_scripting_deep($Content)
			echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")." data-toggle='tooltip' title='".Toolbox::unclean_html_cross_side_scripting_deep(Html::resume_text(Toolbox::unclean_html_cross_side_scripting_deep($data['answer']),10))."' href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>";
				  
				  
				 
            echo("<td>" .$data["CategoryName"]. "</td>");
			echo("<td>" .$data["date"]. "</td>");
			  echo("<td>" .$data["createduser"]. "</td>");
			  echo("<td>" .$data["date_mod"]. "</td>");
			  echo("<td>" .$data["LastUpdateuser"]. "</td></tr>");
         
		    
		 }
		 
         echo "</table>";
      }
   }
   
   static function showPopular($SortType) {
      global $DB, $CFG_GLPI;

      $faq = !Session::haveRight(self::$rightname, READ);

      
         $title   = __('Most popular entries');
     
	   if ($SortType=="IDAC") // order by ID ASC
		 {
			  $orderby = "ORDER BY id  asc";
            
			 
		 }
		else if ($SortType=="IDDC")  // order by ID DESC
		 {
			  $orderby = "ORDER BY id  desc";
            
			 
		 }
		 
		 else if ($SortType=="SUBASC") // order by Subject ASC
		 {
			  $orderby = "ORDER BY name  asc";
             
			 
		 }
		 
		 else if ($SortType=="SUBDSC") // order by Subject DSC
		 {
			  $orderby = "ORDER BY name  desc";
             
			 
		 }
		 
		 else if ($SortType=="CATASC") // order by category ASC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  asc";
             
			 
		 }
		 
		 else if ($SortType=="CATDSC") // order by category DSC
		 {
			  $orderby = "ORDER BY glpi_knowbaseitemcategories.completename  desc";
             
			 
		 }
		 
		 else if ($SortType=="DTCNASC") // order by created on ASC
		 {
			  $orderby = "ORDER BY date_created  asc";
            
			 
		 }
		 
		 else if ($SortType=="DTCNDSC") // order by created on DSC
		 {
			  $orderby = "ORDER BY date_created  desc";
             
			 
		 }
		 
		 else if ($SortType=="CBASC") // order by created by ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  asc";
             
			 
		 }
		 
		 else if ($SortType=="CBDSC") // order by created by DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname)  desc";
            
			 
		 }

		 
		 else if ($SortType=="LUDASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY date_mod   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUDDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY date_mod  desc";
             
			 
		 }
		  else if ($SortType=="LUUASC") // order by last update  on ASC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)   asc";
             
			 
		 }
		 
		 else if ($SortType=="LUUDSC") // order by last update  by  DSC
		 {
			  $orderby = "ORDER BY concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname)  desc";
            
		 }
	
	
	  else // default lastupdate
		 {
		 
         $orderby = "ORDER BY `view` DESC";
       
         }
	  
	  
	  
	  
	  
$publish_root_check="";
      $faq_limit = "";
      $addselect = "";
      // Force all joins for not published to verify no visibility set
      $join = self::addVisibilityJoins(true);

      if (Session::getLoginUserID()) {
         $faq_limit .= "WHERE ".self::addVisibilityRestrict();
      } else {
         // Anonymous access
         if (Session::isMultiEntitiesMode()) {
            $faq_limit .= " WHERE (`glpi_entities_knowbaseitems`.`entities_id` = '0'
                                   AND `glpi_entities_knowbaseitems`.`is_recursive` = '1')";
         } else {
            $faq_limit .= " WHERE 1";
         }
      }


$publish_root_check="and glpi_knowbaseitems.id in  (select knowbaseitems_id from glpi_entities_knowbaseitems where entities_id =0)";     

	 // Only published
      $faq_limit .= " AND (`glpi_entities_knowbaseitems`.`entities_id` IS NOT NULL
                           OR `glpi_knowbaseitems_profiles`.`profiles_id` IS NOT NULL
                           OR `glpi_groups_knowbaseitems`.`groups_id` IS NOT NULL
                           OR `glpi_knowbaseitems_users`.`users_id` IS NOT NULL)";

      // Add visibility date
      $faq_limit .= " AND (`glpi_knowbaseitems`.`begin_date` IS NULL
                           OR `glpi_knowbaseitems`.`begin_date` < NOW())
                      AND (`glpi_knowbaseitems`.`end_date` IS NULL
                           OR `glpi_knowbaseitems`.`end_date` > NOW()) ";


      if ($faq) { // FAQ
         $faq_limit .= " AND (`glpi_knowbaseitems`.`is_faq` = '1')";
      }
	  
	  	 
		 $join_CreatedLog = "LEFT JOIN zcustom_glpi_knowbaseitems_creationlog
                     ON (glpi_knowbaseitems.id = zcustom_glpi_knowbaseitems_creationlog.Knowbaseitem_ID) 
                      LEFT JOIN glpi_users as glpi_knowbaseitems_users_createlog on     
                       (glpi_knowbaseitems_users_createlog.id =zcustom_glpi_knowbaseitems_creationlog.User_id)
					   LEFT JOIN glpi_users as glpi_knowbaseitems_users_updatelog on     
                       (glpi_knowbaseitems_users_updatelog.id =glpi_knowbaseitems.Users_id)
                          LEFT JOIN glpi_knowbaseitemcategories on (glpi_knowbaseitemcategories.id=glpi_knowbaseitems.knowbaseitemcategories_id)
						  ";


      if (KnowbaseItemTranslation::isKbTranslationActive()) {
         $join .= "LEFT JOIN `glpi_knowbaseitemtranslations`
                     ON (`glpi_knowbaseitems`.`id` = `glpi_knowbaseitemtranslations`.`knowbaseitems_id`
                           AND `glpi_knowbaseitemtranslations`.`language` = '".$_SESSION['glpilanguage']."') ";
         
	
		 
		 $addselect .= ", `glpi_knowbaseitemtranslations`.`name` AS transname,
                          `glpi_knowbaseitemtranslations`.`answer` AS transanswer  ";
      }
	  
	  $AddCustomized_column=",zcustom_glpi_knowbaseitems_creationlog.Date_Created,concat(glpi_knowbaseitems_users_createlog.realname , ' ',glpi_knowbaseitems_users_createlog.firstname) as createduser , concat(glpi_knowbaseitems_users_updatelog.realname,' ',glpi_knowbaseitems_users_updatelog.firstname) as LastUpdateuser,glpi_knowbaseitemcategories.completename as CategoryName";
	  


      $query = "SELECT DISTINCT `glpi_knowbaseitems`.* $AddCustomized_column   $addselect
                FROM `glpi_knowbaseitems`
                 $join  $join_CreatedLog  
                $faq_limit $publish_root_check
                $orderby
                LIMIT 30";
				
				 //echo($query );
      $result = $DB->query($query);
      $number = $DB->numrows($result);

      if ($number > 0) {
		  
		 // echo($query  );
        

    		echo "<table class='tab_cadrehov'>";
         echo "<tr class='noHover'><th colspan=7>".$title."</th></tr>";
        echo "<tr class='tab_bg_2'><th> ID <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=IDAC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=IDDC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a> </th><th class='left'> Subject <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=SUBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=SUBDSC' ><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th> <th class='left'> Category <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=CATASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=CATDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=DTCNASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=DTCNDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Created by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=CBASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=CBDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update on <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=LUDASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=LUDDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th><th class='left'> Last Update by <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=LUUASC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-up.png'></a> <a href ='".$CFG_GLPI['root_doc']."/front/knowbaseitem.php?POPSortorder=LUUDSC'><img src='".$CFG_GLPI['root_doc']."/pics/puce-down.png'></a></th>";
		while ($data = $DB->fetch_assoc($result)) {
            $name = $data['name'];
           
            if (isset($data['transname']) && !empty($data['transname'])) {
               $name = $data['transname'];
            }
            echo "<tr class='tab_bg_2'><td> " .$data["id"]. "</td><td class='left'>";
            /*     echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")."  href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>"; */
			
			// Tooltip not working akk
			
			
			//Toolbox::unclean_html_cross_side_scripting_deep($Content)
			echo "<a ".($data['is_faq']?" class='pubfaq'  ":" class='knowbase' ")." data-toggle='tooltip' title='".Toolbox::unclean_html_cross_side_scripting_deep(Html::resume_text(Toolbox::unclean_html_cross_side_scripting_deep($data['answer']),10))."' href=\"".
                  $CFG_GLPI["root_doc"]."/front/knowbaseitem.form.php?id=".$data["id"]."  \">".
                  Html::resume_text($name,80)."</a></td>";
				  
				 
            echo("<td>" .$data["CategoryName"]. "</td>");
			echo("<td>" .$data["date"]. "</td>");
			  echo("<td>" .$data["createduser"]. "</td>");
			  echo("<td>" .$data["date_mod"]. "</td>");
			  echo("<td>" .$data["LastUpdateuser"]. "</td></tr>");
         
		    
		 }
		 
         echo "</table>";
      }
   }

   function getSearchOptions() {

      $tab                      = array();
      $tab['common']            = __('Characteristics');

      $tab[2]['table']          = $this->getTable();
      $tab[2]['field']          = 'id';
      $tab[2]['name']           = __('ID');
      $tab[2]['massiveaction']  = false;
      $tab[2]['datatype']       = 'number';

      $tab[4]['table']          = 'glpi_knowbaseitemcategories';
      $tab[4]['field']          = 'name';
      $tab[4]['name']           = __('Category');
      $tab[4]['datatype']       = 'dropdown';

      $tab[5]['table']          = $this->getTable();
      $tab[5]['field']          = 'date';
      $tab[5]['name']           = __('Date');
      $tab[5]['datatype']       = 'datetime';
      $tab[5]['massiveaction']  = false;

      $tab[6]['table']          = $this->getTable();
      $tab[6]['field']          = 'name';
      $tab[6]['name']           = __('Subject');
      $tab[6]['datatype']       = 'text';

      $tab[7]['table']          = $this->getTable();
      $tab[7]['field']          = 'answer';
      $tab[7]['name']           = __('Content');
      $tab[7]['datatype']       = 'text';
      $tab[7]['htmltext']       = true;

      $tab[8]['table']          = $this->getTable();
      $tab[8]['field']          = 'is_faq';
      $tab[8]['name']           = __('FAQ item');
      $tab[8]['datatype']       = 'bool';

      $tab[9]['table']          = $this->getTable();
      $tab[9]['field']          = 'view';
      $tab[9]['name']           = _n('View', 'Views', 2);
      $tab[9]['datatype']       = 'integer';
      $tab[9]['massiveaction']  = false;

      $tab[10]['table']         = $this->getTable();
      $tab[10]['field']         = 'begin_date';
      $tab[10]['name']          = __('Visibility start date');
      $tab[10]['datatype']      = 'datetime';

      $tab[11]['table']         = $this->getTable();
      $tab[11]['field']         = 'end_date';
      $tab[11]['name']          = __('Visibility end date');
      $tab[11]['datatype']      = 'datetime';

      $tab[19]['table']         = $this->getTable();
      $tab[19]['field']         = 'date_mod';
      $tab[19]['name']          = __('Last update');
      $tab[19]['datatype']      = 'datetime';
      $tab[19]['massiveaction'] = false;

      $tab[70]['table']         = 'glpi_users';
      $tab[70]['field']         = 'name';
      $tab[70]['name']          = __('User');
      $tab[70]['massiveaction'] = false;
      $tab[70]['datatype']      = 'dropdown';
      $tab[70]['right']         = 'all';

      $tab[80]['table']         = 'glpi_entities';
      $tab[80]['field']         = 'completename';
      $tab[80]['name']          = __('Entity');
      $tab[80]['massiveaction'] = false;
      $tab[80]['datatype']      = 'dropdown';

      $tab[86]['table']         = $this->getTable();
      $tab[86]['field']         = 'is_recursive';
      $tab[86]['name']          = __('Child entities');
      $tab[86]['datatype']      = 'bool';

      return $tab;
   }


   /**
    * Show visibility config for a knowbaseitem
    *
    * @since version 0.83
   **/
   function showVisibility() {
      global $DB, $CFG_GLPI;

      $ID      = $this->fields['id'];
	 
	  
	  $canedit = $this->can($ID, UPDATE); 
    



      echo "<div class='center'>";

      $rand = mt_rand();
      $nb   = count($this->users) + count($this->groups) + count($this->profiles)
              + count($this->entities);

      if ($canedit) {
         echo "<div class='firstbloc'>";
         echo "<form name='knowbaseitemvisibility_form$rand' id='knowbaseitemvisibility_form$rand' ";
         echo " method='post' action='".Toolbox::getItemTypeFormURL('KnowbaseItem')."'>";
         echo "<input type='hidden' name='knowbaseitems_id' value='$ID'>";
         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_1'><th colspan='4'>".__('Add a target')."</th></tr>";
         echo "<tr class='tab_bg_2'><td width='100px'>";

         
		 
		 //akk permission for tl and supervisor to publish to root
		 if ($_SESSION["glpiactiveprofile"]['id']==4||$_SESSION["glpiactiveprofile"]['id']==7)
		 {
		 $types = array('Entity', 'Group', 'Profile', 'User');
		 }
		 else
		 {
			$types = array('Profile'); 
			 
		 }
		 
		 //$types = array('Entity', 'Group', 'Profile', 'User');
         
		 
		 $addrand = Dropdown::showItemTypes('_type', $types);
         $params  = array('type'  => '__VALUE__',
                          'right' => ($this->getField('is_faq') ? 'faq' : 'knowbase'));

         Ajax::updateItemOnSelectEvent("dropdown__type".$addrand,"visibility$rand",
                                       $CFG_GLPI["root_doc"]."/ajax/visibility.php",
                                       $params);

         echo "</td>";
         echo "<td><span id='visibility$rand'></span>";
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }


      echo "<div class='spaced'>";
      if ($canedit && $nb) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams
            = array('num_displayed'
                        => $nb,
                    'container'
                        => 'mass'.__CLASS__.$rand,
                    'specific_actions'
                         => array('delete' => _x('button', 'Delete permanently')) );

         if ($this->fields['users_id'] != Session::getLoginUserID()) {
            $massiveactionparams['confirm']
               = __('Caution! You are not the author of this element. Delete targets can result in loss of access to that element.');
         }
         Html::showMassiveActions($massiveactionparams);
      }
      echo "<table class='tab_cadre_fixehov'>";
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && $nb) {
         $header_begin  .= "<th width='10'>";
         $header_top    .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_end    .= "</th>";
      }
      $header_end .= "<th>".__('Type')."</th>";
      $header_end .= "<th>"._n('Recipient', 'Recipients', 2)."</th>";
      $header_end .= "</tr>";
      echo $header_begin.$header_top.$header_end;

      // Users
      if (count($this->users)) {
         foreach ($this->users as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td>";
                  Html::showMassiveActionCheckBox('KnowbaseItem_User',$data["id"]);
                  echo "</td>";
               }
               echo "<td>".__('User')."</td>";
               echo "<td>".getUserName($data['users_id'])."</td>";
               echo "</tr>";
            }
         }
      }

      // Groups
      if (count($this->groups)) {
         foreach ($this->groups as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td>";
                  Html::showMassiveActionCheckBox('Group_KnowbaseItem',$data["id"]);
                  echo "</td>";
               }
               echo "<td>".__('Group')."</td>";
               echo "<td>";
               $names     = Dropdown::getDropdownName('glpi_groups', $data['groups_id'],1);
               $groupname = sprintf(__('%1$s %2$s'), $names["name"],
                                    Html::showToolTip($names["comment"], array('display' => false)));
               if ($data['entities_id'] >= 0) {
                  $groupname = sprintf(__('%1$s / %2$s'), $groupname,
                                       Dropdown::getDropdownName('glpi_entities',
                                                                 $data['entities_id']));
                  if ($data['is_recursive']) {
                     $groupname = sprintf(__('%1$s %2$s'), $groupname,
                                          "<span class='b'>(".__('R').")</span>");
                  }
               }
               echo $groupname;
               echo "</td>";
               echo "</tr>";
            }
         }
      }

      // Entity
      if (count($this->entities)) {
         foreach ($this->entities as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td>";
                  Html::showMassiveActionCheckBox('Entity_KnowbaseItem',$data["id"]);
                  echo "</td>";
               }
               echo "<td>".__('Entity')."</td>";
               echo "<td>";
               $names      = Dropdown::getDropdownName('glpi_entities', $data['entities_id'],1);
               $entityname = sprintf(__('%1$s %2$s'), $names["name"],
                                    Html::showToolTip($names["comment"], array('display' => false)));
               if ($data['is_recursive']) {
                  $entityname = sprintf(__('%1$s %2$s'), $entityname,
                                        "<span class='b'>(".__('R').")</span>");
               }
               echo $entityname;
               echo "</td>";
               echo "</tr>";
            }
         }
      }

      // Profiles
      if (count($this->profiles)) {
         foreach ($this->profiles as $key => $val) {
            foreach ($val as $data) {
               echo "<tr class='tab_bg_1'>";
               if ($canedit) {
                  echo "<td>";
                  Html::showMassiveActionCheckBox('KnowbaseItem_Profile',$data["id"]);
                  echo "</td>";
               }
               echo "<td>"._n('Profile', 'Profiles', 1)."</td>";
               echo "<td>";
               $names       = Dropdown::getDropdownName('glpi_profiles', $data['profiles_id'], 1);
               $profilename = sprintf(__('%1$s %2$s'), $names["name"],
                                    Html::showToolTip($names["comment"], array('display' => false)));
               if ($data['entities_id'] >= 0) {
                  $profilename = sprintf(__('%1$s / %2$s'), $profilename,
                                       Dropdown::getDropdownName('glpi_entities',
                                                                 $data['entities_id']));
                  if ($data['is_recursive']) {
                     $profilename = sprintf(__('%1$s %2$s'), $profilename,
                                        "<span class='b'>(".__('R').")</span>");
                  }
               }
               echo $profilename;
               echo "</td>";
               echo "</tr>";
            }
         }
      }
      if ($nb) {
         echo $header_begin.$header_bottom.$header_end;
      }

      echo "</table>";
      if ($canedit && $nb) {
         $massiveactionparams['ontop'] =false;
         Html::showMassiveActions($massiveactionparams);
         Html::closeForm();
      }

      echo "</div>";
      // Add items

      return true;
   }


   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
   **/
   function getRights($interface='central') {

      if ($interface == 'central') {
         $values = parent::getRights();
         $values[self::KNOWBASEADMIN] = __('Knowledge base administration');
         $values[self::PUBLISHFAQ]    = __('Publish in the FAQ');
      }
      $values[self::READFAQ]       = __('Read the FAQ');
      return $values;
   }

}
?>
