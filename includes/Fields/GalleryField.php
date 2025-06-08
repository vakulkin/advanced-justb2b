<?php

namespace JustB2b\Fields;

defined('ABSPATH') || exit;

class GalleryField extends TextField
{
    protected string $type = 'media_gallery';
    protected array $meidaTypes = [];

    public function __construct(string $key, string $label)
    {
        parent::__construct($key, $label);
        $this->setMediaTypes(['image']);
        $this->defaultValue = [];
    }

    public function setMediaTypes(array $meidaTypes): static
    {
        $this->meidaTypes = $meidaTypes;
        return $this;
    }
}
