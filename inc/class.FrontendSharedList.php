<?php

/**
 * @deprecated since v3.1
 */
class FrontendSharedList extends FrontendShared
{
	protected function initSessionAccountHandler()
    {
		SessionAccountHandler::setAccountFromRequest();
	}
}
