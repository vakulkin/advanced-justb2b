<?php

namespace JustB2b\Models;

use JustB2b\Utils\Prefixer;
use JustB2b\Traits\LazyLoaderTrait;

defined('ABSPATH') || exit;

abstract class BasePostModel extends BaseModel
{
    use LazyLoaderTrait;

    protected static string $key;
    
    protected ?string $title = null;

    abstract static public function getSingleName(): string;
    abstract static public function getPluralName(): string;

    public function __construct(int $id)
    {
        parent::__construct($id);
    }

    public function getTitle(): string
    {
        $this->initTitle();
        return $this->title;
    }

    protected function initTitle(): void
    {
        $this->lazyLoad($this->title, function () {
            return get_the_title($this->id);
        });
    }

    public static function getKey(): string
    {
        return static::$key;
    }

    public static function getPrefixedKey(): string
    {
        return Prefixer::getPrefixed(static::getKey());
    }
}
