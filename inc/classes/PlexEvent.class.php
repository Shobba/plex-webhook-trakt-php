<?php
abstract class PlexEvent extends BasicEnum {
	const Play		= "media.play";
    const Pause		= "media.pause";
    const Resume 	= "media.resume";
    const Stop		= "media.stop";
    const Scrobble 	= "media.scrobble";
    const Rate		= "media.rate";
}
?>