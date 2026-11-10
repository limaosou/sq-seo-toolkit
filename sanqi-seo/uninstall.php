<?php
/**
 * 卸载清理：删除本插件写入的全部数据（仅在后台「删除插件」时执行）。
 *
 * @package SQ_SEO_Toolkit
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// 设置项、日志、重定向规则、导入导出数据等。
	delete_option( 'sqseo_404_log' );
	delete_option( 'sqseo_indexnow_last' );
	delete_option( 'sqseo_link_report' );
	delete_option( 'sqseo_options' );
	delete_option( 'sqseo_redirects' );
	delete_option( 'sqseo_spider_log' );

// 文章自定义 TDK / FAQ / AI 摘要 / 推送记录等。
	delete_post_meta_by_key( '_sqseo_desc' );
	delete_post_meta_by_key( '_sqseo_faq' );
	delete_post_meta_by_key( '_sqseo_keywords' );
	delete_post_meta_by_key( '_sqseo_push_result' );
	delete_post_meta_by_key( '_sqseo_push_time' );
	delete_post_meta_by_key( '_sqseo_sm_result' );
	delete_post_meta_by_key( '_sqseo_title' );
	delete_post_meta_by_key( '_sqseo_tldr' );

// 每日日志清理定时任务。
	wp_clear_scheduled_hook( 'sqseo_daily_cleanup' );
