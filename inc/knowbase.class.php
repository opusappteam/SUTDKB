<?php
/*
 * @version $Id: knowbase.class.php 23228 2014-11-14 09:50:02Z yllen $
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
 * Knowbase Class
 *
 * @since version 0.84
**/
class Knowbase extends CommonGLPI {


   static function getTypeName($nb=0) {

      // No plural
      return __('Knowledge base');
   }


   function defineTabs($options=array()) {

      $ong = array();
      $this->addStandardTab(__CLASS__, $ong, $options);

      $ong['no_all_tab'] = true;
      return $ong;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         $tabs[1] = _x('button', 'Dash Board');
		 $tabs[2] = _x('button', 'Search');
         $tabs[3] = _x('button', 'Browse');
         if (KnowbaseItem::canUpdate()) {
            $tabs[4] = _x('button', 'Manage');
         }
         
		  //4 for super user and 7 for supervisor
		 if ($_SESSION["glpiactiveprofile"]['id']==4||$_SESSION["glpiactiveprofile"]['id']==7)
		     $tabs[5] = _x('button', 'Deleted Article');
       
    

	   return $tabs;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

      if ($item->getType() == __CLASS__) {
         switch ($tabnum) {
            case 1 : 
               $item->showDashBoard();
               break;
			case 2 : 
               $item->showSearchView();
               break;

            case 3 :
               $item->showBrowseView();
               break;

            case 4 :
               $item->showManageView();
               break;
			   
			    case 5 :
               $item->showDelView();
               break;
         }
      }
      return true;
   }
static function showDelView() {
 KnowbaseItem::Article_deleted_view($_GET["DelSortorder"]);

}

static function showDashBoard() {
	
  KnowbaseItem::showRecent($_GET["RSortorder"]);
  echo("<br>");
  KnowbaseItem::showlastupdate($_GET["LUPSortorder"]);
   echo("<br>");
    KnowbaseItem::showPopular($_GET["POPSortorder"]);
	 echo("<br>");

}



   /**
    * Show the knowbase search view
   **/
   static function showSearchView() {

      // Search a solution
      if (!isset($_GET["contains"])
          && isset($_GET["itemtype"])
          && isset($_GET["items_id"])) {

         if ($item = getItemForItemtype($_GET["itemtype"])) {
            if ($item->getFromDB($_GET["items_id"])) {
               $_GET["contains"] = addslashes($item->getField('name'));
            }
         }
      }

      if (isset($_GET["contains"])) {
         $_SESSION['kbcontains'] = $_GET["contains"];
      } else if (isset($_SESSION['kbcontains'])) {
         $_GET['contains'] = $_SESSION["kbcontains"];
      }
      $ki = new KnowbaseItem();
      $ki->searchForm($_GET);

        if (!isset($_GET['contains']) || empty($_GET['contains'])) {
		
		echo "<script type='text/javascript'>alert('Please enter search string');</script>";
		
		}
		else
		{
		 KnowbaseItem::showList($_GET, 'search');
		}
	  
        
      
   }


   /**
    * Show the knowbase browse view
   **/
   static function showBrowseView() {

      if (isset($_GET["knowbaseitemcategories_id"])) {
         $_SESSION['kbknowbaseitemcategories_id'] = $_GET["knowbaseitemcategories_id"];
      } else if (isset($_SESSION['kbknowbaseitemcategories_id'])) {
         $_GET["knowbaseitemcategories_id"] = $_SESSION['kbknowbaseitemcategories_id'];
      }

      $ki = new KnowbaseItem();
      $ki->showBrowseForm($_GET);
      if (!isset($_GET["itemtype"])
          || !isset($_GET["items_id"])) {
         KnowbaseItemCategory::showFirstLevel($_GET);
      }
      KnowbaseItem::showList($_GET, 'browse');
   }


   /**
    * Show the knowbase Manage view
   **/
   static function showManageView() {

      if (isset($_GET["unpublished"])) {
         $_SESSION['kbunpublished'] = $_GET["unpublished"];
      } else if (isset($_SESSION['kbunpublished'])) {
         $_GET["unpublished"] = $_SESSION['kbunpublished'];
      }
      if (!isset($_GET["unpublished"])) {
         $_GET["unpublished"] = 'myunpublished';
      }
      $ki = new KnowbaseItem();
      $ki->showManageForm($_GET);
      KnowbaseItem::showList($_GET, $_GET["unpublished"]);
   }


}
?>