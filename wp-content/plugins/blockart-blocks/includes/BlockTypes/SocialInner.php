<?php
/**
 * SocialInner block.
 *
 * @package BlockArt
 */

namespace BlockArt\BlockTypes;

defined( 'ABSPATH' ) || exit;

/**
 * SocialInner block.
 */
class SocialInner extends AbstractBlock {

	/**
	 * Block name.
	 *
	 * @var string Block name.
	 */
	protected $block_name = 'social-inner';

	const SOCIAL_SHARE_URLS = array(
		'facebook'    => 'https://www.facebook.com/sharer.php?u=',
		'twitter'     => 'https://twitter.com/share?url=',
		'linkedin'    => 'https://www.linkedin.com/shareArticle?url=',
		'youtube'     => 'https://www.youtube.com/',
		'pinterest'   => 'https://pinterest.com/pin/create/link/?url=',
		'reddit'      => 'https://reddit.com/submit?url=',
		'blogger'     => 'https://www.blogger.com/blog_this.pyra?t&amp;u=',
		'tumblr'      => 'https://www.tumblr.com/widgets/share/tool?canonicalUrl=',
		'telegram'    => 'https://t.me/share/url?url=&text=',
		'email'       => 'mailto:?',
		'googlePlus'  => 'https://plus.google.com/share?url=',
		'buffer'      => 'https://bufferapp.com/add?text=&url=',
		'stumbleUpon' => 'https://www.stumbleupon.com/submit?url=&title=',
		'wordpress'   => 'https://wordpress.com/press-this.php?u=&t=&s=&i=',
		'pocket'      => 'https://getpocket.com/save?url=&title=',
		'skype'       => 'https://web.skype.com/share?url=',
		'whatsapp'    => 'https://api.whatsapp.com/send?text=',
	);


	/**
	 * Build html.
	 *
	 * @param string $content Block content.
	 * @return string
	 */
	public function build_html( $content ) {
		if ( ! blockart_is_rest_request() ) {
			$type = $this->get_attribute( 'type' );
			global $wp;
			if ( $type ) {
				$content = str_replace( '{{' . strtoupper( $type ) . '}}', self::SOCIAL_SHARE_URLS[ $type ] . rawurlencode( add_query_arg( $wp->query_vars, home_url() ) ), $content );
			}
		}
		return $content;
	}
}
