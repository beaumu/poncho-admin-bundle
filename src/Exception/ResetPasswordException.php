<?php

namespace Poncho\AdminBundle\Exception;

final class ResetPasswordException extends \Exception
{
    public string $reason;

    public function __construct(string $reason)
    {
        $this->reason = $reason;
        parent::__construct();
    }

    public function getReason(): string
    {
        return $this->reason;
    }
}
