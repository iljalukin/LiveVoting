<?php

namespace LiveVoting\Exceptions;

/**
 * Class xlvoVotingManagerException
 */
class xlvoVotingManagerException extends xlvoException {

	/**
	 * @param string $message
	 */
	public function __construct($message) {
		parent::__construct($message);
	}
}
