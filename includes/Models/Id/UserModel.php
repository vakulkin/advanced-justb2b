<?php

namespace JustB2b\Models\Id;

use JustB2b\Fields\AbstractField;
use JustB2b\Fields\SelectField;
use JustB2b\Traits\RuntimeCacheTrait;
use JustB2b\Utils\Prefixer;
use WP_Query;

defined( 'ABSPATH' ) || exit;

/**
 * @feature-section user_context
 * @title[ru] Контекст клиента: B2B или B2C
 * @desc[ru] JustB2B автоматически различает клиентов по типу (B2B или B2C) и использует это как основу для ценообразования и отображения товаров.
 * @order 150
 */

/**
 * @feature user_context model
 * @title[ru] Идентификация клиента
 * @desc[ru] Плагин определяет, является ли пользователь B2B-клиентом, и на основе этого показывает нужные цены и товары.
 * @order 151
 */


class UserModel extends AbstractIdModel {
	use RuntimeCacheTrait;

	protected function cacheContext( array $extra = [] ): array {
		return array_merge( [ 'user_id' => $this->id ], $extra );
	}

	/**
	 * @feature user_context is_b2b
	 * @title[ru] Определение B2B-клиента
	 * @desc[ru] Система понимает, когда пользователь относится к сегменту B2B, и применяет соответствующие правила и цены.
	 * @order 152
	 */
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
