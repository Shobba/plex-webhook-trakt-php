<?php
require_once(__DIR__ . "/inc/global.php");

if(Param::get(0) == "saved") {
	
	// Plex user successfully authorized
	
	echo "Access token saved.";#
	exit();
	
} elseif(Param::get(0) == "authorize") {
	
	// Try to authorize to TRAKT.TV account
	
	if(!isset($_GET['code'])) {
		
		// Redirect to trakt authorization website
		
		if(!Param::get(1)) throw new Exception("No PLEX username set (http://website.com/authorize/{plex_username}).");
		
		$trakt = new Trakt(Param::get(1));
		$trakt->authorize();
		exit();
		
	} else {
		
		// Came back from trakt authorization website
		
		$trakt = new Trakt($_GET['plex_user']);
		$trakt->writeAccessToken($_GET['code']);
		
		header("LOCATION: /saved");
		exit();
	}
	
} elseif(isset($_POST['payload'])) {
	
	// Got data from PLEX
	
	$plex = json_decode($_POST['payload']);
	$media = NULL;
	
	switch($plex->Metadata->type) {
		case PlexType::Series:
			$media				= new Episode();
			$media->title 		= $plex->Metadata->title;
			$media->summary 	= $plex->Metadata->summary;
			$media->showTitle	= $plex->Metadata->grandparentTitle;
			$media->season		= $plex->Metadata->parentIndex;
			$media->number		= $plex->Metadata->index;
			break;
			
		case PlexType::Movie:
			$media = new Movie();
			$media->title = $plex->Metadata->title;
			$media->originalTitle = $plex->Metadata->originalTitle;
			$media->summary = $plex->Metadata->summary;
			$media->year = $plex->Metadata->year;
			break;
	}
	
	$trakt = new Trakt($plex->Account->title);
	$trakt->action($plex->event, $media, $plex->Player->title);
	
	exit();
}
?>