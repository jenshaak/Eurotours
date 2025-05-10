<?php

$settings = new \stdClass();
$settings->installFolder = "/srv/eurotours/";
$settings->shareds = [ "web/cache", "web/media", "web/uploads", "var/logs", "vendor" ];
$settings->gitRepository = "ssh://git@bitbucket.org/motvicka/EuroTours.git";
$settings->gitBranch = "master";
$settings->env = "prod";

if (isset($argv[1])) {
	$settings->gitBranch = $argv[1];
}

function run($settings)
{
	$deployFolder = $settings->installFolder . "releases/" . getNameOfDeployFolder() . "/";
	$sharedFolder = $settings->installFolder . "shared/";

	prepareFolders($settings->installFolder, $settings->shareds);
	gitCloneToFolder($settings->gitRepository, $settings->gitBranch, $deployFolder);
	prepareSharedFolders($deployFolder, $sharedFolder, $settings->shareds);
	createSymlinkToConfig($deployFolder, $sharedFolder);
	downloadComposer($deployFolder);
	installVendor($deployFolder, $settings->env);
	clearCache($deployFolder, $settings->env);
	asseticDump($deployFolder, $settings->env);
	doctrineUpdate($deployFolder, $settings->env);
	createCurrentSymlink($settings->installFolder, $deployFolder);
}

function prepareFolders($installFolder, $shareds)
{
	exec("mkdir -p {$installFolder}releases {$installFolder}shared");
	foreach ($shareds as $shared) {
		exec("mkdir -p {$installFolder}shared/{$shared}");
	}
}

function getNameOfDeployFolder()
{
	return date("YmdHis");
}

function gitCloneToFolder($gitRepository, $gitBranch, $folder)
{
	echo exec("git clone -q -b {$gitBranch} {$gitRepository} $folder");
}

function prepareSharedFolders($deployFolder, $sharedFolder, $shareds)
{
	foreach ($shareds as $shared) {
		exec("rm -rf {$deployFolder}{$shared}");
		exec("ln -nfs {$sharedFolder}{$shared} {$deployFolder}{$shared}");
	}
}

function downloadComposer($deployFolder)
{
	exec("cd {$deployFolder} && curl -sS https://getcomposer.org/installer | php");
}

function clearCache($deployFolder, $env)
{
	exec("cd {$deployFolder} && php bin/console cache:clear --env={$env} --no-debug");
}

function asseticDump($deployFolder, $env)
{
	exec("cd {$deployFolder} && php bin/console assetic:dump --env={$env} --no-debug");
}

function doctrineUpdate($deployFolder, $env)
{
	exec("cd {$deployFolder} && php bin/console doctrine:schema:update --force --env={$env}");
}

function createSymlinkToConfig($deployFolder, $sharedFolder)
{
	exec("mkdir -p {$sharedFolder}app/config");
	exec("touch {$sharedFolder}app/config/parameters.yml");
	exec("ln -nfs {$sharedFolder}app/config/parameters.yml {$deployFolder}app/config/parameters.yml");
}

function installVendor($deployFolder, $env)
{
	exec("cd {$deployFolder} && SYMFONY_ENV={$env} php composer.phar install --no-dev --verbose --prefer-dist");
}

function createCurrentSymlink($installFolder, $deployFolder)
{
	exec("ln -nfs {$deployFolder} {$installFolder}current");
}

# RUN
run($settings);
