<?php

namespace Webkul\Support\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LicenseExpiredException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('support::license.read-only'));
    }

    public function render(Request $request): Response
    {
        return response($this->getMessage(), 402);
    }

    public function report(): bool
    {
        return false;
    }
}
