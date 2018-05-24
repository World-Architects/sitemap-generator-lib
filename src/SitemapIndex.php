<?php
declare(strict_types = 1);

namespace SitemapGenerator;

/**
 * URL Set
 *
 * @link https://www.sitemaps.org/protocol.html
 */
class SitemapIndex extends UrlSet {

	/**
	 * Returns a <sitemap> element instead of <urlset>
	 *
	 * @var bool
	 */
	protected $isSitemapIndex = true;

}
