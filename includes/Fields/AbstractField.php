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

	public function toACF(): array {
		$field = [ 
			'key' => $this->prefixedKey,
			'name' => $this->prefixedKey,
			'label' => $this->label,
			'type' => 'text',
			'wrapper' => [ 
				'width' => $this->width,
			],
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
		return $value;
	}


	protected function renderEntities(
		array $values,
		callable $resolver,
		callable $linkGenerator,
		callable $labelGetter
	): string {

		if (false === $values) {
			return 'error';
		}

		$visibleCount = 3;
		$resolvedEntities = $this->resolveEntities( $values, $resolver );
		$renderedLinks = $this->renderVisibleEntities( $resolvedEntities, $linkGenerator, $labelGetter, $visibleCount );
		$moreIndicator = $this->renderRemainingCountIndicator( count( $resolvedEntities ), $visibleCount );

		return sprintf(
			'<div class="justb2b-associations">%s%s</div>',
			$renderedLinks,
			$moreIndicator
		);
	}

	protected function resolveEntities( array $values, callable $resolver ): array {
		$entities = [];

		foreach ( $values as $value ) {
			$id = (int) ( $value['id'] ?? 0 );
			if ( ! $id ) {
				continue;
			}

			$entity = $resolver( $id );
			if ( $entity && ! is_wp_error( $entity ) ) {
				$subtype = $value['subtype'] ?? $value['taxonomy'] ?? ( $value['user_email'] ?? false ? 'user' : 'item' );
				$entities[] = [ 'entity' => $entity, 'subtype' => $subtype ];
			}
		}

		return $entities;
	}

	protected function renderVisibleEntities(
		array $entities,
		callable $linkGenerator,
		callable $labelGetter,
		int $visibleCount
	): string {
		$output = '';

		foreach ( array_slice( $entities, 0, $visibleCount ) as $item ) {
			$label = esc_attr( $labelGetter( $item['entity'] ) );
			$url = esc_url( $linkGenerator( $item['entity'] ) );
			$output .= sprintf(
				'<a class="justb2b-association-field justb2b-%s-field-value" href="%s" target="_blank" rel="noopener noreferrer" title="%s">%s</a>',
				$item['subtype'],
				$url,
				$label,
				$label
			);
		}

		return $output;
	}

	protected function renderRemainingCountIndicator( int $total, int $visibleCount ): string {
		$remaining = $total - $visibleCount;

		if ( $remaining > 0 ) {
			// Use a generic "item" subtype; you can adjust this logic if needed.
			return sprintf(
				'<span class="justb2b-association-field justb2b-item-field-value">+%s</span>',
				esc_html( $remaining )
			);
		}

		return '';
	}
}
