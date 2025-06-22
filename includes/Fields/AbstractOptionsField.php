<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

class AbstractOptionsField extends TextField {
	protected array $options = [];
	public function getOptions(): array {
		return $this->options;
	}

	public function setOptions( array $options ): static {
		$this->options = $options;
		return $this;
	}

	public function toACF(): array {
		$field = parent::toACF();
		$field['choices'] = $this->options;
		return $field;
	}

}
