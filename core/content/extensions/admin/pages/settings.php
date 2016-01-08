<?php
use uCMS\Core\Form;
use uCMS\Core\Setting;
use uCMS\Core\Localization\Language;
use uCMS\Core\uCMS;
$languages = Language::GetList();
// TODO: Move this to somewhere specific
$zones = array();
$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$format = Setting::Get("datetime_format")." O";
foreach ($tzlist as $zone) {
	$datetime = new DateTime('NOW', new DateTimeZone($zone));
	$title = $zone.': '.$datetime->format($format);
	$zones[$title] = $zone;
}

// TODO: Move this to somewhere specific
// TODO: Generate array of options
$formats = array();


$form = new Form("settings", "", $this->tr("Apply"));
$form->addField("site_name", "text", $this->tr("Site Name:"), "", Setting::Get("site_name"));
$form->addField("site_description", "text", $this->tr("Site Description:"), "", Setting::Get("site_description"));
$form->addField("site_title", "text", $this->tr("Site Title:"), $this->tr("Shown on the page title."), Setting::Get("site_title"));
$form->addField("site_author", "text", $this->tr("Site Author:"), $this->tr("Tell, who is the author of this site."), Setting::Get("site_author"));
$form->addSelectField($languages, "language", $this->tr("Language:"), "", Setting::Get("language"), 1, false);
$form->addFlag("ucms_maintenance", $this->tr("Enable maintenance mode:"), $this->tr("Disable site for everyone but the administrators."), Setting::Get("ucms_maintenance"));
$form->addFlag("enable_cache", $this->tr("Enable content cache:"), $this->tr("Caching site content may significantly increase site performance."), Setting::Get("enable_cache"));
$form->addFlag("clean_url", $this->tr("User-Friendly URLs:"), $this->tr("Nice links like: @s/super-cool-entry.", uCMS::GetDomain()), Setting::Get("clean_url"));
$form->addFlag("embedding_allowed", $this->tr("Allow embedding site:"), $this->tr("Allow to use site in iframe at any other domain."), Setting::Get("embedding_allowed"));
$form->addField("site_domain", "text", $this->tr("Site Domain:"), $this->tr("Site Address."), uCMS::GetDomain());
$form->addField("ucms_dir", "text", $this->tr("μCMS Directory:"), $this->tr("Subdirectory with μCMS in it (if exists) like: @s/ucms - the directory is /ucms.", uCMS::GetDomain()), uCMS::GetDirectory(), "", false);
$form->addSelectField($zones, "ucms_timezone", $this->tr("Time Zone:"), "", Setting::Get("ucms_timezone"), 1, false);
// TODO: Editable option
$form->addSelectField($formats, "datetime_format", $this->tr("Date and Time Format:"), "", Setting::Get("datetime_format"), 1, false);
$form->addField("admin_email", "text", $this->tr("Administrator's email:"), "", Setting::Get("admin_email"), "", false);
$form->render();
?>