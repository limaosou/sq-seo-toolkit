<?php
/**
 * Plugin Name: SQ SEO Toolkit
 * Plugin URI:  https://github.com/limaosou/sq-seo-toolkit
 * Description: 轻量全功能 SEO 工具箱：TDK、Sitemap、robots.txt（AI 爬虫一键放行）、llms.txt、OG 社交卡片、JSON-LD 结构化数据、图片 ALT 补全、百度主动推送、IndexNow、301 重定向、站长平台验证码、内容体检（新鲜度+死链扫描）。无需授权，无外部请求上报。Lightweight all-in-one SEO toolkit.
 * Version:     2.3.8
 * Author:      狸猫
 * Author URI:  https://www.qinghuali.com/
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: sanqi-seo
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // 禁止直接访问
}

define( 'SQSEO_VERSION', '2.3.8' );
define( 'SQSEO_URL', plugin_dir_url( __FILE__ ) );

/* =========================================================
 * 一、默认选项与读取
 * ======================================================= */

function sqseo_defaults() {
	return array(
		// 基础 TDK
		'home_title'     => '',   // 首页标题（留空则不接管）
		'home_keywords'  => '',   // 首页关键词
		'home_desc'      => '',   // 首页描述（仅当「接管描述」开启时输出）
		'cat_title_tpl'  => '',   // 分类标题模板，支持 {分类名} {站名}
		'cat_keywords'   => '',   // 分类关键词（{分类名} 可用）
		'takeover_desc'  => 0,    // 是否由插件输出 description（默认关闭，防止与主题重复）
		// 社交卡片
		'og_enable'      => 1,    // 输出 OG / Twitter 卡片
		'og_default_img' => '',   // 默认分享图 URL
		// 结构化数据
		'jsonld_enable'  => 1,    // 输出 JSON-LD
		'skip_article'   => 0,    // 跳过文章页 Article（主题已输出时勾选，避免重复）
		'org_name'       => '',   // Organization 名称（留空用站名）
		// AI 优化
		'robots_ai'      => 0,    // robots.txt 放行 AI 爬虫
		'llms_enable'    => 1,    // 启用 /llms.txt
		'llms_content'   => '',   // llms.txt 内容
		'tldr_enable'    => 1,    // 文章头部输出「要点速览」AI 摘要区块
		// Sitemap
		'sitemap_enable' => 1,    // 启用 /sitemap.xml
		'excluded_cats'  => array(), // 排除的分类 ID（sitemap 与 llms.txt 均跳过）
		// 薄页治理
		'noindex_thin'   => 1,    // 标签/作者/搜索/日期页 noindex
		// 图片
		'alt_enable'     => 1,    // 正文图片自动补 ALT
		// 百度推送
		'baidu_site'     => '',   // 站点域名，如 www.example.com
		'baidu_token'    => '',   // 百度推送 token
		// IndexNow（Bing/Yandex 等）
		'indexnow_key'   => '',   // 密钥，留空 = 关闭
		// 神马搜索推送
		'shenma_site'    => '',   // 站点域名
		'shenma_user'    => '',   // 神马站长平台用户名（邮箱）
		'shenma_token'   => '',   // 神马推送 token
		// 自动内链
		'autolink_enable' => 0,   // 开启自动内链
		'autolink_rules' => '',   // 规则：每行一组「关键词|URL」
		'autolink_max'   => '5',  // 每篇文章最多自动加几个内链
		// 抓取日志
		'spider_log'     => 1,    // 记录搜索引擎蜘蛛抓取 + 404 监控
		// 站长平台验证码（留空 = 不输出）
		'verify_baidu'   => '',   // 百度 baidu-site-verification
		'verify_bing'    => '',   // 必应 msvalidate.01
		'verify_shenma'  => '',   // 神马 shenma-site-verification
		'verify_sogou'   => '',   // 搜狗 sogou_site_verification
		'verify_so'      => '',   // 360 so-site-verification
		// 内容体检
		'fresh_months'   => '12', // 超过 N 个月未更新的文章视为「待刷新」
	);
}

function sqseo_opts() {
	static $opts = null;
	if ( null === $opts ) {
		$opts = wp_parse_args( get_option( 'sqseo_options', array() ), sqseo_defaults() );
	}
	return $opts;
}

/* =========================================================
 * 二、后台菜单与设置页
 * ======================================================= */

add_action( 'admin_menu', 'sqseo_admin_menu' );
function sqseo_admin_menu() {
	add_menu_page(
		'SQ SEO Toolkit',
		'SQ SEO',
		'manage_options',
		'sanqi-seo',
		'sqseo_page_tdk',
		'data:image/svg+xml;base64,' . base64_encode( '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><rect x="1" y="1" width="18" height="18" rx="5" fill="none" stroke="#a7aaad" stroke-width="1.6"/><path d="M5.2 11.4l2.4-2.8 2 2.2 3.2-3.9" fill="none" stroke="#a7aaad" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="13.6" cy="13.4" r="2.2" fill="none" stroke="#a7aaad" stroke-width="1.4"/><path d="M15.4 15.2l1.5 1.5" stroke="#a7aaad" stroke-width="1.4" stroke-linecap="round"/></svg>' ),
		81
	);
	$subs = array(
		array( '基础 TDK', 'sanqi-seo', 'sqseo_page_tdk' ),
		array( '社交卡片', 'sqseo-social', 'sqseo_page_social' ),
		array( '结构化数据', 'sqseo-schema', 'sqseo_page_schema' ),
		array( 'AI 搜索优化', 'sqseo-geo', 'sqseo_page_geo' ),
		array( 'Sitemap', 'sqseo-sitemap', 'sqseo_page_sitemap' ),
		array( 'HTML 地图', 'sqseo-htmlmap', 'sqseo_page_htmlmap' ),
		array( '链接推送', 'sqseo-push', 'sqseo_page_push' ),
		array( '图片优化', 'sqseo-image', 'sqseo_page_image' ),
		array( '收录控制', 'sqseo-index', 'sqseo_page_index' ),
		array( '301 重定向', 'sqseo-redirect', 'sqseo_page_redirect' ),
		array( '自动内链', 'sqseo-autolink', 'sqseo_page_autolink' ),
		array( '抓取日志', 'sqseo-crawl-log', 'sqseo_crawl_log_page' ),
		array( '内容体检', 'sqseo-content', 'sqseo_page_content_check' ),
		array( '推送日志', 'sqseo-push-log', 'sqseo_push_log_page' ),
		array( '备份 / 迁移', 'sqseo-backup', 'sqseo_page_backup' ),
	);
	foreach ( $subs as $s ) {
		add_submenu_page( 'sanqi-seo', 'SQ SEO Toolkit - ' . $s[0], $s[0], 'manage_options', $s[1], $s[2] );
	}
}

add_action( 'admin_init', 'sqseo_maybe_save' );
function sqseo_maybe_save() {
	if ( ! isset( $_POST['sqseo_save'] ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_save', 'sqseo_nonce' );

	$in    = isset( $_POST['sqseo'] ) ? (array) wp_unslash( $_POST['sqseo'] ) : array();
	$def   = sqseo_defaults();
	$types = sqseo_field_types();

	// 每个页面只提交自己的字段（隐藏域 sqseo_keys），按白名单逐项清洗后合并进现有选项
	$keys = array();
	if ( isset( $_POST['sqseo_keys'] ) ) {
		$keys = array_filter( array_map( 'trim', explode( ',', (string) wp_unslash( $_POST['sqseo_keys'] ) ) ) );
	}

	$opts = get_option( 'sqseo_options', array() );
	$opts = is_array( $opts ) ? $opts : array();

	foreach ( $keys as $key ) {
		if ( in_array( $key, $types['switch'], true ) ) {
			$opts[ $key ] = empty( $in[ $key ] ) ? 0 : 1;
		} elseif ( in_array( $key, $types['textarea'], true ) ) {
			$opts[ $key ] = isset( $in[ $key ] ) ? sanitize_textarea_field( $in[ $key ] ) : '';
		} elseif ( in_array( $key, $types['url'], true ) ) {
			$opts[ $key ] = isset( $in[ $key ] ) ? esc_url_raw( trim( $in[ $key ] ) ) : '';
		} elseif ( in_array( $key, $types['cats'], true ) ) {
			$opts[ $key ] = isset( $in[ $key ] ) ? array_values( array_filter( array_map( 'intval', (array) $in[ $key ] ) ) ) : array();
		} elseif ( in_array( $key, $types['verify'], true ) ) {
			$opts[ $key ] = isset( $in[ $key ] ) ? sqseo_sanitize_verify( $in[ $key ] ) : '';
		} elseif ( in_array( $key, $types['text'], true ) ) {
			$opts[ $key ] = isset( $in[ $key ] ) ? sanitize_text_field( $in[ $key ] ) : '';
		}
	}
	// 只保留默认键，防脏数据
	$opts = array_intersect_key( $opts, $def );

	update_option( 'sqseo_options', $opts );

	// 回跳到提交来源的页面
	$allowed_pages = array( 'sanqi-seo', 'sqseo-social', 'sqseo-schema', 'sqseo-geo', 'sqseo-sitemap', 'sqseo-htmlmap', 'sqseo-push', 'sqseo-image', 'sqseo-index', 'sqseo-redirect', 'sqseo-autolink', 'sqseo-crawl-log', 'sqseo-content', 'sqseo-push-log', 'sqseo-backup' );
	$page          = isset( $_POST['sqseo_page'] ) ? sanitize_key( wp_unslash( $_POST['sqseo_page'] ) ) : 'sanqi-seo';
	if ( ! in_array( $page, $allowed_pages, true ) ) {
		$page = 'sanqi-seo';
	}
	wp_safe_redirect( add_query_arg( array( 'page' => $page, 'sqseo_updated' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 字段类型注册表（新增设置项时在此登记，并加进对应页面的 sqseo_keys） */
function sqseo_field_types() {
	static $t = null;
	if ( null === $t ) {
		$t = array(
			// 单行文本
			'text'     => array( 'home_title', 'home_keywords', 'cat_title_tpl', 'cat_keywords', 'org_name', 'baidu_site', 'baidu_token', 'indexnow_key', 'shenma_site', 'shenma_user', 'shenma_token', 'autolink_max', 'verify_baidu', 'verify_bing', 'verify_shenma', 'verify_sogou', 'verify_so', 'fresh_months' ),
			// 多行文本
			'textarea' => array( 'home_desc', 'llms_content', 'autolink_rules' ),
			// URL
			'url'      => array( 'og_default_img' ),
			// 开关（checkbox 勾选=1，未勾=0）
			'switch'   => array( 'takeover_desc', 'og_enable', 'jsonld_enable', 'skip_article', 'robots_ai', 'llms_enable', 'tldr_enable', 'sitemap_enable', 'alt_enable', 'noindex_thin', 'autolink_enable', 'spider_log' ),
			// 分类 ID 数组
			'cats'     => array( 'excluded_cats' ),
			// 站长验证码（支持粘贴整行 <meta ...> 标签，自动提取 content）
			'verify'   => array( 'verify_baidu', 'verify_bing', 'verify_shenma', 'verify_sogou', 'verify_so' ),
		);
	}
	return $t;
}

/** 验证码清洗：粘贴整行 <meta name="..." content="xxx"> 时自动只取 content 值 */
function sqseo_sanitize_verify( $val ) {
	$val = trim( (string) $val );
	if ( '' === $val ) {
		return '';
	}
	if ( false !== stripos( $val, 'content=' ) && preg_match( '/content\s*=\s*["\']([^"\']+)["\']/i', $val, $m ) ) {
		return sanitize_text_field( $m[1] );
	}
	return sanitize_text_field( $val );
}

/** 品牌小 logo（内联 SVG，页头用） */
function sqseo_logo_svg() {
	return '<svg class="sqseo-logo" width="22" height="22" viewBox="0 0 22 22" aria-hidden="true"><defs><linearGradient id="sqseo-logo-g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#232e5c"/><stop offset="1" stop-color="#3858e9"/></linearGradient></defs><rect x="1" y="1" width="20" height="20" rx="6" fill="url(#sqseo-logo-g)"/><path d="M6.2 12.4l2.6-3 2.2 2.4 3.4-4.2" fill="none" stroke="#fff" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/><circle cx="15.4" cy="14.6" r="2.5" fill="none" stroke="#fff" stroke-width="1.6"/><path d="M17.3 16.5l1.7 1.7" stroke="#fff" stroke-width="1.6" stroke-linecap="round"/></svg>';
}

/** 右下角 Toast 提示（保存成功 / 清空完成等，JS 自动 3 秒消失，无 JS 时静态可见） */
function sqseo_toast( $html ) {
	echo '<div class="sqseo-toast" role="status"><span class="sqseo-toast-icon" aria-hidden="true">✓</span><div class="sqseo-toast-body">' . $html . '</div><button type="button" class="sqseo-toast-close" aria-label="关闭">&times;</button></div>';
}

/** 一键复制按钮（JS 负责写入剪贴板与「已复制」反馈；无 JS 时退化为普通按钮无副作用） */
function sqseo_copy_btn( $text ) {
	echo '<button type="button" class="sqseo-copy" data-copy="' . esc_attr( $text ) . '" title="复制地址"><span aria-hidden="true">📋</span> 复制</button>';
}

/** 页面公共头部（标题 + 保存提示 + 冲突提醒 + 用法说明 + 表单开头） */
function sqseo_page_open( $title, $intro, $keys, $slug ) {
	$updated = ! empty( $_GET['sqseo_updated'] );
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>SQ SEO Toolkit · <?php echo esc_html( $title ); ?></h1>
			<span class="sqseo-version">v<?php echo esc_html( SQSEO_VERSION ); ?></span>
		</div>

		<?php if ( $updated ) : ?>
			<?php sqseo_toast( '<b>已保存</b>' ); ?>
		<?php endif; ?>

		<?php sqseo_conflict_notice(); ?>

		<div class="sqseo-card sqseo-intro">
			<?php echo $intro; // 内部静态字符串，无用户输入 ?>
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'sqseo_save', 'sqseo_nonce' ); ?>
			<input type="hidden" name="sqseo_keys" value="<?php echo esc_attr( $keys ); ?>">
			<input type="hidden" name="sqseo_page" value="<?php echo esc_attr( $slug ); ?>">
	<?php
}

/** 页面公共尾部（保存按钮 + 关闭表单） */
function sqseo_page_close() {
	echo '<p class="sqseo-actions">';
	submit_button( '保存本页设置', 'primary large', 'sqseo_save', false );
	echo '</p></form></div>';
}

/** 后台样式：仅在本插件页面与文章编辑页加载 */
add_action( 'admin_enqueue_scripts', 'sqseo_admin_assets' );
function sqseo_admin_assets( $hook ) {
	$is_ours = ( false !== strpos( $hook, 'sanqi-seo' )
		|| false !== strpos( $hook, 'sqseo-' )
		|| in_array( $hook, array( 'post.php', 'post-new.php', 'edit.php' ), true ) );
	if ( ! $is_ours ) {
		return;
	}
	// 版本号附加文件修改时间：任何一次文件更新都会自动刷新浏览器缓存，不再依赖手工同步版本号
	$css_path = plugin_dir_path( __FILE__ ) . 'admin.css';
	$css_ver  = SQSEO_VERSION . '.' . (string) @filemtime( $css_path );
	wp_enqueue_style( 'sqseo-admin', SQSEO_URL . 'admin.css', array(), $css_ver );

	$js_path = plugin_dir_path( __FILE__ ) . 'admin.js';
	if ( file_exists( $js_path ) ) {
		$js_ver = SQSEO_VERSION . '.' . (string) @filemtime( $js_path );
		wp_enqueue_script( 'sqseo-admin', SQSEO_URL . 'admin.js', array(), $js_ver, true );
	}
}

/** ========== 页 1：基础 TDK ========== */
function sqseo_page_tdk() {
	$o = sqseo_opts();
	sqseo_page_open(
		'基础 TDK',
		'<b>这一页管什么：</b>控制搜索引擎结果页里显示的<b>标题、关键词、描述</b>。标题和描述是影响百度点击率的第一要素。<br>
		<b>怎么填：</b>标题 ≤ 25 个汉字，含品牌词；关键词 3～5 个英文逗号分隔；描述 80～120 字。<b>「接管 description」默认关闭</b>——若主题已在输出描述，保持关闭即可避免页面出现两份 description。每篇文章还可以在文章编辑页下方的「SQ SEO」面板里单独填写标题/关键词/描述（优先级高于本页模板）。',
		'home_title,home_keywords,home_desc,takeover_desc,cat_title_tpl,cat_keywords',
		'sanqi-seo'
	);
	?>
	<details class="sqseo-guide" id="sqseo-guide">
		<summary>🚀 3 分钟上手（第一次用点开看，老手可收起——状态会记住）</summary>
		<ol class="sqseo-guide-steps">
			<li><b>填首页信息：</b>在下面「首页」区填好标题、关键词、描述——这是搜索结果里别人看到的第一眼。</li>
			<li><b>检查 description：</b>「接管 description」默认关闭是对的；只有确认前台没有重复描述时才打开。</li>
			<li><b>提交地图：</b>到「Sitemap」页复制地址，粘贴到百度 / Bing / Google 三个站长平台（一次性工作）。之后每发一篇文章，推送是全自动的。</li>
		</ol>
	</details>
	<h2 class="title">首页</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sqseo-home-title">首页标题</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-home-title" name="sqseo[home_title]" value="<?php echo esc_attr( $o['home_title'] ); ?>" placeholder="例如：你的站点名 - 一句话说明">
				<p class="description">留空 = 不接管首页标题。建议 25 个汉字以内。<b>注意：保存后若前台标题没变化，多半是主题硬编码了标题输出，或页面被缓存</b>（此时标题仍由主题控制，其余功能不受影响）。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-home-kw">首页关键词</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-home-kw" name="sqseo[home_keywords]" value="<?php echo esc_attr( $o['home_keywords'] ); ?>" placeholder="例如：站点名,关键词1,关键词2,关键词3">
				<p class="description">3～5 个，英文逗号分隔。留空 = 不输出 keywords。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-home-desc">首页描述</label></th>
			<td>
				<textarea name="sqseo[home_desc]" id="sqseo-home-desc" rows="3" class="large-text" placeholder="80～120 字的一句话介绍"><?php echo esc_textarea( $o['home_desc'] ); ?></textarea>
				<p class="description"><b>仅在下方「接管 description」开启时才输出</b>。若主题已输出描述，请保持此处留空或关闭接管，避免页面出现两份 description。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-takeover">接管 description</label></th>
			<td>
				<label><input type="checkbox" name="sqseo[takeover_desc]" id="sqseo-takeover" value="1" <?php checked( $o['takeover_desc'], 1 ); ?>> 由本插件输出 description（首页用上面的描述；文章页自动取摘要）</label>
				<p class="description"><b class="sqseo-text-warn">默认关闭。</b>开启前请先在前台「查看网页源代码」确认当前只有 0 或 1 份 description。开启后请再检查一遍，确保全页只有 1 份。</p>
			</td>
		</tr>
	</table>

	<h2 class="title">分类归档页</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sqseo-cat-tpl">分类标题模板</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-cat-tpl" name="sqseo[cat_title_tpl]" value="<?php echo esc_attr( $o['cat_title_tpl'] ); ?>" placeholder="例如：{分类名} - 你的站点名">
				<p class="description">可用变量：<code>{分类名}</code>、<code>{站名}</code>。留空 = 不接管分类标题。模板会应用到<b>所有</b>分类页。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-cat-kw">分类关键词</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-cat-kw" name="sqseo[cat_keywords]" value="<?php echo esc_attr( $o['cat_keywords'] ); ?>" placeholder="例如：{分类名},{分类名}资源">
				<p class="description">可用变量：<code>{分类名}</code>。留空 = 不输出分类 keywords。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 2：社交卡片 ========== */
function sqseo_page_social() {
	$o = sqseo_opts();
	sqseo_page_open(
		'社交卡片（OpenGraph / Twitter Card）',
		'<b>这一页管什么：</b>把链接分享到<b>微信、微博、QQ、Twitter</b> 等平台时，显示成「标题 + 描述 + 大图」的富媒体卡片（不开启就只有一个光秃秃的链接）。<br>
		<b>怎么用：</b>开启开关即可，图片会自动取文章特色图；没有特色图时使用下面的默认分享图（建议传一张 1200×630 的站点 logo/品牌图到媒体库，把地址填进来）。<br>
		<b>怎么验证：</b>打开任意页面「查看网页源代码」搜 <code>og:title</code>，或把链接粘贴到微信里发给自己看效果。',
		'og_enable,og_default_img',
		'sqseo-social'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">输出 OG 卡片</th>
			<td>
				<label><input type="checkbox" name="sqseo[og_enable]" value="1" <?php checked( $o['og_enable'], 1 ); ?>> 在页面头部输出 og: 与 twitter: 标签（微信/微博/Twitter 分享出卡片）</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-og-img">默认分享图</label></th>
			<td>
				<input type="url" class="regular-text code" id="sqseo-og-img" name="sqseo[og_default_img]" value="<?php echo esc_attr( $o['og_default_img'] ); ?>" placeholder="https://example.com/wp-content/uploads/share.jpg">
				<p class="description">文章没有特色图时使用。建议 1200×630。优先级：文章特色图 → 正文第一张图 → 此默认图。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 3：结构化数据 ========== */
function sqseo_page_schema() {
	$o = sqseo_opts();
	sqseo_page_open(
		'结构化数据（JSON-LD）',
		'<b>这一页管什么：</b>给页面加一段机器可读的「自我介绍」（Schema.org 标准），让搜索引擎展示<b>富摘要</b>（星级、面包屑导航等），也帮助 AI 理解页面内容。<br>
		<b>输出内容：</b>WebSite（全站）+ Organization（首页品牌信息）+ Article（文章页）+ BreadcrumbList（文章页与分类页的面包屑导航）。<br>
		<b>怎么验证：</b>打开任意页面「查看网页源代码」搜 <code>application/ld+json</code>；或到 Google 富媒体测试工具 ( search.google.com/test/rich-results ) 输入 URL 检测。',
		'jsonld_enable,skip_article,org_name',
		'sqseo-schema'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">输出 JSON-LD</th>
			<td>
				<label><input type="checkbox" name="sqseo[jsonld_enable]" value="1" <?php checked( $o['jsonld_enable'], 1 ); ?>> WebSite（全站）+ Organization（首页）+ Article 与 BreadcrumbList（文章页）+ BreadcrumbList（分类页）</label>
			</td>
		</tr>
		<tr>
			<th scope="row">跳过 Article 输出</th>
			<td>
				<label><input type="checkbox" name="sqseo[skip_article]" value="1" <?php checked( $o['skip_article'], 1 ); ?>> 文章页不再输出 Article，仅保留面包屑</label>
				<p class="description"><b>先自查再决定，别默认勾选：</b>文章页「查看网页源代码」搜 <code>application/ld+json</code>——若只有<b>一段</b>（本插件输出）且搜不到 <code>headline</code> 字段，说明主题没输出 Article，<b>保持不勾</b>，插件会补上完整的 Article（对 SEO 和 AI 搜索都重要）；若看到<b>两段</b>且主题那段含 headline，才勾上避免重复。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-org">品牌名称</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-org" name="sqseo[org_name]" value="<?php echo esc_attr( $o['org_name'] ); ?>" placeholder="留空则使用站点标题">
				<p class="description">出现在 Organization 结构化数据里，建议与「设置 → 常规 → 站点标题」保持一致（如：你的站点名）。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 4：AI 搜索优化（GEO）========== */
function sqseo_page_geo() {
	$o = sqseo_opts();
	sqseo_page_open(
		'AI 搜索优化（GEO）',
		'<b>这一页管什么：</b>让你的站被 <b>豆包 / DeepSeek / ChatGPT / Kimi</b> 等 AI 搜索引擎「看见」并引用——这是近年新增的流量入口。<br>
		<b>两个动作：</b>① robots.txt 放行 AI 爬虫（让它们能来抓）；② llms.txt（给 AI 的站点说明书，告诉它你是谁、有什么内容）。<br>
		<b>进阶动作：</b>③ 在文章编辑框里给重点文章填「AI 摘要·要点速览」——AI 引用网站最看重的就是<b>确切的要点</b>（时间 / 数据 / 人名），这比关键词堆砌有效得多。<br>
		<b>注意：</b>本开关仅在站点根目录没有物理 robots.txt 文件时生效（WordPress 动态生成模式）。若服务器已有物理 robots.txt，请直接编辑该文件并保持本开关关闭。',
		'robots_ai,llms_enable,llms_content,tldr_enable',
		'sqseo-geo'
	);
	global $wpdb;
	$total   = (int) wp_count_posts( 'post' )->publish;
	$faq_n   = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sqseo_faq' AND meta_value <> ''" );
	$tldr_n  = (int) $wpdb->get_var( "SELECT COUNT(DISTINCT post_id) FROM {$wpdb->postmeta} WHERE meta_key = '_sqseo_tldr' AND meta_value <> ''" );
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">robots.txt 放行 AI 爬虫</th>
			<td>
				<label><input type="checkbox" name="sqseo[robots_ai]" value="1" <?php checked( $o['robots_ai'], 1 ); ?>> 在 robots.txt 追加放行规则（GPTBot / OAI-SearchBot / ClaudeBot / PerplexityBot / Google-Extended / Bytespider / Meta / Amazon 等 17 个）</label>
				<p class="description">生效前提：网站根目录<b>没有物理 robots.txt 文件</b>（由 WordPress 动态生成）。保存后访问 <code><?php echo esc_html( home_url( '/robots.txt' ) ); ?></code> 检查。</p>
			</td>
		</tr>
		<tr>
			<th scope="row">启用 llms.txt</th>
			<td>
				<label><input type="checkbox" name="sqseo[llms_enable]" value="1" <?php checked( $o['llms_enable'], 1 ); ?>> 输出 <code><?php echo esc_html( home_url( '/llms.txt' ) ); ?></code>（AI 爬虫的站点说明书），并附带全站索引 <code>/llms-full.txt</code></label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-llms">llms.txt 内容</label></th>
			<td>
				<textarea name="sqseo[llms_content]" id="sqseo-llms" rows="8" class="large-text code" placeholder="# 站点名称&#10;&#10;> 一句话介绍&#10;&#10;## 核心栏目&#10;- [栏目](URL): 说明"><?php echo esc_textarea( $o['llms_content'] ); ?></textarea>
				<p class="description">纯文本，Markdown 风格。留空且开关开启时，会自动生成一份基础版。内容随你定制：介绍站点定位、列出核心栏目、说明更新频率。</p>
			</td>
		</tr>
		<tr>
			<th scope="row">文章「要点速览」区块</th>
			<td>
				<label><input type="checkbox" name="sqseo[tldr_enable]" value="1" <?php checked( $o['tldr_enable'], 1 ); ?>> 填写了「AI 摘要」的文章，在正文开头渲染可见的要点速览区块</label>
				<p class="description">区块同时是 Article 结构化数据的 speakable 朗读源。不想要前台显示就关掉——AI 摘要仍会进入 JSON-LD 和 llms-full.txt。</p>
			</td>
		</tr>
	</table>

	<h2 class="title">GEO 体检（当前站点 AI 引用就绪度）</h2>
	<div class="sqseo-card sqseo-health">
		<?php
		$faq_pct  = $total > 0 ? min( 100, (int) round( $faq_n / $total * 100 ) ) : 0;
		$tldr_pct = $total > 0 ? min( 100, (int) round( $tldr_n / $total * 100 ) ) : 0;
		?>
		<div class="sqseo-health-row">
			<span class="sqseo-health-name">AI 爬虫可达</span>
			<span class="sqseo-meter"><i style="width:<?php echo $o['robots_ai'] ? 90 : 20; ?>%"></i></span>
			<span class="sqseo-health-desc"><?php if ( $o['robots_ai'] ) : ?>
				17 个 AI 爬虫已通过插件开关放行；若使用物理 robots.txt，请确认其中也有放行规则
			<?php else : ?>
				开关关闭 — 如站点使用物理 robots.txt，请确认其中已包含 AI 放行规则
			<?php endif; ?></span>
			<span class="sqseo-badge <?php echo $o['robots_ai'] ? 'sqseo-badge--ok' : 'sqseo-badge--warn'; ?>"><?php echo $o['robots_ai'] ? '✅ 良好' : '待确认'; ?></span>
		</div>
		<div class="sqseo-health-row">
			<span class="sqseo-health-name">llms.txt</span>
			<span class="sqseo-meter"><i style="width:<?php echo $o['llms_enable'] ? 100 : 0; ?>%"></i></span>
			<span class="sqseo-health-desc"><?php if ( $o['llms_enable'] ) : ?>
				<a href="<?php echo esc_url( home_url( '/llms.txt' ) ); ?>" target="_blank">/llms.txt</a> · <a href="<?php echo esc_url( home_url( '/llms-full.txt' ) ); ?>" target="_blank">/llms-full.txt</a> 双文件在线
				<?php sqseo_copy_btn( home_url( '/llms.txt' ) ); ?>
			<?php else : ?>
				未启用 — 去「AI 搜索优化」页打开开关
			<?php endif; ?></span>
			<span class="sqseo-badge <?php echo $o['llms_enable'] ? 'sqseo-badge--ok' : 'sqseo-badge--err'; ?>"><?php echo $o['llms_enable'] ? '✅ 良好' : '未启用'; ?></span>
		</div>
		<div class="sqseo-health-row">
			<span class="sqseo-health-name">FAQ 覆盖</span>
			<span class="sqseo-meter"><i style="width:<?php echo (int) $faq_pct; ?>%"></i></span>
			<span class="sqseo-health-desc"><?php echo $faq_n; ?> / <?php echo $total; ?> 篇 · FAQPage 是 AI 搜索最爱引用的格式，覆盖越多机会越大</span>
			<span class="sqseo-badge <?php echo $faq_pct >= 20 ? 'sqseo-badge--ok' : ( $faq_n > 0 ? 'sqseo-badge--warn' : 'sqseo-badge--warn' ); ?>"><?php echo $faq_n > 0 ? ( $faq_pct >= 20 ? '✅ 良好' : '进行中' ) : '起步'; ?></span>
		</div>
		<div class="sqseo-health-row">
			<span class="sqseo-health-name">AI 摘要覆盖</span>
			<span class="sqseo-meter"><i style="width:<?php echo (int) $tldr_pct; ?>%"></i></span>
			<span class="sqseo-health-desc"><?php echo $tldr_n; ?> / <?php echo $total; ?> 篇 · 建议先把最热门的 20～30 篇补齐（要点含时间/集数/人名）</span>
			<span class="sqseo-badge <?php echo $tldr_pct >= 20 ? 'sqseo-badge--ok' : 'sqseo-badge--warn'; ?>"><?php echo $tldr_n > 0 ? ( $tldr_pct >= 20 ? '✅ 良好' : '进行中' ) : '起步'; ?></span>
		</div>
		<div class="sqseo-health-row">
			<span class="sqseo-health-name">结构化数据</span>
			<span class="sqseo-meter"><i style="width:<?php echo $o['jsonld_enable'] ? 100 : 0; ?>%"></i></span>
			<span class="sqseo-health-desc"><?php echo $o['jsonld_enable'] ? 'JSON-LD 已启用（WebSite / Organization / Article / BreadcrumbList / FAQPage）' : '已关闭 — 去「结构化数据」页开启'; ?></span>
			<span class="sqseo-badge <?php echo $o['jsonld_enable'] ? 'sqseo-badge--ok' : 'sqseo-badge--err'; ?>"><?php echo $o['jsonld_enable'] ? '✅ 良好' : '已关闭'; ?></span>
		</div>
	</div>
	<p class="description">「AI 收录查询 / ChatGPT 排名监控」这类功能需要调用外部服务，本插件坚持无授权、无第三方依赖，用上面的覆盖度指标做日常运营参考即可。</p>
	<?php
	sqseo_page_close();
}

/** ========== 页 5：Sitemap ========== */
function sqseo_page_sitemap() {
	$o = sqseo_opts();
	sqseo_page_open(
		'Sitemap（站点地图）',
		'<b>这一页管什么：</b>给搜索引擎提供一份「全站页面清单」，是新内容被发现的最快通道。<br>
		<b>怎么用：</b>保持开启，把 sitemap 地址提交到三个站长平台：百度搜索资源平台 / Bing Webmaster / Google Search Console（各平台「站点地图/链接提交」处粘贴地址即可，一次性工作）。<br>
		<b>本地图只含：</b>文章 + 分类页，<b>自动排除</b>标签页、作者页、页面（防止空薄页稀释权重）。下方勾选的分类及其文章也会被排除。',
		'sitemap_enable,excluded_cats',
		'sqseo-sitemap'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">启用 sitemap</th>
			<td>
				<label><input type="checkbox" name="sqseo[sitemap_enable]" value="1" <?php checked( $o['sitemap_enable'], 1 ); ?>> 输出 <code><?php echo esc_html( home_url( '/sitemap.xml' ) ); ?></code>（仅文章 + 分类，自动排除标签/作者/页面）</label>
				<?php sqseo_copy_btn( home_url( '/sitemap.xml' ) ); ?>
				<p class="description">与 WordPress 自带的 wp-sitemap.xml 互不冲突，二者提交任意一个到站长平台即可。</p>
			</td>
		</tr>
		<tr>
			<th scope="row">排除分类</th>
			<td>
				<?php
				$all_cats = get_categories( array( 'hide_empty' => false ) );
				if ( $all_cats ) :
					?>
					<div class="sqseo-chip-row">
					<?php
					foreach ( $all_cats as $cat ) :
						?>
						<label class="sqseo-chip">
							<input type="checkbox" name="sqseo[excluded_cats][]" value="<?php echo esc_attr( $cat->term_id ); ?>" <?php checked( in_array( (int) $cat->term_id, (array) $o['excluded_cats'], true ) ); ?>>
							<?php echo esc_html( $cat->name ); ?>（<?php echo (int) $cat->count; ?> 篇）
						</label>
						<?php
					endforeach;
					?>
					</div>
					<?php
				endif;
				?>
				<p class="description">勾选的分类及其下的文章<b>不会出现在 sitemap 和 llms.txt</b>（页面本身照常访问，只是不主动提交）。适合内容跑题或长期停更的栏目，例如只有 2 篇工具教程的「云盘」。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 6：链接推送 ========== */
function sqseo_page_push() {
	$o = sqseo_opts();
	sqseo_page_open(
		'链接推送',
		'<b>这一页管什么：</b>发布新文章时，把链接<b>主动推给搜索引擎</b>（不等它们自己来爬），加快收录速度。<br>
		<b>百度推送：</b>到百度搜索资源平台 → 「普通收录 → API推送」拿到 token，两个框都填才生效。<br>
		<b>IndexNow（Bing/Yandex 等）：</b>自己编一串 16～32 位小写字母数字填入即可，验证文件自动生成。<br>
		<b>神马搜索：</b>到神马站长平台 → 「数据提交 → MIP数据提交」拿 token，三个框都填才生效（选填，适合移动端流量）。<br>
		<b>推送结果去哪看：</b>左侧「SQ SEO → 推送日志」，每次推送的时间、文章和结果都有记录，可手动补推。',
		'baidu_site,baidu_token,indexnow_key,shenma_site,shenma_user,shenma_token',
		'sqseo-push'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sqseo-baidu-site">百度推送 - 站点</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-baidu-site" name="sqseo[baidu_site]" value="<?php echo esc_attr( $o['baidu_site'] ); ?>" placeholder="www.example.com">
				<p class="description">不带 https:// 前缀。需与百度搜索资源平台登记的域名完全一致。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-baidu-token">百度推送 - token</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-baidu-token" name="sqseo[baidu_token]" value="<?php echo esc_attr( $o['baidu_token'] ); ?>" placeholder="在百度搜索资源平台「链接提交→API推送」里获取">
				<p class="description">填好后，文章<b>发布或更新</b>时会自动推送给百度，加速收录。两个框都填才生效。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-indexnow-key">IndexNow 密钥</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-indexnow-key" name="sqseo[indexnow_key]" value="<?php echo esc_attr( $o['indexnow_key'] ); ?>" placeholder="32 位随机字符串，例如：a1b2c3...">
				<p class="description">
					文章发布时自动推送给 <b>Bing / Yandex 等</b>（IndexNow 协议），加速海外引擎收录。<b>留空 = 关闭</b>。<br>
					密钥自己随便编一串小写字母数字（16～32 位）即可，插件会自动在 <code><?php echo esc_html( home_url( '/' ) ); ?>密钥.txt</code> 生成验证文件，无需手动上传。
				</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-shenma-site">神马推送 - 站点</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-shenma-site" name="sqseo[shenma_site]" value="<?php echo esc_attr( $o['shenma_site'] ); ?>" placeholder="www.example.com">
				<p class="description">不带 https:// 前缀。选填——主要吃移动端神马搜索流量的站才需要；下面两项都填才生效。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-shenma-user">神马推送 - 用户名</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-shenma-user" name="sqseo[shenma_user]" value="<?php echo esc_attr( $o['shenma_user'] ); ?>" placeholder="神马站长平台登录邮箱">
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-shenma-token">神马推送 - token</label></th>
			<td>
				<input type="text" class="regular-text code" id="sqseo-shenma-token" name="sqseo[shenma_token]" value="<?php echo esc_attr( $o['shenma_token'] ); ?>" placeholder="神马站长平台「数据提交 → MIP数据提交」里获取">
				<p class="description">文章发布时自动推送给<b>神马搜索</b>，结果记入推送日志。三个框都填才生效。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 7：图片优化 ========== */
function sqseo_page_image() {
	$o = sqseo_opts();
	sqseo_page_open(
		'图片优化',
		'<b>这一页管什么：</b>图片 alt 属性是百度图片搜索的收录依据，也是无障碍访问的必需项。<br>
		<b>怎么用：</b>开启后，文章正文里缺少 alt 的图片会自动补上文章标题作为 alt（只补正文图片；主题模板输出的列表图补不到，那需要改主题）。<br>
		<b>小提示：</b>给重点文章手动写更精确的 alt（如「文章主题 + 画面描述」）效果更好，本功能是保底方案。',
		'alt_enable',
		'sqseo-image'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">自动补图片 ALT</th>
			<td>
				<label><input type="checkbox" name="sqseo[alt_enable]" value="1" <?php checked( $o['alt_enable'], 1 ); ?>> 文章正文中缺少 alt 的图片，自动补上文章标题作为 alt</label>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/** ========== 页 8：收录控制 ========== */
function sqseo_page_index() {
	$o = sqseo_opts();
	sqseo_page_open(
		'收录控制（noindex）',
		'<b>这一页管什么：</b>告诉搜索引擎「哪些页面不要收录」。资源站的标签页/作者页/搜索页通常是空薄页，被收录反而稀释全站权重。<br>
		<b>怎么用：</b>保持下方开关开启即可。开启后这些页面会输出 <code>noindex,follow</code>——不收录它们，但仍然顺着页面里的链接继续爬文章。<br>
		<b>不影响：</b>文章页和分类页的正常收录，放心开启。已收录的薄页会在搜索引擎下次更新时逐步移出。',
		'noindex_thin,verify_baidu,verify_bing,verify_shenma,verify_sogou,verify_so',
		'sqseo-index'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">薄页 noindex</th>
			<td>
				<label><input type="checkbox" name="sqseo[noindex_thin]" value="1" <?php checked( $o['noindex_thin'], 1 ); ?>> 标签页 / 作者页 / 搜索页 / 日期归档页 输出 noindex,follow（阻止空薄页被收录，权重集中到文章与分类页）</label>
				<p class="description"><b class="sqseo-text-ok">建议保持开启</b>——这是资源站薄页治理的标准做法。不影响文章页和分类页的正常收录。</p>
			</td>
		</tr>
	</table>

	<h2 class="title">站长平台验证码</h2>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row"><label for="sqseo-verify-baidu">百度</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-verify-baidu" name="sqseo[verify_baidu]" value="<?php echo esc_attr( $o['verify_baidu'] ); ?>" placeholder="例如：codeva-XXXXXX">
				<p class="description">直接把平台给的<b>整行 &lt;meta ...&gt; 代码粘贴进来就行</b>，插件自动提取 content 值（也可以只填那串值）。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-verify-bing">必应 Bing</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-verify-bing" name="sqseo[verify_bing]" value="<?php echo esc_attr( $o['verify_bing'] ); ?>" placeholder="例如：0123456789ABCDEF...">
				<p class="description">Bing 站长工具验证码，对应 <code>&lt;meta name="msvalidate.01"&gt;</code>。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-verify-shenma">神马</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-verify-shenma" name="sqseo[verify_shenma]" value="<?php echo esc_attr( $o['verify_shenma'] ); ?>">
				<p class="description">神马站长平台验证码，对应 <code>&lt;meta name="shenma-site-verification"&gt;</code>。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-verify-sogou">搜狗</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-verify-sogou" name="sqseo[verify_sogou]" value="<?php echo esc_attr( $o['verify_sogou'] ); ?>">
				<p class="description">搜狗站长平台验证码，对应 <code>&lt;meta name="sogou_site_verification"&gt;</code>。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-verify-so">360 搜索</label></th>
			<td>
				<input type="text" class="regular-text" id="sqseo-verify-so" name="sqseo[verify_so]" value="<?php echo esc_attr( $o['verify_so'] ); ?>">
				<p class="description">360 站长平台验证码，对应 <code>&lt;meta name="so-site-verification"&gt;</code>（旧字段名为 360-site-verification，两种都兼容的场景下以平台提示为准）。</p>
			</td>
		</tr>
	</table>
	<p class="description">验证码只对「首页」输出 meta（各平台都只检测首页）。全部留空 = 什么都不输出。填好保存后，回对应站长平台点「验证」即可。<b>验证不通过先查两件事：</b>① 保存后回本页看输入框里是否只剩那串值（粘整行代码会自动提取，若没提取到说明格式不对）；② 去前台首页 Ctrl+F5 查看网页源代码搜索 <code>site-verification</code>，看不到就是缓存没刷新——先清页面缓存插件与 CDN 缓存，再回去验证。</p>
	<?php
	sqseo_page_close();
}

/** 前台输出站长平台验证 meta（仅首页，priority 1 早早输出） */
add_action( 'wp_head', 'sqseo_site_verification', 1 );
function sqseo_site_verification() {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}
	$o   = sqseo_opts();
	$map = array(
		'verify_baidu'  => 'baidu-site-verification',
		'verify_bing'   => 'msvalidate.01',
		'verify_shenma' => 'shenma-site-verification',
		'verify_sogou'  => 'sogou_site_verification',
		'verify_so'     => 'so-site-verification',
	);
	foreach ( $map as $key => $name ) {
		$content = sqseo_sanitize_verify( $o[ $key ] ); // 兼容旧数据：误存整行 <meta> 也会自动提取 content
		if ( '' !== $content ) {
			echo '<meta name="' . esc_attr( $name ) . '" content="' . esc_attr( $content ) . '">' . "\n";
		}
	}
}

/** 检测其他 SEO 插件是否同时启用，防止重复 meta */
function sqseo_conflict_notice() {
	if ( ! function_exists( 'is_plugin_active' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$conflicts = array(
		'wordpress-seo/wp-seo.php'       => 'Yoast SEO',
		'seo-by-rank-math/rank-math.php' => 'Rank Math',
		'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'All in One SEO',
		'baiduseo/baiduseo.php'          => '百度站长SEO合集',
	);
	$found = array();
	foreach ( $conflicts as $file => $name ) {
		if ( is_plugin_active( $file ) ) {
			$found[] = $name;
		}
	}
	if ( ! $found ) {
		return;
	}
	echo '<div class="notice notice-warning"><p><b>检测到其他 SEO 插件正在运行：</b>' . esc_html( implode( '、', $found ) ) . '。同时运行会输出重复 meta，请只保留一个（建议去「插件」页停用其他 SEO 插件后再用本插件）。</p></div>';
}

/* =========================================================
 * 三、前台输出：标题 / keywords / description
 * ======================================================= */

/** 标题接管（优先级 9999，晚于主题，仅在填写了内容时生效） */
add_filter( 'pre_get_document_title', 'sqseo_filter_title', 9999 );
function sqseo_filter_title( $title ) {
	$o = sqseo_opts();
	if ( is_front_page() || is_home() ) {
		if ( '' !== $o['home_title'] ) {
			return $o['home_title'];
		}
	} elseif ( is_singular( 'post' ) ) {
		// 优先使用文章编辑框里填的自定义标题
		$custom_title = get_post_meta( get_the_ID(), '_sqseo_title', true );
		if ( '' !== $custom_title ) {
			return $custom_title;
		}
	} elseif ( is_category() ) {
		if ( '' !== $o['cat_title_tpl'] ) {
			$cat = get_queried_object();
			if ( $cat instanceof WP_Term ) {
				return strtr( $o['cat_title_tpl'], array(
					'{分类名}' => $cat->name,
					'{站名}'   => get_bloginfo( 'name' ),
				) );
			}
		}
	}
	return $title;
}

/** keywords / description 输出 */
add_action( 'wp_head', 'sqseo_head_meta', 1 );
function sqseo_head_meta() {
	$o = sqseo_opts();
	$keywords = '';
	$desc     = '';

	if ( is_front_page() || is_home() ) {
		$keywords = $o['home_keywords'];
		$desc     = $o['takeover_desc'] ? $o['home_desc'] : '';
	} elseif ( is_category() ) {
		$cat = get_queried_object();
		if ( $cat instanceof WP_Term && '' !== $o['cat_keywords'] ) {
			$keywords = str_replace( '{分类名}', $cat->name, $o['cat_keywords'] );
		}
	} elseif ( is_singular( 'post' ) ) {
		$custom_desc = get_post_meta( get_the_ID(), '_sqseo_desc', true );
		if ( $o['takeover_desc'] ) {
			if ( '' !== $custom_desc ) {
				// 编辑框自定义描述优先
				$desc = $custom_desc;
			} else {
				$desc = wp_strip_all_tags( get_the_excerpt() );
				if ( function_exists( 'mb_substr' ) && mb_strlen( $desc, 'UTF-8' ) > 140 ) {
					$desc = mb_substr( $desc, 0, 140, 'UTF-8' );
				} elseif ( strlen( $desc ) > 280 ) {
					$desc = function_exists( 'mb_strcut' ) ? mb_strcut( $desc, 0, 280, 'UTF-8' ) : substr( $desc, 0, 280 );
				}
			}
		}
		// keywords：v2.3.6 起文章页不再输出。部分主题会自带文章页 keywords，
		// 插件再输出会造成同页两份 keywords；且 keywords 标签 Google/百度均已忽略，
		// 去重零损失。文章页 description 照常输出（主题通常未输出 name=description，缺它不行）。
		$keywords = '';
	}

	if ( '' !== $keywords ) {
		echo '<meta name="keywords" content="' . esc_attr( $keywords ) . '">' . "\n";
	}
	if ( '' !== $desc ) {
		echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
}

/* =========================================================
 * 四、OG / Twitter 卡片
 * ======================================================= */

add_action( 'wp_head', 'sqseo_head_og', 2 );
function sqseo_head_og() {
	$o = sqseo_opts();
	if ( ! $o['og_enable'] ) {
		return;
	}

	$url      = home_url( '/' );
	$type     = 'website';
	$title    = get_bloginfo( 'name' );
	$desc     = get_bloginfo( 'description' );
	$image    = $o['og_default_img'];

	if ( is_singular( 'post' ) ) {
		$type  = 'article';
		$title = get_the_title();
		$url   = get_permalink();
		$desc  = wp_strip_all_tags( get_the_excerpt() );
		if ( function_exists( 'mb_substr' ) && mb_strlen( $desc, 'UTF-8' ) > 120 ) {
			$desc = mb_substr( $desc, 0, 120, 'UTF-8' ) . '…';
		}
		$thumb = get_the_post_thumbnail_url( null, 'large' );
		if ( $thumb ) {
			$image = $thumb;
		} else {
			// 兜底：正文第一张图
			$content = get_post_field( 'post_content' );
			if ( $content && preg_match( '/<img\b[^>]*src=["\']([^"\']+)["\']/i', $content, $m ) ) {
				$image = $m[1];
			}
		}
	} elseif ( is_category() ) {
		$cat   = get_queried_object();
		$title = single_cat_title( '', false ) . ' - ' . get_bloginfo( 'name' );
		$url   = get_category_link( $cat );
	} elseif ( is_front_page() || is_home() ) {
		if ( '' !== $o['home_title'] ) {
			$title = $o['home_title'];
		}
	}

	// 注：即使没有图片也输出基本卡片（og:title/og:url 仍有价值），仅跳过图片标签

	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
	if ( '' !== $desc ) {
		echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	}
	if ( '' !== $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( '' !== $image ) {
		echo '<meta name="twitter:image" content="' . esc_url( $image ) . '">' . "\n";
	}
}

/* =========================================================
 * 五、JSON-LD 结构化数据
 * ======================================================= */

add_action( 'wp_head', 'sqseo_head_jsonld', 3 );
function sqseo_head_jsonld() {
	$o = sqseo_opts();
	if ( ! $o['jsonld_enable'] ) {
		return;
	}

	$brand  = '' !== $o['org_name'] ? $o['org_name'] : get_bloginfo( 'name' );
	$home   = home_url( '/' );
	$graph  = array();

	// WebSite（全站）
	$graph[] = array(
		'@type'           => 'WebSite',
		'@id'             => $home . '#website',
		'url'             => $home,
		'name'            => get_bloginfo( 'name' ),
		'description'     => get_bloginfo( 'description' ),
		'inLanguage'      => get_bloginfo( 'language' ),
		'publisher'       => array( '@id' => $home . '#organization' ),
	);

	// Organization（首页）
	if ( is_front_page() || is_home() ) {
		$graph[] = array(
			'@type' => 'Organization',
			'@id'   => $home . '#organization',
			'name'  => $brand,
			'url'   => $home,
		);
	}

	// 分类页：面包屑
	if ( is_category() ) {
		$cat = get_queried_object();
		if ( $cat instanceof WP_Term ) {
			$graph[] = sqseo_breadcrumb( array(
				array( 'name' => get_bloginfo( 'name' ), 'url' => $home ),
				array( 'name' => $cat->name, 'url' => get_category_link( $cat ) ),
			) );
		}
	}

	// 文章页：Article + 面包屑
	if ( is_singular( 'post' ) ) {
		$pid   = get_the_ID();
		$img   = get_the_post_thumbnail_url( $pid, 'large' );
		if ( ! $img && '' !== $o['og_default_img'] ) {
			$img = $o['og_default_img'];
		}
		$art = array(
			'@type'            => 'Article',
			'@id'              => get_permalink() . '#article',
			'headline'         => get_the_title(),
			'url'              => get_permalink(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'inLanguage'       => get_bloginfo( 'language' ),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'publisher'        => array( '@id' => $home . '#organization' ),
			'mainEntityOfPage' => get_permalink(),
		);
		if ( $img ) {
			$art['image'] = $img;
		}
		// AI 摘要：作为 Article 描述 + speakable（AI 搜索引用时可直接朗读/摘取要点）
		$tldr = trim( (string) get_post_meta( $pid, '_sqseo_tldr', true ) );
		if ( '' !== $tldr ) {
			$art['description'] = str_replace( array( "\r\n", "\n" ), '；', $tldr );
			$art['speakable']   = array(
				'@type'       => 'SpeakableSpecification',
				'cssSelector' => array( '.sqseo-tldr' ),
			);
		}
		if ( ! $o['skip_article'] ) {
			$graph[] = $art;
		}

		$cats = get_the_category( $pid );
		$crumb = array(
			array( 'name' => get_bloginfo( 'name' ), 'url' => $home ),
		);
		if ( $cats && ! is_wp_error( $cats ) ) {
			$crumb[] = array( 'name' => $cats[0]->name, 'url' => get_category_link( $cats[0] ) );
		}
		$crumb[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
		$graph[] = sqseo_breadcrumb( $crumb );

		// FAQ 问答对（若有）→ FAQPage Schema
		$faq_items = sqseo_parse_faq( get_post_meta( $pid, '_sqseo_faq', true ) );
		if ( $faq_items ) {
			$graph[] = sqseo_faq_schema_items( $faq_items );
		}
	}

	if ( ! $graph ) {
		return;
	}

	$data = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
	echo '<script type="application/ld+json">' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}

function sqseo_breadcrumb( $items ) {
	$list = array();
	$pos  = 1;
	foreach ( $items as $it ) {
		$list[] = array(
			'@type'    => 'ListItem',
			'position' => $pos++,
			'name'     => $it['name'],
			'item'     => $it['url'],
		);
	}
	return array(
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $list,
	);
}

/* =========================================================
 * 六、robots.txt：AI 爬虫放行
 * ======================================================= */

add_filter( 'robots_txt', 'sqseo_robots_txt', 10, 2 );
function sqseo_robots_txt( $output, $public ) {
	$o = sqseo_opts();
	if ( ! $o['robots_ai'] ) {
		return $output;
	}
	$ai_bots = array(
		'GPTBot', 'OAI-SearchBot', 'ChatGPT-User', 'ClaudeBot', 'Claude-Web', 'anthropic-ai',
		'PerplexityBot', 'Perplexity-User', 'Google-Extended', 'Bytespider', 'Applebot', 'Applebot-Extended',
		'Meta-ExternalAgent', 'Amazonbot', 'YouBot', 'Diffbot', 'cohere-ai',
	);
	$out = "\n# AI 爬虫放行（SQ SEO Toolkit）\n";
	foreach ( $ai_bots as $bot ) {
		$out .= "User-agent: {$bot}\nAllow: /\n";
	}
	return $output . $out;
}

/* =========================================================
 * 七、llms.txt 与 sitemap.xml 输出（init 早期拦截）
 * ======================================================= */

add_action( 'init', 'sqseo_route_files', 1 );
function sqseo_route_files() {
	$o = sqseo_opts();
	if ( empty( $_SERVER['REQUEST_URI'] ) ) {
		return;
	}
	$path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
	if ( ! is_string( $path ) ) {
		return;
	}
	$path = untrailingslashit( $path );

	if ( $o['llms_enable'] && '/llms.txt' === $path ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo sqseo_llms_text();
		exit;
	}

	if ( $o['llms_enable'] && '/llms-full.txt' === $path ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo sqseo_llms_full_text();
		exit;
	}

	// IndexNow 密钥验证文件：/密钥.txt
	$inkey = trim( (string) $o['indexnow_key'] );
	if ( '' !== $inkey && '/' . rawurlencode( $inkey ) . '.txt' === $path ) {
		header( 'Content-Type: text/plain; charset=utf-8' );
		echo $inkey;
		exit;
	}

	if ( $o['sitemap_enable'] && ( '/sitemap.xml' === $path || '/sitemap_index.xml' === $path ) ) {
		header( 'Content-Type: application/xml; charset=utf-8' );
		echo sqseo_sitemap_xml();
		exit;
	}
}

function sqseo_llms_text() {
	$o = sqseo_opts();
	if ( '' !== trim( (string) $o['llms_content'] ) ) {
		return $o['llms_content'];
	}
	// 自动生成基础版
	$lines   = array();
	$lines[] = '# ' . get_bloginfo( 'name' );
	$lines[] = '';
	$lines[] = '> ' . ( get_bloginfo( 'description' ) ? get_bloginfo( 'description' ) : get_bloginfo( 'name' ) . '，持续更新' );
	$lines[] = '';
	$lines[] = '## 核心栏目';
	$cats = get_categories( array(
		'hide_empty' => true,
		'exclude'    => (array) $o['excluded_cats'],
	) );
	foreach ( $cats as $cat ) {
		$lines[] = '- [' . $cat->name . '](' . get_category_link( $cat ) . '): ' . $cat->name . '分类下的内容列表';
	}
	$lines[] = '';
	$lines[] = '## 说明';
	$lines[] = '- 内容持续更新，页面包含标题、摘要与发布日期';
	$lines[] = '- 数据截至 ' . date_i18n( 'Y 年 m 月' );
	return implode( "\n", $lines ) . "\n";
}

/** llms-full.txt：全站文章标题+链接+摘要版（供 AI 一次读完全站） */
function sqseo_llms_full_text() {
	$o    = sqseo_opts();
	$opts = array(
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'numberposts'      => 1000, // 上限防超大
		'orderby'          => 'modified',
		'order'            => 'DESC',
		'suppress_filters' => true,
	);
	if ( ! empty( $o['excluded_cats'] ) ) {
		$opts['category__not_in'] = (array) $o['excluded_cats'];
	}
	$posts = get_posts( $opts );

	$lines   = array();
	$lines[] = '# ' . get_bloginfo( 'name' ) . ' — 全站内容索引';
	$lines[] = '';
	$lines[] = '> 共 ' . count( $posts ) . ' 篇，按更新时间倒序。每条含标题、链接、发布日期与摘要。';
	$lines[] = '';

	foreach ( $posts as $p ) {
		$lines[] = '## ' . get_the_title( $p );
		$lines[] = '- 链接: ' . get_permalink( $p );
		$lines[] = '- 发布: ' . get_the_date( 'Y-m-d', $p );
		// 优先使用文章的「AI 摘要（要点速览）」，没有则回退到普通摘要
		$tldr = trim( (string) get_post_meta( $p->ID, '_sqseo_tldr', true ) );
		$excerpt = '';
		if ( '' !== $tldr ) {
			$excerpt = str_replace( array( "\r\n", "\n" ), '；', $tldr );
		} else {
			$excerpt = wp_strip_all_tags( get_the_excerpt( $p ) );
			$excerpt = html_entity_decode( $excerpt, ENT_QUOTES, 'UTF-8' );
			$excerpt = str_replace( array( '[…]', '[...]' ), '', $excerpt );
			$excerpt = trim( $excerpt );
			if ( function_exists( 'mb_substr' ) && mb_strlen( $excerpt, 'UTF-8' ) > 160 ) {
				$excerpt = mb_substr( $excerpt, 0, 160, 'UTF-8' ) . '…';
			} elseif ( strlen( $excerpt ) > 320 ) {
				$excerpt = function_exists( 'mb_strcut' ) ? mb_strcut( $excerpt, 0, 320, 'UTF-8' ) . '…' : substr( $excerpt, 0, 320 ) . '…';
			}
		}
		if ( '' !== $excerpt ) {
			$lines[] = '- 摘要: ' . $excerpt;
		}
		$lines[] = '';
	}

	return implode( "\n", $lines ) . "\n";
}

function sqseo_sitemap_xml() {
	$urls = array();

	// 首页
	$home_lastmod = get_lastpostmodified( 'GMT' );
	$urls[]       = array(
		'loc'     => home_url( '/' ),
		'lastmod' => $home_lastmod ? gmdate( 'c', strtotime( $home_lastmod ) ) : '',
	);

	// 分类（有内容的；排除用户勾选的分类）
	$o    = sqseo_opts();
	$cats = get_categories( array(
		'hide_empty' => true,
		'number'     => 500,
		'exclude'    => (array) $o['excluded_cats'],
	) );
	foreach ( $cats as $cat ) {
		$urls[] = array( 'loc' => get_category_link( $cat ), 'lastmod' => '' );
	}

	// 文章（含修改时间，按修改时间倒序；排除被排除分类下的文章）
	$posts = get_posts( array(
		'post_type'        => 'post',
		'post_status'      => 'publish',
		'numberposts'      => 5000,
		'orderby'          => 'modified',
		'order'            => 'DESC',
		'suppress_filters' => true,
		'category__not_in' => (array) $o['excluded_cats'],
	) );
	foreach ( $posts as $p ) {
		$urls[] = array( 'loc' => get_permalink( $p ), 'lastmod' => get_post_modified_time( 'c', true, $p ) );
	}

	$xml  = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
	$xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
	foreach ( $urls as $u ) {
		$xml .= "  <url>\n";
		$xml .= '    <loc>' . esc_url( $u['loc'] ) . "</loc>\n";
		if ( ! empty( $u['lastmod'] ) ) {
			$xml .= '    <lastmod>' . esc_html( $u['lastmod'] ) . "</lastmod>\n";
		}
		$xml .= "  </url>\n";
	}
	$xml .= "</urlset>\n";
	return $xml;
}

/* =========================================================
 * 八、图片 ALT 自动补全
 * ======================================================= */

add_filter( 'the_content', 'sqseo_img_alt', 20 );
function sqseo_img_alt( $content ) {
	$o = sqseo_opts();
	if ( ! $o['alt_enable'] || ! is_singular( 'post' ) || is_feed() || empty( $content ) ) {
		return $content;
	}
	$title = get_the_title();
	if ( '' === $title ) {
		return $content;
	}
	return preg_replace_callback( '/<img\b[^>]*>/i', function ( $m ) use ( $title ) {
		if ( false !== stripos( $m[0], ' alt=' ) ) {
			return $m[0];
		}
		return str_replace( '<img', '<img alt="' . esc_attr( $title ) . '"', $m[0] );
	}, $content );
}

/* =========================================================
 * 九、百度主动推送（免授权，直接用官方 API）
 * ======================================================= */

add_action( 'transition_post_status', 'sqseo_on_post_publish', 10, 3 );
function sqseo_on_post_publish( $new_status, $old_status, $post ) {
	if ( 'publish' !== $new_status || 'post' !== $post->post_type ) {
		return;
	}
	// 避免自动保存/修订触发
	if ( wp_is_post_revision( $post->ID ) || wp_is_post_autosave( $post->ID ) ) {
		return;
	}
	sqseo_do_baidu_push( $post->ID );
	sqseo_do_indexnow_push( $post->ID );
	sqseo_do_shenma_push( $post->ID );
}

/** 执行百度推送（可复用；返回是否执行了推送） */
function sqseo_do_baidu_push( $post_id ) {
	$o = sqseo_opts();
	if ( '' === trim( $o['baidu_site'] ) || '' === trim( $o['baidu_token'] ) ) {
		return false;
	}

	$api = 'https://data.zz.baidu.com/urls?site=' . rawurlencode( $o['baidu_site'] ) . '&token=' . rawurlencode( $o['baidu_token'] );

	$args = array(
		'timeout'  => 10,
		'headers'  => array( 'Content-Type' => 'text/plain' ),
		'body'     => get_permalink( $post_id ),
		'sslverify' => true,
	);

	$res = wp_remote_post( $api, $args );

	// 部分海外服务器 DNS 对百度域名解析异常，导致证书域名不匹配（cURL error 60）。
	// 此时降级重试一次：跳过证书校验（链路仍为 TLS 加密，仅发送 URL 列表，风险可接受）。
	// 注意：WP_Error 的 code 是 http_request_failed，cURL 错误号只存在于消息文本中，须从消息里匹配。
	$via_fallback = false;
	if ( is_wp_error( $res ) && preg_match( '/cURL error (5[1-9]|6[0-9])/', $res->get_error_message() ) ) {
		$args['sslverify'] = false;
		$res               = wp_remote_post( $api, $args );
		$via_fallback      = true;
	}

	if ( is_wp_error( $res ) ) {
		update_post_meta( $post_id, '_sqseo_push_result', '❌ 推送失败：' . $res->get_error_message() );
	} else {
		// 把百度返回的 JSON 翻译成人话
		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		if ( is_array( $body ) && isset( $body['success'] ) && (int) $body['success'] > 0 ) {
			$text = '✅ 推送成功';
			if ( isset( $body['remain'] ) ) {
				$text .= '（今日剩余配额 ' . (int) $body['remain'] . ' 条）';
			}
		} elseif ( is_array( $body ) && isset( $body['message'] ) ) {
			$text = '❌ 推送失败：' . sanitize_text_field( (string) $body['message'] );
			if ( isset( $body['code'] ) ) {
				$text .= '（code ' . (int) $body['code'] . '）';
			}
		} else {
			$text = '推送结果：' . wp_remote_retrieve_body( $res );
		}
		if ( $via_fallback ) {
			$text .= '〔备用通道〕';
		}
		update_post_meta( $post_id, '_sqseo_push_result', $text );
	}
	update_post_meta( $post_id, '_sqseo_push_time', time() );
	return true;
}

/** 执行 IndexNow 推送（Bing/Yandex/Seznam 共用入口） */
function sqseo_do_indexnow_push( $post_id ) {
	$o   = sqseo_opts();
	$key = trim( (string) $o['indexnow_key'] );
	if ( '' === $key ) {
		return false;
	}

	$payload = wp_json_encode( array(
		'host'        => wp_parse_url( home_url(), PHP_URL_HOST ),
		'key'         => $key,
		'keyLocation' => home_url( '/' . $key . '.txt' ),
		'urlList'     => array( get_permalink( $post_id ) ),
	) );

	$args = array(
		'timeout'   => 10,
		'headers'   => array( 'Content-Type' => 'application/json; charset=utf-8' ),
		'body'      => $payload,
		'sslverify' => true,
	);

	$res = wp_remote_post( 'https://api.indexnow.org/indexnow', $args );

	// 与百度推送相同：服务器证书链异常（cURL error 51/60-69）时降级重试一次
	$via_fallback = false;
	if ( is_wp_error( $res ) && preg_match( '/cURL error (5[1-9]|6[0-9])/', $res->get_error_message() ) ) {
		$args['sslverify'] = false;
		$res               = wp_remote_post( 'https://api.indexnow.org/indexnow', $args );
		$via_fallback      = true;
	}

	if ( is_wp_error( $res ) ) {
		update_option( 'sqseo_indexnow_last', '失败：' . $res->get_error_message() );
	} else {
		$code = wp_remote_retrieve_response_code( $res );
		$msg  = array(
			200 => '成功（已接收）',
			202 => '成功（已排队）',
			400 => '失败：请求格式错误',
			403 => '失败：密钥无效',
			422 => '失败：URL 与密钥不匹配',
			429 => '失败：请求过于频繁',
		);
		update_option( 'sqseo_indexnow_last', current_time( 'm-d H:i' ) . ' ' . ( isset( $msg[ $code ] ) ? $msg[ $code ] : 'HTTP ' . $code ) . ( $via_fallback ? '〔备用通道〕' : '' ) );
	}
	return true;
}

/** 执行神马搜索推送（MIP 数据提交接口，与百度同款 POST 协议） */
function sqseo_do_shenma_push( $post_id ) {
	$o = sqseo_opts();
	if ( '' === trim( $o['shenma_site'] ) || '' === trim( $o['shenma_user'] ) || '' === trim( $o['shenma_token'] ) ) {
		return false;
	}

	$api = 'https://data.zhanzhang.sm.cn/push?site=' . rawurlencode( $o['shenma_site'] )
		. '&user_name=' . rawurlencode( $o['shenma_user'] )
		. '&resource_name=mip_add&token=' . rawurlencode( $o['shenma_token'] );

	$args = array(
		'timeout'   => 10,
		'headers'   => array( 'Content-Type' => 'text/plain' ),
		'body'      => get_permalink( $post_id ),
		'sslverify' => true,
	);

	$res = wp_remote_post( $api, $args );

	// 与百度推送相同：证书链异常（cURL error 51/60-69）时降级重试一次
	$via_fallback = false;
	if ( is_wp_error( $res ) && preg_match( '/cURL error (5[1-9]|6[0-9])/', $res->get_error_message() ) ) {
		$args['sslverify'] = false;
		$res               = wp_remote_post( $api, $args );
		$via_fallback      = true;
	}

	if ( is_wp_error( $res ) ) {
		update_post_meta( $post_id, '_sqseo_sm_result', '❌ 推送失败：' . $res->get_error_message() );
	} else {
		$body = json_decode( wp_remote_retrieve_body( $res ), true );
		$rc   = is_array( $body ) && isset( $body['returnCode'] ) ? (int) $body['returnCode'] : -1;
		if ( 200 === $rc ) {
			$text = '✅ 推送成功';
		} elseif ( is_array( $body ) && isset( $body['errorMsg'] ) && '' !== (string) $body['errorMsg'] ) {
			$text = '❌ 推送失败：' . sanitize_text_field( (string) $body['errorMsg'] ) . '（returnCode ' . $rc . '）';
		} else {
			$known = array(
				201 => '失败：token 不合法',
				202 => '失败：当日推送配额已用完',
				400 => '失败：请求参数有误',
				500 => '失败：神马服务器内部错误',
			);
			$text = isset( $known[ $rc ] ) ? '❌ ' . $known[ $rc ] : '❌ 推送失败：未知返回（returnCode ' . $rc . '）';
		}
		update_post_meta( $post_id, '_sqseo_sm_result', $text . ( $via_fallback ? '〔备用通道〕' : '' ) );
	}
	update_post_meta( $post_id, '_sqseo_push_time', time() );
	return true;
}

/** 文章编辑页显示最近一次推送结果 */
add_action( 'post_submitbox_misc_actions', 'sqseo_push_result_box' );
function sqseo_push_result_box( $post ) {
	if ( 'post' !== $post->post_type ) {
		return;
	}
	$result = get_post_meta( $post->ID, '_sqseo_push_result', true );
	if ( '' === $result ) {
		return;
	}
	echo '<div class="misc-pub-section">百度推送结果：<code class="sqseo-code">' . esc_html( $result ) . '</code></div>';
}

/* =========================================================
 * 九-2、推送日志页（百度 + IndexNow，支持手动补推）
 * ======================================================= */

/** 手动补推（admin_post 处理器） */
add_action( 'admin_post_sqseo_manual_push', 'sqseo_manual_push_handler' );
function sqseo_manual_push_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_manual_push' );

	$post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
	if ( $post_id && 'post' === get_post_type( $post_id ) ) {
		sqseo_do_baidu_push( $post_id );
		sqseo_do_shenma_push( $post_id );
	}

	wp_safe_redirect( add_query_arg(
		array( 'page' => 'sqseo-push-log', 'sqseo_pushed' => 1 ),
		admin_url( 'admin.php' )
	) );
	exit;
}

/** 推送日志页 */
function sqseo_push_log_page() {
	$o       = sqseo_opts();
	$pushed  = ! empty( $_GET['sqseo_pushed'] );
	$innow   = get_option( 'sqseo_indexnow_last', '' );

	// 最近 50 篇有推送记录的文章
	$q = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => 50,
		'orderby'        => 'meta_value_num',
		'meta_key'       => '_sqseo_push_time',
		'order'          => 'DESC',
		'post_status'    => 'publish',
	) );
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>推送日志</h1>
		<span class="sqseo-version">SQ SEO Toolkit</span>
		</div>

		<?php if ( $pushed ) : ?>
			<?php sqseo_toast( '<b>已手动补推。</b>结果几秒后会刷新到下方记录里。' ); ?>
		<?php endif; ?>

		<?php
		// 今日推送统计（遍历最近 50 篇的推送结果，按今天时间戳与结果文本分类）
		$today_start = (int) current_time( 'timestamp' ) - (int) current_time( 'timestamp' ) % 86400;
		$ok_today = $fail_today = 0;
		foreach ( $q->posts as $p ) {
			$pt = (int) get_post_meta( $p->ID, '_sqseo_push_time', true );
			if ( $pt < $today_start ) {
				continue;
			}
			$pr = (string) get_post_meta( $p->ID, '_sqseo_push_result', true );
			if ( false !== strpos( $pr, '推送成功' ) ) {
				$ok_today++;
			} elseif ( '' !== $pr ) {
				$fail_today++;
			}
		}
		$innow_configured = '' !== trim( $o['indexnow_key'] );
		$innow_ok         = $innow_configured && ( '' === $innow || false === strpos( $innow, '失败' ) );
		?>
		<div class="sqseo-stats">
			<div class="sqseo-stat">
				<span class="sqseo-stat-num <?php echo $ok_today > 0 ? 'sqseo-stat-num--ok' : ''; ?>"><?php echo (int) $ok_today; ?></span>
				<span class="sqseo-stat-label">今日推送成功</span>
				<span class="sqseo-stat-sub">百度 + 神马，按文章计</span>
			</div>
			<div class="sqseo-stat">
				<span class="sqseo-stat-num <?php echo $fail_today > 0 ? 'sqseo-stat-num--err' : ''; ?>"><?php echo (int) $fail_today; ?></span>
				<span class="sqseo-stat-label">今日推送失败</span>
				<span class="sqseo-stat-sub"><?php echo $fail_today > 0 ? '看下方记录列的失败原因' : '全部正常，保持节奏'; ?></span>
			</div>
			<div class="sqseo-stat">
				<span class="sqseo-stat-icon" aria-hidden="true"><?php echo $innow_ok ? '✅' : ( $innow_configured ? '⚠️' : '—' ); ?></span>
				<span class="sqseo-stat-label">IndexNow</span>
				<span class="sqseo-stat-sub"><?php
					if ( ! $innow_configured ) {
						echo '未配置（选填，覆盖 Bing 等）';
					} elseif ( '' !== $innow ) {
						echo esc_html( $innow );
					} else {
						echo '已配置，发布后自动推送';
					}
				?></span>
			</div>
		</div>

		<h2 class="title">推送通道状态</h2>
		<table class="widefat striped sqseo-status-table">
			<tbody>
				<tr>
					<td class="sqseo-col-name"><b>百度主动推送</b></td>
					<td>
						<?php if ( '' !== trim( $o['baidu_site'] ) && '' !== trim( $o['baidu_token'] ) ) : ?>
							<span class="sqseo-status--ok">✅ 已配置</span>（<?php echo esc_html( $o['baidu_site'] ); ?>）
						<?php else : ?>
							<span class="sqseo-status--off">未配置</span> — 去「SQ SEO → 链接推送」页填站点域名和 token
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td><b>IndexNow（Bing 等）</b></td>
					<td>
						<?php if ( '' !== trim( $o['indexnow_key'] ) ) : ?>
							<span class="sqseo-status--ok">✅ 已配置</span>
							<?php if ( '' !== $innow ) : ?>
								，最近结果：<code class="sqseo-code"><?php echo esc_html( $innow ); ?></code>
							<?php endif; ?>
						<?php else : ?>
							<span class="sqseo-status--off">未配置</span> — 去「SQ SEO → 链接推送」页生成并填入密钥（留空 = 关闭）
						<?php endif; ?>
					</td>
				</tr>
				<tr>
					<td><b>神马搜索推送</b></td>
					<td>
						<?php if ( '' !== trim( $o['shenma_site'] ) && '' !== trim( $o['shenma_user'] ) && '' !== trim( $o['shenma_token'] ) ) : ?>
							<span class="sqseo-status--ok">✅ 已配置</span>（<?php echo esc_html( $o['shenma_site'] ); ?>）
						<?php else : ?>
							<span class="sqseo-status--off">未配置</span> — 选填；需要时去「SQ SEO → 链接推送」页填站点/用户名/token
						<?php endif; ?>
					</td>
				</tr>
			</tbody>
		</table>

		<h2 class="title">推送记录（最近 50 篇）</h2>
		<?php if ( ! $q->have_posts() ) : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">📨</span>
				<p class="sqseo-empty-title">还没有推送记录</p>
				<p class="sqseo-empty-desc">发布（或更新）文章且配置好百度 token 后，这里会自动出现记录。也可以先在上方挑一篇文章手动补推验证通道。</p>
			</div>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th class="sqseo-col-time">时间</th>
						<th>文章</th>
						<th class="sqseo-col-result">百度结果</th>
						<th class="sqseo-col-result">神马结果</th>
						<th class="sqseo-col-op">操作</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $q->posts as $p ) : ?>
					<tr>
						<td class="sqseo-mono"><?php
							$t = (int) get_post_meta( $p->ID, '_sqseo_push_time', true );
							echo $t ? esc_html( date_i18n( 'm-d H:i', $t ) ) : '—';
						?></td>
						<td><a href="<?php echo esc_url( get_edit_post_link( $p->ID ) ); ?>"><?php echo esc_html( get_the_title( $p ) ); ?></a></td>
						<td><code class="sqseo-code"><?php echo esc_html( get_post_meta( $p->ID, '_sqseo_push_result', true ) ); ?></code></td>
						<td><code class="sqseo-code"><?php echo esc_html( get_post_meta( $p->ID, '_sqseo_sm_result', true ) ?: '—' ); ?></code></td>
						<td><a class="button button-small" href="<?php echo esc_url( wp_nonce_url(
							add_query_arg( array(
								'action' => 'sqseo_manual_push',
								'post'   => $p->ID,
							), admin_url( 'admin-post.php' ) ),
							'sqseo_manual_push'
						) ); ?>">手动补推</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		<?php wp_reset_postdata(); ?>
	</div>
	<?php
}

/* =========================================================
 * 九-3、抓取日志：蜘蛛访问记录 + 404 死链监控
 * ======================================================= */

/** 识别蜘蛛 UA（返回中文名，非蜘蛛返回空串） */
function sqseo_detect_bot( $ua ) {
	$bots = array(
		'Baiduspider'   => '百度',
		'Googlebot'     => '谷歌',
		'bingbot'       => '必应',
		'360Spider'     => '360',
		'YisouSpider'   => '神马',
		'Sogou'         => '搜狗',
		'Bytespider'    => '头条',
		'PetalBot'      => '花瓣',
		'GPTBot'        => 'GPTBot',
		'OAI-SearchBot' => 'OpenAI',
		'ClaudeBot'     => 'Claude',
		'PerplexityBot' => 'Perplexity',
		'Meta-ExternalAgent' => 'Meta',
		'Amazonbot'     => 'Amazon',
		'Applebot'      => 'Apple',
	);
	foreach ( $bots as $sig => $name ) {
		if ( false !== stripos( $ua, $sig ) ) {
			return $name;
		}
	}
	return '';
}

/** 前台记录：蜘蛛抓取 + 404 死链（写入 option，限量滚动覆盖） */
add_action( 'template_redirect', 'sqseo_track_crawl' );
function sqseo_track_crawl() {
	$o = sqseo_opts();
	if ( empty( $o['spider_log'] ) || is_admin() || is_feed() || is_user_logged_in() ) {
		return;
	}
	$ua  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? (string) $_SERVER['HTTP_USER_AGENT'] : '';
	$bot = sqseo_detect_bot( $ua );
	$url = esc_url_raw( home_url( add_query_arg( null, null ) ) );
	$ref = isset( $_SERVER['HTTP_REFERER'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) : '';
	$now = time();
	$status = is_404() ? 404 : 200;

	// 蜘蛛抓取记录（最多 100 条，另有每日定时清理兜底）
	if ( '' !== $bot ) {
		$log = get_option( 'sqseo_spider_log', array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		array_unshift( $log, array( 't' => $now, 'b' => $bot, 'u' => $url, 's' => $status ) );
		if ( count( $log ) > 100 ) {
			$log = array_slice( $log, 0, 100 );
		}
		update_option( 'sqseo_spider_log', $log, false );
	}

	// 404 记录（按 URL 聚合，最多 100 条）
	if ( is_404() ) {
		$log = get_option( 'sqseo_404_log', array() );
		if ( ! is_array( $log ) ) {
			$log = array();
		}
		if ( isset( $log[ $url ] ) ) {
			$log[ $url ]['c'] = (int) $log[ $url ]['c'] + 1;
			$log[ $url ]['t'] = $now;
		} else {
			$log[ $url ] = array( 'c' => 1, 't' => $now, 'r' => $ref );
			// 超限时移除最旧的一条
			if ( count( $log ) > 100 ) {
				uasort( $log, function ( $a, $b ) { return $a['t'] <=> $b['t']; } );
				array_shift( $log );
			}
		}
		update_option( 'sqseo_404_log', $log, false );
	}
}

/** 每日定时清理：蜘蛛日志/404 聚合各只保留最近 100 条，扫描报告 90 天过期 */
add_action( 'sqseo_daily_cleanup', 'sqseo_daily_cleanup' );
function sqseo_daily_cleanup() {
	$log = get_option( 'sqseo_spider_log', array() );
	if ( is_array( $log ) && count( $log ) > 100 ) {
		update_option( 'sqseo_spider_log', array_slice( $log, 0, 100 ), false );
	}
	$n404 = get_option( 'sqseo_404_log', array() );
	if ( is_array( $n404 ) && count( $n404 ) > 100 ) {
		uasort( $n404, function ( $a, $b ) { return $b['t'] <=> $a['t']; } ); // 按时间新→旧
		update_option( 'sqseo_404_log', array_slice( $n404, 0, 100, true ), false );
	}
	$rep = get_option( 'sqseo_link_report', array() );
	if ( is_array( $rep ) && ! empty( $rep['t'] ) && (int) $rep['t'] < time() - 90 * DAY_IN_SECONDS ) {
		delete_option( 'sqseo_link_report' );
	}
}

/** 保证清理任务已排期（后台每次加载自检，无需依赖激活钩子） */
add_action( 'admin_init', 'sqseo_schedule_cleanup' );
function sqseo_schedule_cleanup() {
	if ( ! wp_next_scheduled( 'sqseo_daily_cleanup' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'sqseo_daily_cleanup' );
	}
}

register_deactivation_hook( __FILE__, 'sqseo_deactivate_cron' );
function sqseo_deactivate_cron() {
	wp_clear_scheduled_hook( 'sqseo_daily_cleanup' );
}

/** 清空抓取记录（admin_post 处理器） */
add_action( 'admin_post_sqseo_clear_crawl', 'sqseo_clear_crawl_handler' );
function sqseo_clear_crawl_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_clear_crawl' );
	$type = isset( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : '';
	if ( 'spider' === $type ) {
		delete_option( 'sqseo_spider_log' );
	} elseif ( '404' === $type ) {
		delete_option( 'sqseo_404_log' );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'sqseo-crawl-log', 'sqseo_cleared' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 抓取日志页 */
function sqseo_crawl_log_page() {
	$o       = sqseo_opts();
	$cleared = ! empty( $_GET['sqseo_cleared'] );
	$updated = ! empty( $_GET['sqseo_updated'] );
	$spider  = get_option( 'sqseo_spider_log', array() );
	$notfound = get_option( 'sqseo_404_log', array() );
	if ( ! is_array( $spider ) ) {
		$spider = array();
	}
	if ( ! is_array( $notfound ) ) {
		$notfound = array();
	}
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>抓取日志</h1>
			<span class="sqseo-version">SQ SEO Toolkit</span>
		</div>

		<?php if ( $updated ) : ?>
			<?php sqseo_toast( '<b>已保存。</b>' ); ?>
		<?php endif; ?>

		<?php if ( $cleared ) : ?>
			<?php sqseo_toast( '<b>已清空。</b>后续抓取会重新开始记录。' ); ?>
		<?php endif; ?>

		<?php if ( ! empty( $_GET['sqseo_rd_added'] ) ) : ?>
			<?php sqseo_toast( '<b>301 重定向已添加。</b>可在「301 重定向」页管理；已删除文章的 404 建议同时在百度站长「死链提交」里提交一次。' ); ?>
		<?php endif; ?>

		<div class="sqseo-card sqseo-intro">
			<b>这一页管什么：</b>记录搜索引擎蜘蛛来爬过哪些页面、以及访客碰到的 <b>404 死链</b>，帮你确认收录通道是否正常、发现站内断链。页面默认只展开最近一段记录，更早的收在「展开更早的…」折叠条里；<b>日志每天自动清理</b>，各保留最近 100 条，无需手动维护。<br>
			<b>注意：</b>被 CDN 或页面缓存命中的请求到不了 PHP，不会计入——记录的多是缓存未命中的抓取，数量偏少属正常现象。<br>
			<b>修复建议：</b>站内来源的 404 优先改正链接；已删文章的 404 可到百度站长「死链提交」提交，加速从索引移除。
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'sqseo_save', 'sqseo_nonce' ); ?>
			<input type="hidden" name="sqseo_keys" value="spider_log">
			<input type="hidden" name="sqseo_page" value="sqseo-crawl-log">
			<h2 class="title">记录开关</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row">开启抓取记录</th>
					<td>
						<label><input type="checkbox" name="sqseo[spider_log]" value="1" <?php checked( $o['spider_log'], 1 ); ?>> 记录蜘蛛抓取与 404 死链（每天自动清理，只保留最近 100 条 / 100 个 URL，不会撑大数据库）</label>
					</td>
				</tr>
			</table>
			<p class="sqseo-actions"><?php submit_button( '保存本页设置', 'primary large', 'sqseo_save', false ); ?></p>
		</form>

		<?php
		// 蜘蛛来源分布（按最近记录聚合，纯 PHP 计数，无额外查询）
		$bot_names = array(
			'百度' => '百度', '谷歌' => '谷歌', '必应' => '必应', '360' => '360',
			'神马' => '神马', '搜狗' => '搜狗', '头条' => '头条', '花瓣' => '花瓣',
			'GPTBot' => 'GPTBot', 'OAI-SearchBot' => 'OpenAI', 'ChatGPT-User' => 'ChatGPT',
			'ClaudeBot' => 'Claude', 'PerplexityBot' => 'Perplexity', 'Perplexity-User' => 'Perplexity',
			'Google-Extended' => '谷歌AI', 'Bytespider' => '字节', 'Meta-ExternalAgent' => 'Meta',
			'Amazonbot' => 'Amazon', 'Applebot' => 'Apple', 'YouBot' => 'You.com',
			'Diffbot' => 'Diffbot', 'cohere-ai' => 'Cohere',
		);
		$dist = array();
		foreach ( $spider as $row ) {
			$b = (string) $row['b'];
			$label = isset( $bot_names[ $b ] ) ? $bot_names[ $b ] : $b;
			if ( '' === $label ) {
				$label = '其他';
			}
			$dist[ $label ] = ( isset( $dist[ $label ] ) ? $dist[ $label ] : 0 ) + 1;
		}
		arsort( $dist );
		$dist_total = array_sum( $dist );
		// 分布条分段配色（低饱和、与强调色系协调）
		$dist_colors = array( '#3858e9', '#7c8cf0', '#22a06b', '#e5684a', '#b45309', '#8250c4', '#0e7490', '#64748b', '#a8a29e', '#d97706' );
		?>
		<h2 class="title">蜘蛛抓取记录（最近 <?php echo count( $spider ); ?> 条）</h2>
		<?php if ( ! empty( $dist ) && $dist_total > 0 ) : ?>
			<div class="sqseo-dist">
				<div class="sqseo-dist-bar" role="img" aria-label="蜘蛛来源分布比例">
					<?php $di = 0; foreach ( $dist as $label => $n ) : $pct = $n / $dist_total * 100; if ( $di >= count( $dist_colors ) ) { break; } ?>
						<span style="width:<?php echo esc_attr( round( $pct, 2 ) ); ?>%;background:<?php echo esc_attr( $dist_colors[ $di ] ); ?>;" title="<?php echo esc_attr( $label . ' ' . $n . ' 次（' . round( $pct ) . '%）' ); ?>"></span>
					<?php $di++; endforeach; ?>
				</div>
				<div class="sqseo-dist-legend">
					<?php $di = 0; foreach ( array_slice( $dist, 0, count( $dist_colors ), true ) as $label => $n ) : ?>
						<span class="sqseo-dist-item"><i style="background:<?php echo esc_attr( $dist_colors[ $di ] ); ?>;"></i><?php echo esc_html( $label ); ?> <?php echo (int) round( $n / $dist_total * 100 ); ?>%</span>
					<?php $di++; endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
		<?php if ( empty( $spider ) ) : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">🕷️</span>
				<p class="sqseo-empty-title">暂无蜘蛛记录</p>
				<p class="sqseo-empty-desc">开启记录后，等蜘蛛来爬（或去百度站长手动抓一条）就会出现数据。被缓存命中的请求到不了 PHP，不会计入——记录的多是缓存未命中的抓取。</p>
			</div>
		<?php else :
			$render_spider_rows = function ( array $rows ) {
				foreach ( $rows as $row ) :
					$s_code = (int) $row['s'];
					if ( $s_code >= 200 && $s_code < 300 ) {
						$s_badge = 'sqseo-badge--ok';
					} elseif ( $s_code >= 400 ) {
						$s_badge = 'sqseo-badge--err';
					} else {
						$s_badge = 'sqseo-badge--warn';
					}
					?>
					<tr>
						<td class="sqseo-mono"><?php echo esc_html( date_i18n( 'm-d H:i:s', (int) $row['t'] ) ); ?></td>
						<td><?php echo esc_html( $row['b'] ); ?></td>
						<td><code class="sqseo-code"><?php echo esc_html( $row['u'] ); ?></code></td>
						<td><span class="sqseo-badge <?php echo $s_badge; // 由上方白名单逻辑生成 ?>"><?php echo $s_code; ?></span></td>
					</tr>
					<?php
				endforeach;
			};
			$spider_recent = array_slice( $spider, 0, 15 );
			$spider_older  = array_slice( $spider, 15 );
			?>
			<p>
				<a class="button button-small sqseo-btn-danger" data-confirm="确定清空全部蜘蛛记录吗？此操作不可恢复。" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'sqseo_clear_crawl', 'type' => 'spider' ), admin_url( 'admin-post.php' ) ), 'sqseo_clear_crawl' ) ); ?>">清空蜘蛛记录</a>
			</p>
			<table class="widefat striped">
				<thead>
					<tr>
						<th class="sqseo-col-time">时间</th>
						<th class="sqseo-col-op">蜘蛛</th>
						<th>URL</th>
						<th class="sqseo-col-op">状态码</th>
					</tr>
				</thead>
				<tbody>
				<?php $render_spider_rows( $spider_recent ); ?>
				</tbody>
			</table>
			<?php if ( ! empty( $spider_older ) ) : ?>
				<details class="sqseo-fold">
					<summary>展开更早的 <?php echo count( $spider_older ); ?> 条记录</summary>
					<table class="widefat striped">
						<thead>
							<tr>
								<th class="sqseo-col-time">时间</th>
								<th class="sqseo-col-op">蜘蛛</th>
								<th>URL</th>
								<th class="sqseo-col-op">状态码</th>
							</tr>
						</thead>
						<tbody>
						<?php $render_spider_rows( $spider_older ); ?>
						</tbody>
					</table>
				</details>
				<p class="description">日志每天自动清理，只保留最近 100 条——更早的记录会被滚动移除，不需要手动维护。</p>
			<?php endif; ?>
		<?php endif; ?>

		<h2 class="title">404 死链监控（<?php echo count( $notfound ); ?> 个 URL）</h2>
		<?php if ( ! empty( $notfound ) ) : ?>
			<p>
				<a class="button button-small sqseo-btn-danger" data-confirm="确定清空全部 404 记录吗？此操作不可恢复。" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'sqseo_clear_crawl', 'type' => '404' ), admin_url( 'admin-post.php' ) ), 'sqseo_clear_crawl' ) ); ?>">清空 404 记录</a>
			</p>
			<?php
			$rd_home = home_url( '/' );
			$render_404_rows = function ( array $rows ) use ( $rd_home ) {
				foreach ( $rows as $nf_url => $nf_row ) :
					$nf_path = (string) wp_parse_url( $nf_url, PHP_URL_PATH );
					?>
					<tr>
						<td><code class="sqseo-code"><?php echo esc_html( $nf_url ); ?></code></td>
						<td><?php echo (int) $nf_row['c']; ?></td>
						<td class="sqseo-mono"><?php echo esc_html( date_i18n( 'm-d H:i', (int) $nf_row['t'] ) ); ?></td>
						<td><code class="sqseo-code"><?php echo esc_html( isset( $nf_row['r'] ) ? $nf_row['r'] : '—' ); ?></code></td>
						<td>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="sqseo-rd-form">
								<input type="hidden" name="action" value="sqseo_redirect_add">
								<?php wp_nonce_field( 'sqseo_redirect_add' ); ?>
								<input type="hidden" name="sqseo_redirect_from" value="<?php echo esc_attr( $nf_path ); ?>">
								<input type="hidden" name="sqseo_back" value="crawl">
								<input type="text" name="sqseo_redirect_to" class="sqseo-inline-input" value="<?php echo esc_attr( $rd_home ); ?>" aria-label="301 目标地址" title="改成想让旧地址跳去的目标，再点 301">
								<button type="submit" class="button button-small">301 →</button>
							</form>
						</td>
					</tr>
					<?php
				endforeach;
			};
			$nf_recent = array_slice( $notfound, 0, 30, true );
			$nf_older  = array_slice( $notfound, 30, null, true );
			?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>死链 URL</th>
						<th class="sqseo-col-op">次数</th>
						<th class="sqseo-col-time">最近一次</th>
						<th>来源页面（找修复线索）</th>
						<th class="sqseo-col-op">一键 301</th>
					</tr>
				</thead>
				<tbody>
				<?php $render_404_rows( $nf_recent ); ?>
				</tbody>
			</table>
			<?php if ( ! empty( $nf_older ) ) : ?>
				<details class="sqseo-fold">
					<summary>展开更早的 <?php echo count( $nf_older ); ?> 个死链 URL</summary>
					<table class="widefat striped">
						<thead>
							<tr>
								<th>死链 URL</th>
								<th class="sqseo-col-op">次数</th>
								<th class="sqseo-col-time">最近一次</th>
								<th>来源页面（找修复线索）</th>
								<th class="sqseo-col-op">一键 301</th>
							</tr>
						</thead>
						<tbody>
						<?php $render_404_rows( $nf_older ); ?>
						</tbody>
					</table>
				</details>
			<?php endif; ?>
			<p class="description">修复建议：站内来源的 404 优先改正链接；已删文章的 404 可在右侧填目标地址点「301 →」加跳转规则（目标建议填对应分类页，比跳首页体验好），并在百度站长「死链提交」里提交，加速从索引移除。404 聚合每天自动清理，只保留最近 100 个 URL。</p>
		<?php else : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">🛡️</span>
				<p class="sqseo-empty-title">暂无 404 记录——这是好事</p>
				<p class="sqseo-empty-desc">有人（或蜘蛛）访问到不存在的 URL 时会自动记在这里，届时记得回来查来源。</p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* =========================================================
 * 九-4、自动内链：关键词自动加站内链接
 * ======================================================= */

add_filter( 'the_content', 'sqseo_auto_links', 25 );
function sqseo_auto_links( $content ) {
	$o = sqseo_opts();
	if ( empty( $o['autolink_enable'] ) || is_feed() || is_admin() || empty( $content ) ) {
		return $content;
	}
	// 解析规则：每行「关键词|URL」
	$map = array();
	$lines = preg_split( '/\r\n|\r|\n/', (string) $o['autolink_rules'] );
	foreach ( (array) $lines as $line ) {
		$line = trim( (string) $line );
		if ( '' === $line || false === strpos( $line, '|' ) ) {
			continue;
		}
		list( $kw, $url ) = array_pad( explode( '|', $line, 2 ), 2, '' );
		$kw  = trim( (string) $kw );
		$url = esc_url_raw( trim( (string) $url ) );
		if ( '' !== $kw && '' !== $url && mb_strlen( $kw ) >= 2 && mb_strlen( $kw ) <= 20 ) {
			$map[ $kw ] = $url;
		}
	}
	if ( empty( $map ) ) {
		return $content;
	}

	$max = max( 1, min( 20, (int) $o['autolink_max'] ) );
	// 按 <标签> 切分：只在文本片段里替换，绝不碰 <a> 标签内部和其他标签属性
	$parts  = preg_split( '/(<[^>]*>)/', $content, -1, PREG_SPLIT_DELIM_CAPTURE );
	$in_a   = 0;
	$used   = array();
	$total  = 0;
	foreach ( $parts as $i => $part ) {
		if ( '' === $part ) {
			continue;
		}
		if ( '<' === $part[0] ) {
			if ( preg_match( '/^<a[\s>]/i', $part ) ) {
				$in_a++;
			} elseif ( preg_match( '/^<\/a[\s>]/i', $part ) ) {
				$in_a = max( 0, $in_a - 1 );
			}
			continue;
		}
		if ( $in_a > 0 || $total >= $max ) {
			continue;
		}
		foreach ( $map as $kw => $url ) {
			if ( isset( $used[ $kw ] ) ) {
				continue;
			}
			$pos = mb_stripos( $part, $kw );
			if ( false !== $pos ) {
				$link = '<a href="' . esc_url( $url ) . '">' . esc_html( $kw ) . '</a>';
				$part  = mb_substr( $part, 0, $pos ) . $link . mb_substr( $part, $pos + mb_strlen( $kw ) );
				$used[ $kw ] = true;
				$total++;
				break; // 每个文本片段只替换一个关键词，下一片段继续
			}
		}
		$parts[ $i ] = $part;
	}
	return implode( '', $parts );
}

/** 自动内链设置页 */
function sqseo_page_autolink() {
	$o = sqseo_opts();
	sqseo_page_open(
		'自动内链',
		'<b>这一页管什么：</b>把文章正文里出现的<b>指定关键词</b>自动加上指向站内页面的链接（每篇每词最多 1 次），完善站内链接结构、传递权重。<br>
		<b>怎么填：</b>每行一条，格式 <code>关键词|https://你的域名/目标文章.html</code>，例如 <code>关键词|https://example.com/target-post.html</code>。<br>
		<b>安全边界：</b>只处理正文文本，<b>不会</b>动已有的链接、标签属性和标题结构；每篇最多加 N 条（下方可调），不会把文章变成链接农场。<br>
		<b>建议：</b>填 10～30 个「剧名/演员/专题词 → 对应文章」的映射最有价值；已有手动链接的文章不会被重复添加（同一关键词每篇只链一次）。',
		'autolink_enable,autolink_rules,autolink_max',
		'sqseo-autolink'
	);
	?>
	<table class="form-table" role="presentation">
		<tr>
			<th scope="row">开启自动内链</th>
			<td>
				<label><input type="checkbox" name="sqseo[autolink_enable]" value="1" <?php checked( $o['autolink_enable'], 1 ); ?>> 文章正文输出时自动按下方规则添加内链</label>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-autolink-rules">内链规则</label></th>
			<td>
				<textarea class="large-text code" rows="8" id="sqseo-autolink-rules" name="sqseo[autolink_rules]" placeholder="每行一条：关键词|目标URL
例如：
关键词一|https://example.com/post-a
关键词二|https://example.com/category/b"><?php echo esc_textarea( $o['autolink_rules'] ); ?></textarea>
				<p class="description">关键词 2～20 字；同一关键词在一篇文章里只加 1 次链接。目标 URL 必须带 https:// 完整地址。</p>
			</td>
		</tr>
		<tr>
			<th scope="row"><label for="sqseo-autolink-max">每篇文章最多内链数</label></th>
			<td>
				<input type="text" class="small-text" id="sqseo-autolink-max" name="sqseo[autolink_max]" value="<?php echo esc_attr( $o['autolink_max'] ); ?>">
				<p class="description">建议 3～8。一篇 1000 字的文章放 5 个左右内链比较自然。</p>
			</td>
		</tr>
	</table>
	<?php
	sqseo_page_close();
}

/* =========================================================
 * 九-5、HTML 站点地图（短代码 [sqseo_html_sitemap]）
 * ======================================================= */

add_shortcode( 'sqseo_html_sitemap', 'sqseo_html_sitemap_shortcode' );
function sqseo_html_sitemap_shortcode( $atts ) {
	$o        = sqseo_opts();
	$excluded = (array) $o['excluded_cats'];
	$out      = '<div class="sqseo-sitemap-html">';

	// 页面
	$pages = get_pages( array( 'sort_column' => 'menu_order', 'hierarchical' => 0 ) );
	if ( $pages ) {
		$out .= '<h2>页面</h2><ul>';
		foreach ( $pages as $pg ) {
			$out .= '<li><a href="' . esc_url( get_permalink( $pg ) ) . '">' . esc_html( get_the_title( $pg ) ) . '</a></li>';
		}
		$out .= '</ul>';
	}

	// 分类 + 各分类下文章
	$cats = get_categories( array( 'hide_empty' => true, 'exclude' => $excluded ) );
	foreach ( (array) $cats as $cat ) {
		$posts = get_posts( array(
			'category'    => $cat->term_id,
			'numberposts' => 200,
			'post_status' => 'publish',
			'orderby'     => 'date',
			'order'       => 'DESC',
			'no_found_rows' => true,
		) );
		if ( empty( $posts ) ) {
			continue;
		}
		$out .= '<h2><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( $cat->name ) . '</a></h2><ul>';
		foreach ( $posts as $p ) {
			$out .= '<li><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( get_the_title( $p ) ) . '</a></li>';
		}
		$out .= '</ul>';
	}
	$out .= '</div>';
	return $out;
}

/** HTML 地图页 */
function sqseo_page_htmlmap() {
	$shortcode = '[sqseo_html_sitemap]';
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<h1>SQ SEO Toolkit · HTML 地图</h1>
			<span class="sqseo-version">v<?php echo esc_html( SQSEO_VERSION ); ?></span>
		</div>

		<div class="sqseo-card sqseo-intro">
			<b>这一页管什么：</b>XML sitemap 是给<b>搜索引擎</b>看的；HTML 地图是给<b>访客和内链</b>看的——一个页面列出全站分类和文章，站内互链更密、访客找内容更方便。<br>
			<b>和 Sitemap 页的关系：</b>/sitemap.xml 继续在「Sitemap」页管理，本页的 HTML 地图不重复生成 XML，二者互补。
		</div>

		<h2 class="title">三步开启</h2>
		<div class="sqseo-card sqseo-panel">
			<ol class="sqseo-steps">
				<li>后台 → 页面 → 新建页面，标题填「网站地图」（链接会像 <code>/sitemap</code>）</li>
				<li>正文里只粘贴这一行短代码：<br><code class="sqseo-code"><?php echo esc_html( $shortcode ); ?></code></li>
				<li>发布。页面会自动列出全站<b>页面、分类及每个分类下的文章</b>（与 sitemap 一致，自动遵守「Sitemap 页设置的排除分类」）</li>
			</ol>
			<p class="description">列表随发文自动更新，无需维护。建议再把新建好的页面链接放到底部菜单或页脚，让蜘蛛顺着它多爬一层内链。</p>
		</div>

		<h2 class="title">说明</h2>
		<table class="widefat striped sqseo-status-table">
			<tbody>
				<tr><td class="sqseo-col-name"><b>数据来源</b></td><td>与 /sitemap.xml 完全一致：已发布文章 + 分类，自动排除「Sitemap」页勾选的排除分类</td></tr>
				<tr><td><b>每分类文章上限</b></td><td>200 篇（超出请靠分类页承担，避免单页过重）</td></tr>
				<tr><td><b>当前排除的分类</b></td><td><?php
					$ex = (array) sqseo_opts()['excluded_cats'];
					if ( empty( $ex ) ) {
						echo '无';
					} else {
						$names = array();
						foreach ( $ex as $id ) {
							$c = get_category( $id );
							if ( $c && ! is_wp_error( $c ) ) {
								$names[] = $c->name;
							}
						}
						echo esc_html( implode( '、', $names ) );
					}
				?>（在「Sitemap」页修改）</td></tr>
			</tbody>
		</table>
	</div>
	<?php
}

/* =========================================================
 * 九-6、AI 摘要「要点速览」（GEO：给人和 AI 的 TL;DR）
 * ======================================================= */

add_filter( 'the_content', 'sqseo_tldr_block', 5 );
function sqseo_tldr_block( $content ) {
	$o = sqseo_opts();
	if ( empty( $o['tldr_enable'] ) || is_feed() || is_admin() || ! is_singular( 'post' ) || ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}
	$tldr = trim( (string) get_post_meta( get_the_ID(), '_sqseo_tldr', true ) );
	if ( '' === $tldr ) {
		return $content;
	}
	$items = '';
	foreach ( preg_split( '/\r\n|\r|\n/', $tldr ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$items .= '<li>' . esc_html( $line ) . '</li>';
		}
	}
	if ( '' === $items ) {
		return $content;
	}
	// 样式只输出一次；配色用中性半透明，深浅色主题都不突兀
	static $css_done = false;
	$css = '';
	if ( ! $css_done ) {
		$css_done = true;
		$css = '<style>.sqseo-tldr{background:rgba(127,127,127,.08);border:1px solid rgba(127,127,127,.18);border-radius:12px;padding:14px 20px;margin:0 0 24px;font-size:.95em;line-height:1.8}.sqseo-tldr strong{display:block;margin-bottom:6px}.sqseo-tldr ul{margin:0;padding-left:1.2em}.sqseo-tldr li{margin:2px 0}</style>';
	}
	return $css . '<div class="sqseo-tldr"><strong>📌 要点速览</strong><ul>' . $items . '</ul></div>' . $content;
}

/* =========================================================
 * 十、文章编辑框：自定义标题/关键词/描述 + FAQ 问答
 * ======================================================= */

add_action( 'add_meta_boxes', 'sqseo_add_metabox' );
function sqseo_add_metabox() {
	add_meta_box( 'sqseo_post_box', 'SQ SEO', 'sqseo_metabox_html', 'post', 'normal', 'high' );
}

function sqseo_metabox_html( $post ) {
	wp_nonce_field( 'sqseo_metabox_save', 'sqseo_metabox_nonce' );
	$t   = get_post_meta( $post->ID, '_sqseo_title', true );
	$k   = get_post_meta( $post->ID, '_sqseo_keywords', true );
	$d   = get_post_meta( $post->ID, '_sqseo_desc', true );
	$faq = get_post_meta( $post->ID, '_sqseo_faq', true );
	?>
	<div class="sqseo-mb">
		<label for="sqseo-mb-title">自定义标题（留空 = 用文章标题）</label>
		<input type="text" id="sqseo-mb-title" name="sqseo_mb[title]" value="<?php echo esc_attr( $t ); ?>" maxlength="80" placeholder="例如：文章标题 - 一句话说明">
		<p class="desc">建议 ≤ 25 个汉字，优先写「主题 + 关键信息」这类搜索词。</p>

		<label for="sqseo-mb-kw">自定义关键词（留空 = 自动使用文章标签）</label>
		<input type="text" id="sqseo-mb-kw" name="sqseo_mb[keywords]" value="<?php echo esc_attr( $k ); ?>" placeholder="逗号分隔，3～5 个">

		<label for="sqseo-mb-desc">自定义描述</label>
		<textarea id="sqseo-mb-desc" name="sqseo_mb[desc]" rows="3" maxlength="300" placeholder="80～140 字，把最重要的信息放前 60 字"><?php echo esc_textarea( $d ); ?></textarea>
		<p class="desc"><b>仅在设置页开启「接管 description」后输出</b>（避免与主题输出的描述重复）。不开启时，也可以直接用 WordPress 原生「摘要」框——主题会自动把它作为页面描述。</p>

		<label for="sqseo-mb-faq">你可能想问 FAQ（每行一组，格式：问题|答案）</label>
		<textarea id="sqseo-mb-faq" name="sqseo_mb[faq]" rows="5" placeholder="这个工具怎么用？|按页面提示填写即可，两分钟能跑完。&#10;支持免费使用吗？|完全免费，无需注册。"><?php echo esc_textarea( $faq ); ?></textarea>
		<p class="desc">全角「｜」或半角「|」分隔均可。填写后会在文章末尾自动渲染可见的问答区块，并输出 FAQPage 结构化数据——这是被 AI 搜索引用最有效的格式。建议每篇 2～5 组，答案给确切信息（数字、日期、人名）。</p>

		<label for="sqseo-mb-tldr">AI 摘要·要点速览（每行一条，2～5 条）</label>
		<textarea id="sqseo-mb-tldr" name="sqseo_mb[tldr]" rows="4" placeholder="一句话说清这篇文章讲了什么。&#10;补上最关键的数据或结论。&#10;再写一条读者最关心的问题。"><?php echo esc_textarea( get_post_meta( $post->ID, '_sqseo_tldr', true ) ); ?></textarea>
		<p class="desc"><b>GEO 核心字段：</b>① 文章开头渲染可见的「要点速览」区块（AI 抓取时最优先读到的内容）；② 作为 Article 结构化数据的 description 和 speakable 朗读源；③ 优先写进 /llms-full.txt。要点 = 确切的事实（时间/集数/人名/平台），别写空话。</p>
	</div>
	<?php
}

add_action( 'save_post_post', 'sqseo_save_metabox', 10, 2 );
function sqseo_save_metabox( $post_id, $post ) {
	if ( ! isset( $_POST['sqseo_metabox_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['sqseo_metabox_nonce'] ), 'sqseo_metabox_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$mb = isset( $_POST['sqseo_mb'] ) ? (array) wp_unslash( $_POST['sqseo_mb'] ) : array();

	$fields = array(
		'title'    => '_sqseo_title',
		'keywords' => '_sqseo_keywords',
		'desc'     => '_sqseo_desc',
		'faq'      => '_sqseo_faq',
		'tldr'     => '_sqseo_tldr',
	);
	foreach ( $fields as $key => $meta_key ) {
		$val = isset( $mb[ $key ] ) ? sanitize_textarea_field( $mb[ $key ] ) : '';
		if ( '' === $val ) {
			delete_post_meta( $post_id, $meta_key );
		} else {
			update_post_meta( $post_id, $meta_key, $val );
		}
	}
}

/* =========================================================
 * 十一、薄页 noindex
 * ======================================================= */

add_action( 'wp_head', 'sqseo_noindex_thin', 1 );
function sqseo_noindex_thin() {
	$o = sqseo_opts();
	if ( ! $o['noindex_thin'] ) {
		return;
	}
	if ( is_tag() || is_author() || is_search() || is_date() ) {
		echo '<meta name="robots" content="noindex,follow">' . "\n";
	}
}

/* =========================================================
 * 十二、FAQ：前台渲染 + FAQPage Schema
 * ======================================================= */

/** 解析 FAQ 文本：每行「问题|答案」，兼容全角竖线 */
function sqseo_parse_faq( $raw ) {
	$out = array();
	if ( '' === trim( (string) $raw ) ) {
		return $out;
	}
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$pos = mb_strpos( $line, '|' );
		if ( false === $pos ) {
			$pos = mb_strpos( $line, '｜' );
		}
		if ( false === $pos || 0 === $pos ) {
			continue;
		}
		$sep    = mb_substr( $line, $pos, 1 );
		$parts  = explode( $sep, $line, 2 );
		$q      = trim( $parts[0] );
		$a      = trim( isset( $parts[1] ) ? $parts[1] : '' );
		if ( '' !== $q && '' !== $a ) {
			$out[] = array( 'q' => $q, 'a' => $a );
		}
	}
	return $out;
}

/** 文章末尾渲染可见的 FAQ 区块（手风琴样式；details 内容在 DOM 中始终可见，符合 Google FAQ 指南） */
add_filter( 'the_content', 'sqseo_faq_block', 25 );
function sqseo_faq_block( $content ) {
	if ( ! is_singular( 'post' ) || is_feed() || in_the_loop() === false ) {
		return $content;
	}
	$items = sqseo_parse_faq( get_post_meta( get_the_ID(), '_sqseo_faq', true ) );
	if ( ! $items ) {
		return $content;
	}

	// 样式只注入一次，全部前缀 .sqseo-faq；颜色用中性半透明 + inherit，
	// 浅色/深色主题下都不违和，不污染主题其他元素
	static $css_done = false;
	$css = '';
	if ( ! $css_done ) {
		$css_done = true;
		$css = '<style>'
			. '.sqseo-faq{margin:44px 0 10px;padding:24px 26px 8px;border-radius:16px;'
			. 'background:linear-gradient(180deg,rgba(59,130,246,.06),rgba(59,130,246,.015));'
			. 'border:1px solid rgba(100,130,180,.18)}'
			. '.sqseo-faq__head{display:flex;align-items:center;gap:10px}'
			. '.sqseo-faq__bar{flex:0 0 auto;width:5px;height:20px;border-radius:3px;background:linear-gradient(180deg,#60a5fa,#2563eb)}'
			. '.sqseo-faq__title{margin:0;font-size:1.18em;font-weight:700;color:inherit}'
			. '.sqseo-faq__sub{margin:6px 0 10px 15px;font-size:.82em;opacity:.55}'
			. '.sqseo-faq details{border-top:1px dashed rgba(120,140,170,.28)}'
			. '.sqseo-faq details:first-of-type{border-top:0}'
			. '.sqseo-faq summary{display:flex;align-items:flex-start;gap:10px;padding:12px 2px;cursor:pointer;list-style:none;font-weight:600;font-size:1em;line-height:1.6;color:inherit;border-radius:8px}'
			. '.sqseo-faq summary::-webkit-details-marker{display:none}'
			. '.sqseo-faq summary:hover{color:#2563eb}'
			. '.sqseo-faq summary:focus-visible{outline:2px solid #3b82f6;outline-offset:2px}'
			. '.sqseo-faq__q{flex:0 0 auto;width:21px;height:21px;margin-top:2px;border-radius:7px;background:linear-gradient(135deg,#60a5fa,#2563eb);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center}'
			. '.sqseo-faq__txt{flex:1;min-width:0}'
			. '.sqseo-faq__arrow{flex:0 0 auto;margin-top:5px;width:0;height:0;border-left:5px solid transparent;border-right:5px solid transparent;border-top:6px solid currentColor;opacity:.4;transition:transform .2s ease}'
			. '.sqseo-faq details[open] summary{color:#2563eb}'
			. '.sqseo-faq details[open] .sqseo-faq__arrow{transform:rotate(180deg)}'
			. '.sqseo-faq__a{margin:0;padding:0 2px 14px 31px;font-size:.95em;line-height:1.85;opacity:.78}'
			. '@media (prefers-reduced-motion:reduce){.sqseo-faq__arrow{transition:none}}'
			. '@media (max-width:600px){.sqseo-faq{padding:18px 16px 4px;margin:36px 0 6px}.sqseo-faq__a{padding-left:0}}'
			. '</style>';
	}

	$html  = '<div class="sqseo-faq">';
	$html .= $css;
	$html .= '<div class="sqseo-faq__head"><span class="sqseo-faq__bar" aria-hidden="true"></span><h2 class="sqseo-faq__title">你可能想问</h2></div>';
	$html .= '<p class="sqseo-faq__sub">关于本篇的快问快答，点击展开</p>';
	foreach ( $items as $i => $it ) {
		// 第一组默认展开，保证访客与搜索引擎落地即见内容
		$html .= '<details' . ( 0 === $i ? ' open' : '' ) . '>';
		$html .= '<summary><span class="sqseo-faq__q" aria-hidden="true">Q</span><span class="sqseo-faq__txt">' . esc_html( $it['q'] ) . '</span><span class="sqseo-faq__arrow" aria-hidden="true"></span></summary>';
		$html .= '<p class="sqseo-faq__a">' . esc_html( $it['a'] ) . '</p>';
		$html .= '</details>';
	}
	$html .= '</div>';
	return $content . $html;
}

/** FAQPage Schema 并入 @graph（在 sqseo_head_jsonld 内部调用） */
function sqseo_faq_schema_items( $items ) {
	$main = array();
	foreach ( $items as $it ) {
		$main[] = array(
			'@type'          => 'Question',
			'name'           => $it['q'],
			'acceptedAnswer' => array(
				'@type' => 'Answer',
				'text'  => $it['a'],
			),
		);
	}
	return array(
		'@type'      => 'FAQPage',
		'mainEntity' => $main,
	);
}

/* =========================================================
 * 十三、301 重定向管理器
 * ======================================================= */

/** 读取重定向规则：array( '/old-path' => array('t' => 目标, 'n' => 命中次数) ) */
function sqseo_redirects() {
	$r = get_option( 'sqseo_redirects', array() );
	return is_array( $r ) ? $r : array();
}

/** 来源归一化：支持粘贴完整 URL，只取路径；统一前导斜杠、去尾斜杠；首页与空值无效 */
function sqseo_redirect_norm_from( $raw ) {
	$raw  = trim( (string) $raw );
	$path = ( '' === $raw ) ? '' : (string) wp_parse_url( $raw, PHP_URL_PATH );
	if ( '' === $path || null === $path ) {
		return '';
	}
	$path = '/' . trim( $path, '/' );
	if ( '/' === $path ) {
		return ''; // 首页不允许作为来源
	}
	return $path;
}

/** 前台：命中规则立即 301（priority 1，赶在 404 记录之前，重定向后的路径不再计入死链） */
add_action( 'template_redirect', 'sqseo_maybe_redirect', 1 );
function sqseo_maybe_redirect() {
	$rules = sqseo_redirects();
	if ( empty( $rules ) || is_admin() ) {
		return;
	}
	$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) $_SERVER['REQUEST_URI'] : '';
	$path = (string) wp_parse_url( $uri, PHP_URL_PATH );
	$norm = '/' . trim( $path, '/' );
	if ( isset( $rules[ $norm ] ) ) {
		$to     = (string) $rules[ $norm ]['t'];
		$target = ( 0 === strpos( $to, '/' ) ) ? home_url( user_trailingslashit( $to ) ) : $to;
		// 命中计数：死链路径属低频访问，直接写库无压力
		$rules[ $norm ]['n'] = ( isset( $rules[ $norm ]['n'] ) ? (int) $rules[ $norm ]['n'] : 0 ) + 1;
		update_option( 'sqseo_redirects', $rules, false );
		wp_redirect( esc_url_raw( $target ), 301 );
		exit;
	}
}

/** 新增/覆盖一条重定向（admin_post 处理器） */
add_action( 'admin_post_sqseo_redirect_add', 'sqseo_redirect_add_handler' );
function sqseo_redirect_add_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_redirect_add' );
	$from = sqseo_redirect_norm_from( isset( $_POST['sqseo_redirect_from'] ) ? wp_unslash( $_POST['sqseo_redirect_from'] ) : '' );
	$to   = trim( (string) ( isset( $_POST['sqseo_redirect_to'] ) ? wp_unslash( $_POST['sqseo_redirect_to'] ) : '' ) );
	if ( '' !== $from && '' !== $to ) {
		if ( 0 === strpos( $to, '/' ) ) {
			$to = '/' . ltrim( $to, '/' ); // 站内路径
		} else {
			$to = esc_url_raw( $to );      // 完整 URL
		}
		if ( '' !== $to && '/' !== $to ) {
			$rules = sqseo_redirects();
			$rules[ $from ] = array( 't' => $to, 'n' => 0 );
			if ( count( $rules ) > 200 ) {
				$rules = array_slice( $rules, 0, 200, true );
			}
			update_option( 'sqseo_redirects', $rules, false );
		}
	}
	$back = ( isset( $_POST['sqseo_back'] ) && 'crawl' === $_POST['sqseo_back'] ) ? 'sqseo-crawl-log' : 'sqseo-redirect';
	wp_safe_redirect( add_query_arg( array( 'page' => $back, 'sqseo_rd_added' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 删除一条重定向（admin_post 处理器，GET + nonce） */
add_action( 'admin_post_sqseo_redirect_del', 'sqseo_redirect_del_handler' );
function sqseo_redirect_del_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_redirect_del' );
	$key = isset( $_GET['k'] ) ? (string) wp_unslash( $_GET['k'] ) : '';
	if ( '' !== $key ) {
		$rules = sqseo_redirects();
		unset( $rules[ $key ] );
		update_option( 'sqseo_redirects', $rules, false );
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'sqseo-redirect', 'sqseo_rd_del' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 301 重定向页 */
function sqseo_page_redirect() {
	$rules = sqseo_redirects();
	$added = ! empty( $_GET['sqseo_rd_added'] );
	$del   = ! empty( $_GET['sqseo_rd_del'] );
	$prefill_from = isset( $_GET['from'] ) ? (string) wp_unslash( $_GET['from'] ) : '';
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>301 重定向</h1>
			<span class="sqseo-version">SQ SEO Toolkit</span>
		</div>

		<?php if ( $added ) : ?>
			<?php sqseo_toast( '<b>重定向已添加。</b>旧地址访问时会自动 301 跳转到目标地址。' ); ?>
		<?php endif; ?>
		<?php if ( $del ) : ?>
			<?php sqseo_toast( '<b>已删除该重定向。</b>' ); ?>
		<?php endif; ?>

		<div class="sqseo-card sqseo-intro">
			<b>这一页管什么：</b>把失效的旧地址 <b>301 永久跳转</b>到有效页面——蜘蛛带来的权重不流失，访客也看不到 404。<br>
			<b>最佳工作流：</b>「抓取日志」发现 404 → 在 404 表格右侧填目标地址点 <b>301 →</b> 一键加规则 → 回本页管理。<br>
			<b>跳向哪里：</b>内容还在就跳新地址；已彻底删除就跳对应分类页（比跳首页体验好）。
		</div>

		<div class="sqseo-card sqseo-panel">
			<h2 class="title">添加重定向</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'sqseo_redirect_add' ); ?>
				<input type="hidden" name="action" value="sqseo_redirect_add">
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row"><label for="sqseo-rd-from">旧地址（来源）</label></th>
						<td>
							<input type="text" class="regular-text code" id="sqseo-rd-from" name="sqseo_redirect_from" value="<?php echo esc_attr( $prefill_from ); ?>" placeholder="/2026/01/old-post.html">
							<p class="description">只填路径即可，<b>粘贴完整 URL 也行</b>（自动取路径、忽略问号后的参数）。</p>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="sqseo-rd-to">新地址（目标）</label></th>
						<td>
							<input type="text" class="regular-text code" id="sqseo-rd-to" name="sqseo_redirect_to" placeholder="https://example.com/new-post.html">
							<p class="description">完整 URL 或站内路径（如 <code>/category/juji</code>）均可。</p>
						</td>
					</tr>
				</table>
				<p class="sqseo-actions"><?php submit_button( '添加重定向', 'primary large', 'submit', false ); ?></p>
			</form>
		</div>

		<h2 class="title">已有规则（<?php echo count( $rules ); ?> 条）</h2>
		<?php if ( empty( $rules ) ) : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">↪️</span>
				<p class="sqseo-empty-title">还没有重定向规则</p>
				<p class="sqseo-empty-desc">去「抓取日志」的 404 死链表里一键添加，或在上方表单手动添加。</p>
			</div>
		<?php else : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>旧地址（来源路径）</th>
						<th>新地址（目标）</th>
						<th class="sqseo-col-op">命中次数</th>
						<th class="sqseo-col-op">操作</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $rules as $from => $rule ) : ?>
					<tr>
						<td><code class="sqseo-code"><?php echo esc_html( $from ); ?></code></td>
						<td><code class="sqseo-code"><?php echo esc_html( $rule['t'] ); ?></code></td>
						<td><?php echo (int) ( isset( $rule['n'] ) ? $rule['n'] : 0 ); ?></td>
						<td><a class="button button-small sqseo-btn-danger" data-confirm="确定删除这条重定向吗？删除后旧地址将恢复 404。" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'sqseo_redirect_del', 'k' => rawurlencode( $from ) ), admin_url( 'admin-post.php' ) ), 'sqseo_redirect_del' ) ); ?>">删除</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">命中次数 = 添加规则后被访问的次数，用来看这条跳转还有没有人走。</p>
		<?php endif; ?>
	</div>
	<?php
}

/* =========================================================
 * 十四、内容体检：内容新鲜度 + 死链扫描
 * ======================================================= */

/** 扫描文章内死链（admin_post 处理器）：提取最近文章正文里的链接，逐个探测 */
add_action( 'admin_post_sqseo_scan_links', 'sqseo_scan_links_handler' );
function sqseo_scan_links_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_scan_links' );
	if ( function_exists( 'set_time_limit' ) ) {
		@set_time_limit( 300 );
	}

	// 1. 取最近 60 篇已发布文章，提取正文里的所有链接（同一链接最多记 5 篇出处）
	$posts = get_posts( array(
		'numberposts'  => 60,
		'post_status'  => 'publish',
		'orderby'      => 'modified',
		'order'        => 'DESC',
		'no_found_rows' => true,
	) );
	$map = array();
	foreach ( $posts as $p ) {
		$content = (string) $p->post_content;
		if ( '' === $content ) {
			continue;
		}
		if ( preg_match_all( '/href=["\']([^"\']+)["\']/i', $content, $m ) ) {
			foreach ( $m[1] as $raw_url ) {
				$u = esc_url_raw( trim( html_entity_decode( $raw_url, ENT_QUOTES ) ) );
				if ( '' === $u || 0 !== strpos( $u, 'http' ) ) {
					continue; // mailto: / tel: / javascript: 等
				}
				if ( ! isset( $map[ $u ] ) ) {
					$map[ $u ] = array();
				}
				if ( count( $map[ $u ] ) < 5 ) {
					$map[ $u ][] = $p->ID;
				}
			}
		}
	}
	$map = array_slice( $map, 0, 150, true ); // 单次最多探测 150 个链接

	// 2. 逐个探测：先 HEAD，被拒（405/403 等）或失败时补一次 GET
	$bad     = array();
	$checked = 0;
	$ua      = 'Mozilla/5.0 (compatible; SQSEO-LinkCheck/1.0; +' . home_url( '/' ) . ')';
	foreach ( $map as $u => $ids ) {
		$checked++;
		$res  = wp_remote_head( $u, array( 'timeout' => 6, 'sslverify' => false, 'redirection' => 3, 'user-agent' => $ua ) );
		$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		if ( 0 === $code || in_array( $code, array( 403, 405, 501 ), true ) ) {
			$res  = wp_remote_get( $u, array( 'timeout' => 8, 'sslverify' => false, 'redirection' => 3, 'user-agent' => $ua ) );
			$code = is_wp_error( $res ) ? 0 : (int) wp_remote_retrieve_response_code( $res );
		}
		if ( is_wp_error( $res ) || $code >= 400 ) {
			$bad[] = array( 'u' => $u, 'c' => $code, 'p' => $ids );
		}
	}
	update_option( 'sqseo_link_report', array( 't' => time(), 'n' => $checked, 'total' => count( $posts ), 'bad' => $bad ), false );

	wp_safe_redirect( add_query_arg( array( 'page' => 'sqseo-content', 'sqseo_scanned' => 1 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 内容体检页 */
function sqseo_page_content_check() {
	$o = sqseo_opts();
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>内容体检</h1>
			<span class="sqseo-version">SQ SEO Toolkit</span>
		</div>

		<?php if ( ! empty( $_GET['sqseo_updated'] ) ) : ?>
			<?php sqseo_toast( '<b>已保存。</b>' ); ?>
		<?php endif; ?>
		<?php if ( ! empty( $_GET['sqseo_scanned'] ) ) : ?>
			<?php sqseo_toast( '<b>死链扫描完成。</b>结果见下方报告。' ); ?>
		<?php endif; ?>

		<div class="sqseo-card sqseo-intro">
			<b>这一页管什么：</b>① <b>内容新鲜度</b>——列出太久没更新的老文章（搜索引擎和 AI 引擎都偏爱「活着的」内容，老文翻新是最省力的提权手段）；② <b>死链扫描</b>——批量检查文章正文里的内链/外链是否还打得开，不用等蜘蛛报 404 就先自查。<br>
			<b>建议节奏：</b>每月体检一次：先把超期文章里流量最好的几篇翻新，再扫一遍死链把打不开的修掉或加 301。
		</div>

		<form method="post" action="">
			<?php wp_nonce_field( 'sqseo_save', 'sqseo_nonce' ); ?>
			<input type="hidden" name="sqseo_keys" value="fresh_months">
			<input type="hidden" name="sqseo_page" value="sqseo-content">
			<h2 class="title">新鲜度阈值</h2>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="sqseo-fresh-months">待刷新判定</label></th>
					<td>
						<input type="text" class="small-text" id="sqseo-fresh-months" name="sqseo[fresh_months]" value="<?php echo esc_attr( $o['fresh_months'] ); ?>"> 个月未更新
						<p class="description">超过这个月数没更新的文章会出现在下方「待刷新」清单里。默认 12 个月。</p>
					</td>
				</tr>
			</table>
			<p class="sqseo-actions"><?php submit_button( '保存本页设置', 'primary large', 'sqseo_save', false ); ?></p>
		</form>

		<?php
		// ---------- 待刷新文章 ----------
		$months = max( 1, min( 60, (int) $o['fresh_months'] ) );
		$cutoff = gmdate( 'Y-m-d H:i:s', strtotime( '-' . $months . ' months' ) );
		$stale  = new WP_Query( array(
			'post_type'      => 'post',
			'post_status'    => 'publish',
			'posts_per_page' => 50,
			'orderby'        => 'modified',
			'order'          => 'ASC',
			'no_found_rows'  => false,
			'date_query'     => array( array( 'column' => 'post_modified_gmt', 'before' => $cutoff ) ),
		) );
		?>
		<h2 class="title">待刷新文章（<?php echo (int) $stale->found_posts; ?> 篇超过 <?php echo (int) $months; ?> 个月未更新，列出最早的 50 篇）</h2>
		<?php if ( $stale->have_posts() ) : ?>
			<table class="widefat striped">
				<thead>
					<tr>
						<th>文章</th>
						<th>分类</th>
						<th class="sqseo-col-time">最后更新</th>
						<th class="sqseo-col-op">操作</th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $stale->posts as $sp ) : ?>
					<tr>
						<td><a href="<?php echo esc_url( (string) get_edit_post_link( $sp->ID ) ); ?>"><?php echo esc_html( get_the_title( $sp ) ); ?></a></td>
						<td><?php
							$cats = get_the_category( $sp->ID );
							echo esc_html( $cats ? implode( '、', wp_list_pluck( $cats, 'name' ) ) : '—' );
						?></td>
						<td class="sqseo-mono"><?php echo esc_html( get_the_modified_date( 'Y-m-d', $sp ) ); ?></td>
						<td><a class="button button-small" href="<?php echo esc_url( (string) get_edit_post_link( $sp->ID ) ); ?>">去更新</a></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
			<p class="description">翻新建议：补充新进展（更新时间/新集数/新价格）、修正失效信息、追加 FAQ 或要点速览，改完到「链接推送」页推一次，收录更快。</p>
		<?php else : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">🌱</span>
				<p class="sqseo-empty-title">内容都很新鲜</p>
				<p class="sqseo-empty-desc">没有超过 <?php echo (int) $months; ?> 个月未更新的文章（或站点文章还不多）。</p>
			</div>
		<?php endif;
		wp_reset_postdata();

		// ---------- 死链扫描 ----------
		$report = get_option( 'sqseo_link_report', array() );
		?>
		<h2 class="title">死链扫描</h2>
		<div class="sqseo-card sqseo-panel">
			<p><a class="button button-primary" data-confirm="开始扫描最近 60 篇文章正文里的链接？探测外链可能需要十几秒到一分钟，请勿关闭页面。" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'sqseo_scan_links' ), admin_url( 'admin-post.php' ) ), 'sqseo_scan_links' ) ); ?>">🔍 开始扫描</a></p>
			<p class="description">扫描范围：最近更新的 60 篇文章正文里的全部链接（内链 + 外链，单次最多 150 个）。状态码 403 / 999 不一定是真死链——部分网站会拦截检测器，可人工在浏览器开一次确认。</p>
		</div>
		<?php if ( ! empty( $report['t'] ) ) : ?>
			<?php $bad = ( isset( $report['bad'] ) && is_array( $report['bad'] ) ) ? $report['bad'] : array(); ?>
			<p class="description">上次扫描：<?php echo esc_html( date_i18n( 'Y-m-d H:i', (int) $report['t'] ) ); ?>，检查了 <?php echo (int) ( isset( $report['n'] ) ? $report['n'] : 0 ); ?> 个链接（来自 <?php echo (int) ( isset( $report['total'] ) ? $report['total'] : 0 ); ?> 篇文章），发现 <b><?php echo count( $bad ); ?></b> 个可疑链接。</p>
			<?php if ( empty( $bad ) ) : ?>
				<div class="sqseo-empty">
					<span class="sqseo-empty-icon" aria-hidden="true">✅</span>
					<p class="sqseo-empty-title">全部链接正常</p>
					<p class="sqseo-empty-desc">最近一次扫描没有发现问题链接。</p>
				</div>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th>问题链接</th>
							<th class="sqseo-col-op">状态</th>
							<th>出现在文章（修复入口）</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $bad as $b ) : ?>
						<tr>
							<td><code class="sqseo-code"><?php echo esc_html( $b['u'] ); ?></code></td>
							<td><span class="sqseo-badge sqseo-badge--err"><?php echo esc_html( (int) $b['c'] > 0 ? $b['c'] : '无法连接' ); ?></span></td>
							<td><?php
								$links = array();
								foreach ( (array) $b['p'] as $pid ) {
									$links[] = '<a href="' . esc_url( (string) get_edit_post_link( $pid ) ) . '">' . esc_html( get_the_title( $pid ) ) . '</a>';
								}
								echo implode( '、', $links ); // 链接与标题均来自本站数据库内部数据
							?></td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
				<p class="description">修复方式：① 站内链接 → 进文章改正；② 外链失效 → 换可用的同类页面或删掉；③ 本站旧地址 → 到「301 重定向」加跳转规则。</p>
			<?php endif; ?>
		<?php else : ?>
			<div class="sqseo-empty">
				<span class="sqseo-empty-icon" aria-hidden="true">🔗</span>
				<p class="sqseo-empty-title">还没扫描过</p>
				<p class="sqseo-empty-desc">点上方「开始扫描」，插件会逐个检查文章正文里的链接是否打得开。</p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}

/* =========================================================
 * 十五、备份 / 迁移：导出导入全部插件配置
 * ======================================================= */

add_action( 'admin_post_sqseo_export', 'sqseo_export_handler' );
function sqseo_export_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_export' );
	$payload = array(
		'plugin'    => 'sq-seo-toolkit',
		'version'   => SQSEO_VERSION,
		'exported'  => gmdate( 'c' ),
		'site'      => home_url(),
		'options'   => sqseo_opts(),
		'redirects' => sqseo_redirects(),
	);
	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=sqseo-backup-' . gmdate( 'Ymd-His' ) . '.json' );
	echo wp_json_encode( $payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	exit;
}

add_action( 'admin_post_sqseo_import', 'sqseo_import_handler' );
function sqseo_import_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( '没有权限' );
	}
	check_admin_referer( 'sqseo_import' );
	$ok = false;
	if ( isset( $_FILES['sqseo_import_file']['tmp_name'] ) && is_uploaded_file( $_FILES['sqseo_import_file']['tmp_name'] ) ) {
		$data = json_decode( (string) file_get_contents( $_FILES['sqseo_import_file']['tmp_name'] ), true );
		if ( is_array( $data ) && isset( $data['plugin'] ) && 'sq-seo-toolkit' === $data['plugin'] ) {
			// 合并选项：只接受本插件已知键，按类型白名单逐项清洗
			if ( isset( $data['options'] ) && is_array( $data['options'] ) ) {
				$def   = sqseo_defaults();
				$types = sqseo_field_types();
				$opts  = get_option( 'sqseo_options', array() );
				$opts  = is_array( $opts ) ? $opts : array();
				foreach ( $data['options'] as $key => $val ) {
					if ( ! is_string( $key ) || ! array_key_exists( $key, $def ) ) {
						continue;
					}
					if ( in_array( $key, $types['switch'], true ) ) {
						$opts[ $key ] = empty( $val ) ? 0 : 1;
					} elseif ( in_array( $key, $types['textarea'], true ) ) {
						$opts[ $key ] = sanitize_textarea_field( (string) $val );
					} elseif ( in_array( $key, $types['url'], true ) ) {
						$opts[ $key ] = esc_url_raw( (string) $val );
					} elseif ( in_array( $key, $types['cats'], true ) ) {
						$opts[ $key ] = array_values( array_filter( array_map( 'intval', (array) $val ) ) );
					} elseif ( in_array( $key, $types['verify'], true ) ) {
						$opts[ $key ] = sqseo_sanitize_verify( $val );
					} elseif ( in_array( $key, $types['text'], true ) ) {
						$opts[ $key ] = sanitize_text_field( (string) $val );
					}
				}
				update_option( 'sqseo_options', $opts );
			}
			// 重定向规则
			if ( isset( $data['redirects'] ) && is_array( $data['redirects'] ) ) {
				$rules = array();
				foreach ( $data['redirects'] as $from => $rule ) {
					$f = sqseo_redirect_norm_from( $from );
					$t = ( is_array( $rule ) && isset( $rule['t'] ) ) ? trim( (string) $rule['t'] ) : '';
					if ( '' === $f || '' === $t ) {
						continue;
					}
					if ( 0 === strpos( $t, '/' ) ) {
						$t = '/' . ltrim( $t, '/' );
					} else {
						$t = esc_url_raw( $t );
					}
					if ( '' !== $t && '/' !== $t ) {
						$rules[ $f ] = array( 't' => $t, 'n' => ( is_array( $rule ) && isset( $rule['n'] ) ) ? (int) $rule['n'] : 0 );
					}
					if ( count( $rules ) >= 200 ) {
						break;
					}
				}
				update_option( 'sqseo_redirects', $rules );
			}
			$ok = true;
		}
	}
	wp_safe_redirect( add_query_arg( array( 'page' => 'sqseo-backup', 'sqseo_imported' => $ok ? 1 : 0 ), admin_url( 'admin.php' ) ) );
	exit;
}

/** 备份 / 迁移页 */
function sqseo_page_backup() {
	?>
	<div class="wrap sqseo-admin">
		<div class="sqseo-header">
			<?php echo sqseo_logo_svg(); ?>
			<h1>备份 / 迁移</h1>
			<span class="sqseo-version">SQ SEO Toolkit</span>
		</div>

		<?php if ( isset( $_GET['sqseo_imported'] ) ) : ?>
			<?php if ( '1' === $_GET['sqseo_imported'] ) : ?>
				<?php sqseo_toast( '<b>导入成功。</b>所有配置已恢复，去各功能页确认一下即可。' ); ?>
			<?php else : ?>
				<?php sqseo_toast( '<b style="color:#b32d2e;">导入失败。</b>请确认选择的是本插件导出的 .json 备份文件。' ); ?>
			<?php endif; ?>
		<?php endif; ?>

		<div class="sqseo-card sqseo-intro">
			<b>这一页管什么：</b>把插件<b>全部配置</b>（各设置页选项 + 301 重定向规则）打包成一个 JSON 文件下载，或在新环境一键恢复。<br>
			<b>典型场景：</b>换域名 / 换服务器 / 搭测试站，不用一项项重新点。<br>
			<b>不包含：</b>抓取日志、推送日志、扫描报告（这些是运行数据，迁移没意义）。
		</div>

		<div class="sqseo-card sqseo-panel">
			<h2 class="title">① 导出备份</h2>
			<p><a class="button button-primary" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'sqseo_export' ), admin_url( 'admin-post.php' ) ), 'sqseo_export' ) ); ?>">⬇️ 下载备份文件（.json）</a></p>
			<p class="description">立即下载，包含全部设置与重定向规则。建议每次大改配置后导出一份留底。</p>
		</div>

		<div class="sqseo-card sqseo-panel">
			<h2 class="title">② 导入恢复</h2>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" enctype="multipart/form-data">
				<?php wp_nonce_field( 'sqseo_import' ); ?>
				<input type="hidden" name="action" value="sqseo_import">
				<p><input type="file" name="sqseo_import_file" accept=".json,application/json" required></p>
				<p><button type="submit" class="button sqseo-btn-danger" data-confirm="导入会覆盖当前插件的全部设置与重定向规则（未导出备份的话无法找回）。确定继续吗？">上传并恢复</button></p>
				<p class="description">只恢复备份文件里包含的项，没出现的设置保持现状。导入前建议先导出一份当前配置留底。</p>
			</form>
		</div>
	</div>
	<?php
}

/* =========================================================
 * 十六、文章列表 SEO 状态列
 * ======================================================= */

add_filter( 'manage_post_posts_columns', 'sqseo_posts_column' );
function sqseo_posts_column( $cols ) {
	$cols['sqseo_check'] = 'SEO';
	return $cols;
}

add_action( 'manage_post_posts_custom_column', 'sqseo_posts_column_content', 10, 2 );
function sqseo_posts_column_content( $col, $post_id ) {
	if ( 'sqseo_check' !== $col ) {
		return;
	}
	$custom_title = (string) get_post_meta( $post_id, '_sqseo_title', true );
	$desc         = trim( (string) get_post_meta( $post_id, '_sqseo_desc', true ) );
	$title        = '' !== $custom_title ? $custom_title : (string) get_the_title( $post_id );
	$tlen         = function_exists( 'mb_strlen' ) ? mb_strlen( $title, 'UTF-8' ) : strlen( $title );

	$warn = array();
	if ( '' === trim( $title ) ) {
		$warn[] = '无标题';
	} elseif ( $tlen > 30 ) {
		$warn[] = '标题偏长(' . $tlen . '字)';
	}
	if ( '' === $desc ) {
		$warn[] = '无描述';
	}
	if ( ! empty( $warn ) ) {
		echo '<span class="sqseo-badge sqseo-badge--warn" title="到文章编辑页下方的 SQ SEO 面板补全">' . esc_html( implode( '，', $warn ) ) . '</span>';
	} else {
		echo '<span class="sqseo-badge sqseo-badge--ok" title="自定义标题与描述均已填写">✓ 已配置</span>';
	}
}

/* =========================================================
 * 十七、激活时：写默认选项（不动已有数据）
 * ======================================================= */

register_activation_hook( __FILE__, 'sqseo_activate' );
function sqseo_activate() {
	if ( false === get_option( 'sqseo_options', false ) ) {
		add_option( 'sqseo_options', sqseo_defaults() );
	}
}
