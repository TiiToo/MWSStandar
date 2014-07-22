<?php

namespace MwsStandar\Composer\Script;

use Composer\Script\Event;


class Hooks
{
	public static function checkHooks(Event $event)
	{
		$io = $event->getIO();
		$gitHook = @file_get_contents(__DIR__.'/../../../../.git/hooks/pre-commit');
		$docHook = @file_get_contents(__DIR__.'/../../../../docs/hooks/pre-commit');
		$result = true;
			if ($gitHook !== $docHook) {
			$io->write('<error>You, motherfucker, please, set up your hooks!</error>');
			$result = false;
			}
		return $result;
	}
}
