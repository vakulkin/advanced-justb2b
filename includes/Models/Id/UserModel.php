<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\SelectField;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section user_context
 * @title[ru] Тип клиента (B2B/B2C)
 * @title[pl] Typ klienta (B2B/B2C)
 * @desc[ru] Администратор назначает тип клиента. Это влияет на цены и условия.
 * @desc[pl] Administrator przypisuje klientowi typ. Ma to wpływ na ceny i warunki zakupu.
 * @order 150
 */


class UserModel extends AbstractIdModel {
	use RuntimeCacheTrait;

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 'user_id' => $this->id ], $extra );
	}

	public function isB2b(): bool {
		return self::getFromRuntimeCache( function () {
			$kind = $this->getFieldValue( 'user_type' );
			return $kind === 'b2b';
		}, $this->cacheContext() );

	}

	public static function getFieldsDefinition(): array {
		return [ 
			( new SelectField( 'user_type', 'Rodzaj', 'user' ) )
				->setOptions( [ 
					'b2c' => 'b2c',
					'b2b' => 'b2b',
				] ),
		];
	}

	public function getUserBanners(): array {
		return self::getFromRuntimeCache( function () {
			$banners = [];
			foreach ( $this->getUserRules() as $rule ) {
				$banner = $rule->getBanner();
				if ( ! empty( $banner ) ) {
					$banners[] = $banner;
				}
			}
			return $banners;
		}, $this->cacheContext() );
	}

	public function getUserBannersHtml(): string {
		$htmlImages = [];

		foreach ( $this->getUserBanners() as $bannerId ) {
			$url = wp_get_attachment_image_url( $bannerId, 'full' );
			if ( ! $url ) {
				continue;
			}

			$alt = get_post_meta( $bannerId, '_wp_attachment_image_alt', true );
			if ( ! $alt ) {
				$alt = get_the_title( $bannerId ) ?: '';
			}

			$htmlImages[] = sprintf(
				'<img src="%s" alt="%s" loading="lazy" />',
				esc_url( $url ),
				esc_attr( $alt )
			);
		}

		return implode( $htmlImages );
	}


	public function getUserRules(): array {
		return self::getFromRuntimeCache( function () {
			$query = new WP_Query( $this->getRuleQueryArgs() );
			$results = [];
			foreach ( $query->posts as $post ) {
				$rule = new RuleModel( $post->ID );
				if ( $rule->isRuleFitToUser() ) {
					$results[] = $rule;
				}
			}
			return $results;
		}, $this->cacheContext() );
	}


	protected function getRuleQueryArgs(): array {
		return [ 
			'post_type' => Prefixer::getPrefixed( 'rule' ),
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'meta_query' => $this->getBaseMetaQuery( $this->isB2b() ),
			'orderby' => [ 
				'priority_clause' => 'ASC',
				'ID' => 'ASC',
			],
		];
	}

}
