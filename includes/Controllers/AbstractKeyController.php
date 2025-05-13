<?php

namespace JustB2b\Controllers;

use JustB2b\Models\AbstractKeyModel;

defined('ABSPATH') || exit;

abstract class AbstractKeyController extends AbstractController
{
    protected AbstractKeyModel $modelObject;

}
