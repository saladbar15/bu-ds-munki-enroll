<?php
namespace CFPropertyList;

// require cfpropertylist
require_once('cfpropertylist-2.0.1/CFPropertyList.php');

// get the directory where munki-enroll is installed
// munki-enroll should always be placed in the same directory the manifests directory resides in
$munki_dir = dirname(__DIR__);

// debug output
echo "Remote munki-enroll location: " . $munki_dir . PHP_EOL;

// get the varibles passed by the enroll script
$hostname   = $_GET["hostname"];
$tla        = $_GET["tla"];
$subtla     = $_GET["subtla"];
$chassis    = $_GET["chassis"];

// Check if hostname is present. If not, end.
if(empty($hostname)) {
  exit("Hostname not defined. Stopping.");
}

// debug output
echo "Computer hostname is: " . $hostname . PHP_EOL;
echo "Computer belongs to: " . $tla . PHP_EOL;
if(empty($subtla)) {
  echo "No sub-TLA present";
  $subtla_path = "";
  $subtla_manifest = "";
} else {
  echo "Computer sub-TLA is: " . $subtla . PHP_EOL;
  $subtla_path = $subtla . "/";
  $subtla_manifest = "-" . $subtla;
}
if(empty($chassis)) {
  echo "No chassis present";
  $chassis_path = "";
  $chassis_manifest = "";
} else {
  echo "Computer chassis is: " . $chassis . PHP_EOL;
  $chassis_path = $chassis . "/";
  $chassis_manifest = "-" . $chassis;
}

// debug output
echo "Target manifest location: " . $munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'clients/' . $hostname . PHP_EOL;

// check if manifest already exists for this machine
if (file_exists($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'clients/' . $hostname)) {

    // debug output
    echo "Computer manifest for " . $hostname . " already exists." . PHP_EOL;

} else {

    // debug output
    echo "Computer manifest for " . $hostname . " does not exist. Will create." . PHP_EOL;

    if (!is_dir($munki_dir . '/manifests/' . $tla . '/')) {

      //debug output
      echo "TLA folder " . $munki_dir . '/manifests/' . $tla . '/ does not exist. Will create.' . PHP_EOL;

      mkdir($munki_dir . '/manifests/' . $tla . '/', 0755, true);

    }

    if (!empty($subtla_path) && !is_dir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path)) {

      //debug output
      echo "Sub-TLA folder " . $munki_dir . '/manifests/' . $tla . '/' . $subtla_path . 'does not exist and is specified. Will create.' . PHP_EOL;

      mkdir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path, 0755, true);

    }

    if (!empty($chassis_path) && !is_dir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path)) {

      //debug output
      echo "Chassis folder " . $munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'does not exist and is specified. Will create.' . PHP_EOL;

      mkdir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path, 0755, true);

    }

    if (!is_dir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'clients/')) {
        //debug output
        echo "Clients folder " . $munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . "clients/ does not exist. Will create." . PHP_EOL;

        mkdir($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'clients/', 0755, true);

    }

    // create the new manifest plist
    $plist = new CFPropertyList();
    $plist->add($dict = new CFDictionary());

    // add manifest to existing catalogs
    $dict->add('catalogs', $array = new CFArray());
    $array->add(new CFString("production"));

    // Add parent manifest to included_manifests to achieve waterfall effect
    $dict->add('included_manifests', $array = new CFArray());
    $array->add(new CFString($tla . '/' .  $subtla_path . $chassis_path . $tla . $subtla_manifest . $chassis_manifest . "_default"));

    // Save the newly created plist
    $plistnew = $munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . 'clients/' . $hostname;
    echo "Writing new plist: " . $plistnew . PHP_EOL;
    $plist->saveXML($plistnew);

    // We'll need to repeat this process for any parent manifests...

    // Let's see if we need to make a tla default list
    if(!file_exists($munki_dir . '/manifests/' . $tla . "_default")) {

      echo "TLA plist does not exist, so we'll make one";

      // create the new manifest plist
      $plist = new CFPropertyList();
      $plist->add($dict = new CFDictionary());

      // this is a parent plist so we won't add any catalogs
      // we will, however, make sure that the TLA plist includes the site
      // default manifest.
      $dict->add('included_manifests', $array = new CFArray());
      $array->add(new CFString("default"));

      // Save the newly created plist
      $plistnew = $munki_dir . '/manifests/' . $tla . '/' . $tla . '_default';
      echo "Writing new plist: " . $plistnew . PHP_EOL;
      $plist->saveXML($plistnew);

    }

    // Let's see if we need to make a sub-tla default list
    if(!empty($subtla_manifest) && !file_exists($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $tla . $subtla_manifest . "_default")) {

      echo "Sub-TLA plist does not exist but is needed, so we'll make one";

      // create the new manifest plist
      $plist = new CFPropertyList();
      $plist->add($dict = new CFDictionary());

      // this is a parent plist so we won't add any catalogs
      // we will, however, make sure that this includes the TLA
      // default manifest.
      $dict->add('included_manifests', $array = new CFArray());
      $array->add(new CFString($tla . '/' . $tla . "_default"));

      // Save the newly created plist
      $plistnew = $munki_dir . '/manifests/' . $tla . '/' .  $subtla_path . $tla . $subtla_manifest . "_default";
      echo "Writing new plist: " . $plistnew . PHP_EOL;
      $plist->saveXML($plistnew);

    }

    // Let's see if we need to make a chassis default list
    if(!empty($chassis_manifest) && !file_exists($munki_dir . '/manifests/' . $tla . '/' . $subtla_path . $chassis_path . $tla . $subtla_manifest . $chassis_manifest . "_default")) {

      echo "Chassis plist does not exist but is needed, so we'll make one";

      // create the new manifest plist
      $plist = new CFPropertyList();
      $plist->add($dict = new CFDictionary());

      // this is a parent plist so we won't add any catalogs
      // we will, however, make sure that this includes the Sub-TLA
      // default manifest.
      $dict->add('included_manifests', $array = new CFArray());
      $array->add(new CFString($tla . '/' .  $subtla_path . $tla . $subtla_manifest . "_default"));

      // Save the newly created plist
      $plistnew = $munki_dir . '/manifests/' . $tla . '/' .  $subtla_path . $chassis_path . $tla . $subtla_manifest . $chassis_manifest . "_default";
      echo "Writing new plist: " . $plistnew . PHP_EOL;
      $plist->saveXML($plistnew);

    }

}

?>
