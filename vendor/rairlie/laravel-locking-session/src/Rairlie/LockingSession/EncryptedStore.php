<?php
namespace Rairlie\LockingSession;

use Illuminate\Session\EncryptedStore as BaseEncryptedStore;

class EncryptedStore extends BaseEncryptedStore
{

    public function __construct($name, $realHandler, $id = null, $lockfileDir = null)
    {
        $lockingSessionHandler = new LockingSessionHandler($realHandler, $lockfileDir);

        return parent::__construct($name, $lockingSessionHandler, $id);
    }

    public function handlerNeedsRequest()
    {
        return $this->handler->needsRequest();
    }

}
