<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$rows   = $vid ? $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'vendor_reply_templates' ) . ' WHERE vendor_id = %d ORDER BY id DESC', $vid ) ) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar"><div><span class="v11-eyebrow">مدیریت پشتیبانی</span><h1>پاسخ‌های آماده</h1><p>ساخت و ذخیره متن‌های آماده برای پاسخ‌گویی سریع‌تر به تیکت‌ها.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>

    <section class="v11-surface" style="margin-bottom:20px;">
        <div class="v11-section-head"><h2>افزودن پاسخ آماده جدید</h2></div>
        <form class="demo-form" method="post" action="">
            <?php wp_nonce_field( 'temsah_vendor_save_reply_template' ); ?>
            <input type="hidden" name="temsah_action" value="vendor_save_reply_template">
            <label>عنوان الگو<input name="title" placeholder="مثلاً راهنمای ارسال اطلاعات ورود" required type="text"/></label>
            <label>متن پاسخ<textarea name="body" required rows="3" placeholder="متن کامل پاسخ را بنویسید..."></textarea></label>
            <button class="btn-primary" type="submit">ذخیره الگو</button>
        </form>
    </section>

    <section class="v11-surface">
        <div class="v11-section-head"><h2>الگوهای ذخیره‌شده</h2></div>
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز پاسخ آماده‌ای ثبت نکرده‌اید.</p></div>
        <?php else : foreach ( $rows as $r ) : ?>
            <div style="padding:12px 0;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;align-items:center;gap:12px;">
                <div><b><?php echo esc_html( $r->title ); ?></b><p style="margin:4px 0 0;color:var(--muted);"><?php echo esc_html( $r->body ); ?></p></div>
                <form method="post" action="">
                    <?php wp_nonce_field( 'temsah_vendor_delete_reply_template' ); ?>
                    <input type="hidden" name="temsah_action" value="vendor_delete_reply_template">
                    <input type="hidden" name="template_id" value="<?php echo (int) $r->id; ?>">
                    <button class="btn-outline" type="submit">حذف</button>
                </form>
            </div>
        <?php endforeach; endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
