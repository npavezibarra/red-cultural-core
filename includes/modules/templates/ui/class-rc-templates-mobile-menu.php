<?php
/**
 * Mobile Menu Rendering for Red Cultural.
 */

declare(strict_types=1);

if (!defined('ABSPATH')) {
	exit;
}

final class RC_Templates_Mobile_Menu {
	public static function init(): void {
		add_action('wp_footer', array(__CLASS__, 'render_mobile_menu'), 10);
	}

	public static function render_mobile_menu(): void {
		if (is_admin()) {
			return;
		}

		$links = RC_Templates_UI::get_main_nav_links();
		if (empty($links)) {
			return;
		}

		?>
		<!-- Backdrop Overlay -->
		<div id="rcp-mobile-backdrop" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-[9998] opacity-0 pointer-events-none transition-opacity duration-300 ease-in-out"></div>

		<!-- Top Slide-down Menu Panel -->
		<nav id="rcp-mobile-menu" class="fixed top-0 left-0 right-0 bg-white z-[9999] max-h-[90vh] shadow-2xl flex flex-col pb-4 rounded-b-[24px]">
			
			<div class="px-8 pt-8 pb-4 flex-1 overflow-y-auto safe-area-top">
				<ul class="space-y-4">
					<?php foreach ($links as $link) : 
						$type = $link['type'] ?? 'link';
						$label = $link['label'] ?? '';
						$url = $link['url'] ?? '#';
						$key = $link['key'] ?? '';
						$children = $link['children'] ?? [];
						$icon = $link['icon'] ?? 'chevron-right';

						if ($type === 'account' && !empty($children)) : ?>
							<!-- Dropdown Item (Account) -->
							<li class="border-b border-gray-50 pb-2">
								<button class="rcp-mobile-dropdown-toggle w-full group flex items-center justify-between py-2 outline-none">
									<span class="text-[16px] tracking-[2px] font-semibold text-gray-900 group-hover:text-[#c5a367] transition-colors"><?php echo esc_html($label); ?></span>
									<i data-lucide="chevron-right" class="chevron-icon w-5 h-5 text-gray-400 transition-transform duration-300"></i>
								</button>
								<div class="rcp-mobile-submenu submenu">
									<div class="submenu-content space-y-4 pl-4 border-l-2 border-gray-100 mt-2">
										<?php foreach ($children as $child) : ?>
											<a href="<?php echo esc_url($child['url']); ?>" class="block text-[15px] tracking-[2px] text-gray-500 hover:text-[#c5a367] py-1">
												<?php echo esc_html($child['label']); ?>
											</a>
										<?php endforeach; ?>
									</div>
								</div>
							</li>
						<?php elseif ($type === 'auth') : ?>
							<li>
								<a href="<?php echo esc_url($url); ?>" class="group flex items-center justify-between py-2 border-b border-gray-50" <?php echo ($url === '#rcp-auth') ? 'data-rcp-auth-open="1"' : ''; ?>>
									<span class="text-[16px] tracking-[2px] font-semibold text-gray-900 group-hover:text-[#c5a367] transition-colors"><?php echo esc_html($label); ?></span>
									<i data-lucide="chevron-right" class="w-5 h-5 text-gray-300"></i>
								</a>
							</li>
						<?php elseif ($type === 'cart') : ?>
							<li>
								<a href="<?php echo esc_url($url); ?>" class="group flex items-center justify-between py-2 border-b border-gray-50">
									<div class="flex items-center gap-3">
										<span class="text-[16px] tracking-[2px] font-semibold text-gray-900 group-hover:text-[#c5a367] transition-colors"><?php echo esc_html($label); ?></span>
										<?php 
										$count = RC_Templates_UI::get_cart_count();
										if ($count > 0) : ?>
											<span class="bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full"><?php echo $count; ?></span>
										<?php endif; ?>
									</div>
									<i data-lucide="chevron-right" class="w-5 h-5 text-gray-300"></i>
								</a>
							</li>
						<?php else : ?>
							<li>
								<a href="<?php echo esc_url($url); ?>" class="group flex items-center justify-between py-2 border-b border-gray-50">
									<span class="text-[16px] tracking-[2px] font-semibold text-gray-900 group-hover:text-[#c5a367] transition-colors"><?php echo esc_html($label); ?></span>
									<i data-lucide="chevron-right" class="w-5 h-5 text-gray-300"></i>
								</a>
							</li>
						<?php endif; ?>
					<?php endforeach; ?>
				</ul>
			</div>

			<!-- Close Button & Handle at bottom of panel -->
			<div class="px-8 pt-4 pb-4 bg-white flex items-center justify-between mt-auto">
				<button id="rcp-mobile-close" class="flex items-center justify-center w-12 h-12 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-2xl transition-all active:scale-95 shadow-sm">
					<i data-lucide="x" class="w-6 h-6"></i>
				</button>
				<!-- Handle for visual cue at bottom since it slides from top -->
				<div class="w-12 h-1.5 bg-gray-200 rounded-full"></div>
				<div class="w-12"></div> <!-- Spacer to keep handle centered -->
			</div>
		</nav>

		<!-- Bottom Floating Trigger Button (Now Top Right) -->
		<button id="rcp-mobile-trigger" class="fixed top-0 right-6 bg-white text-black w-14 h-14 rounded-full z-[9990] flex items-center justify-center transition-transform active:scale-95">
			<i data-lucide="menu" class="w-6 h-6"></i>
		</button>
		<?php
	}
}
