<?php
use uCMS\Core\Form;
use uCMS\Core\Settings;
use uCMS\Core\Language\Language;
use uCMS\Core\uCMS;
$languages = Language::GetList();
// TODO: Move this to somewhere specific
$zones = array();
$tzlist = DateTimeZone::listIdentifiers(DateTimeZone::ALL);

$format = Settings::Get("datetime_format")." O";
foreach ($tzlist as $zone) {
	$datetime = new DateTime('NOW', new DateTimeZone($zone));
	$title = $zone.': '.$datetime->format($format);
	$zones[$title] = $zone;
}

// TODO: Move this to somewhere specific
// TODO: Generate array of options
$formats = array();


$form = new Form("settings", "", tr("Apply"));
$form->addField("site_name", "text", tr("Site Name:"), "", Settings::Get("site_name"));
$form->addField("site_description", "text", tr("Site Description:"), "", Settings::Get("site_description"));
$form->addField("site_title", "text", tr("Site Title:"), tr("Shown on the page title."), Settings::Get("site_title"));
$form->addField("site_author", "text", tr("Site Author:"), tr("Tell, who is the author of this site."), Settings::Get("site_author"));
$form->addSelectField($languages, "language", tr("Language:"), "", Settings::Get("language"), 1, false);
$form->addFlag("ucms_maintenance", tr("Enable maintenance mode:"), tr("Disable site for everyone but the administrators."), Settings::Get("ucms_maintenance"));
$form->addFlag("clean_url", tr("User-Friendly URLs:"), tr("Nice links like: @s/super-cool-entry.", uCMS::GetDomain()), Settings::Get("clean_url"));
$form->addFlag("embedding_allowed", tr("Allow embedding site:"), tr("Allow to use site in iframe at any other domain."), Settings::Get("embedding_allowed"));
$form->addField("site_domain", "text", tr("Site Domain:"), tr("Site Address."), uCMS::GetDomain());
$form->addField("ucms_dir", "text", tr("μCMS Directory:"), tr("Subdirectory with μCMS in it (if exists) like: @s/ucms - the directory is /ucms.", uCMS::GetDomain()), uCMS::GetDirectory(), "", false);
$form->addSelectField($zones, "ucms_timezone", tr("Time Zone:"), "", Settings::Get("ucms_timezone"), 1, false);
// TODO: Editable option
$form->addSelectField($formats, "datetime_format", tr("Date and Time Format:"), "", Settings::Get("datetime_format"), 1, false);
$form->addField("admin_email", "text", tr("Email of the site's administrator:"), "", Settings::Get("admin_email"), "", false);
$form->render();
?>