<?php

namespace JustB2b\Fields;

defined( 'ABSPATH' ) || exit;

use JustB2b\Utils\Prefixer;
use JustB2b\Traits\RuntimeCacheTrait;

abstract class AbstractField {
	use RuntimeCacheTrait;

	protected string $key;
	protected string $label;
	protected string $idType;
	protected string $prefixedKey;
	protected int $index = 0;
	protected int $width = 100;
	protected mixed $defaultValue = '';
	protected string $helpText = '';
	protected string $sectionName = 'Main';

	public function __construct( string $key, string $label, string $idType = '' ) {
		$this->key = $key;
		$this->label = $label;
		$this->idType = $idType;
		$this->prefixedKey = Prefixer::getPrefixed( $key );
	}

	public function getACFId( $id ) {
		return $this->idType === '' ? $id : "{$this->idType}_{$id}";
	}

	public function setWidth( int $width ): static {
		$this->width = $width;
		return $this;
	}

	public function getDefaultValue(): mixed {
		return $this->defaultValue;
	}

	public function setDefaultValue( mixed $value ): static {
		$this->defaultValue = $value;
		return $this;
	}

	public function getKey(): string {
		return $this->key;
	}

	public function getPrefixedKey(): string {
		return $this->prefixedKey;
	}

	public function getLabel(): string {
		return $this->label;
	}

	public function setHelpText( string $text ): static {
		$this->helpText = $text;
		return $this;
	}

	public function getHelpText(): ?string {
		return $this->helpText;
	}

	public function getSectionName(): string {
		return $this->sectionName;
	}

	public function setSectionName( string $name ): static {
		$this->sectionName = $name;
		return $this;
	}

	public function getIndex() {
		return $this->index;
	}

	public function setIndex(int $index) {
		$this->index = $index;
		return $this;
	}

	public function toACF(): array {
		$field = [ 
			'key' => $this->prefixedKey,
			'name' => $this->prefixedKey,
			'label' => $this->label,
			'type' => 'text',
			'wrapper' => [ 
				'width' => $this->width,
			],
			'index' => $this->index,
		];

		if ( isset( $this->helpText ) ) {
			$field['instructions'] = $this->helpText;
		}



		return $field;
	}

	protected function isEmpty( $value ): bool {
		return empty( $value );
	}

	public function isEmptyValue( int $id ): bool {
		return $this->isEmpty( $this->getOriginValue( $id ) );
	}

	public function getOriginValue( int $id ): mixed {
		return get_field( self::getPrefixedKey(), $this->getACFId( $id ) );
	}

	public function getValue( int $id ): mixed {
		$value = $this->getOriginValue( $id );
		return $this->isEmpty( $value ) ? $this->defaultValue : $value;
	}

	public function renderValue( int $id ): string {
		$value = $this->getValue( $id );

		if ( is_array( $value ) ) {
			return $this->hasInvalidItems( $value )
				? $this->renderInvalidItems( $value )
				: $this->renderAllItems( $value );
		}

		return $this->renderScalarValue( $value );
	}

	public function hasInvalidItems( array $items ): bool {
		foreach ( $items as $item ) {
			if ( isset( $item['valid'] ) && $item['valid'] === false ) {
				return true;
			}
		}
		return false;
	}

	public function renderScalarValue( mixed $value ): string {
		if ( $value === null || ( is_string( $value ) && trim( $value ) === '' ) ) {
			return '';
		}

		return sprintf(
			'<span class="justb2b-scalar-field">%s</span>',
			esc_html( (string) $value )
		);
	}

	public function renderAllItems( array $items ): string {
		$html = '<div class="justb2b-associations">';

		foreach ( $items as $item ) {
			$html .= $this->renderItem( $item );
		}

		$html .= '</div>';
		return $html;
	}
	public function renderInvalidItems( array $items ): string {
		$html = '<div class="justb2b-associations">';

		foreach ( $items as $item ) {
			if ( isset( $item['valid'] ) && $item['valid'] === false ) {
				$html .= $this->renderItem( $item );
			}
		}

		$html .= '</div>';
		return $html;
	}

	protected function renderItem( array $item ): string {
		if ( ! isset( $item['key'] ) ) {
			return '';
		}

		$isInvalid = isset( $item['valid'] ) && $item['valid'] === false;
		$type = $item['type'] ?? 'error';

		$classes = "justb2b-association-field justb2b-{$type}-field-value";

		$title = $isInvalid
			? ' title="Ponieważ ten element jest nieprawidłowy, ta reguła nie będzie działać."'
			: '';

		return sprintf(
			'<span class="%s"%s>%s%s</span>',
			esc_attr( $classes ),
			$title,
			$isInvalid ? '<span style="color:red; margin-right:0.2em;">&#10060;</span>' : '',
			esc_html( $item['key'] )
		);
	}





}
