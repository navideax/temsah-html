<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );

global $wpdb;
$vendor    = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid       = $vendor ? (int) $vendor->id : 0;
$t_p       = Temsah_DB::table( 'products' );
$t_c       = Temsah_DB::table( 'commissions' );
$t_tk      = Temsah_DB::table( 'tickets' );

$pub_count   = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_p} WHERE vendor_id = %d AND status = 'published'", $vid ) ) : 0;
$open_tk     = $vid ? (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$t_tk} WHERE vendor_id = %d AND status != 'closed'", $vid ) ) : 0;
$recent_sales = $vid ? $wpdb->get_results(
    $wpdb->prepare(
        "SELECT c.*, oi.product_title FROM {$t_c} c INNER JOIN " . Temsah_DB::table( 'order_items' ) . " oi ON oi.id = c.order_item_id WHERE c.vendor_id = %d ORDER BY c.id DESC LIMIT 6",
        $vid
    )
) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar">
        <div><span class="v11-eyebrow">پنل فروشندگان / داشبورد توسعه‌دهنده</span><h1>داشبورد توسعه‌دهنده</h1><p>فروشگاه <?php echo esc_html( $vendor ? $vendor->store_name : 'تمساح' ); ?> · نمای کلی فروش و فعالیت‌ها</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'vendor/new-product' ) ); ?>"><?php echo temsah_ico( 'paper.svg' ); ?> افزودن محصول</a>
    </div>

    <div class="v11-top-banners">
        <a class="v11-banner green" href="<?php echo esc_url( temsah_url( 'vendor/finance' ) ); ?>"><small>گزارش عملکرد</small><strong>کیف پول و درآمدها</strong><span>مشاهده جزئیات مالی</span></a>
        <a class="v11-banner orange" href="<?php echo esc_url( temsah_url( 'vendor/products' ) ); ?>"><small>مدیریت کاتالوگ</small><strong>محصولات فروشگاه</strong><span>مشاهده محصولات</span></a>
        <a class="v11-banner blue" href="<?php echo esc_url( temsah_url( 'vendor/ready-replies' ) ); ?>"><small>مدیریت پشتیبانی</small><strong>پاسخ سریع‌تر به خریدار</strong><span>پاسخ‌های آماده</span></a>
        <a class="v11-banner lilac" href="<?php echo esc_url( temsah_url( 'vendor/store-settings' ) ); ?>"><small>هویت فروشگاه</small><strong>تنظیمات فروشگاه</strong><span>ویرایش اطلاعات</span></a>
    </div>

    <div class="v11-kpis report">
        <div class="v11-kpi"><span class="v9-icon green"><?php echo temsah_ico( 'wallet.svg' ); ?></span><small>موجودی قابل برداشت</small><strong><?php echo esc_html( temsah_format_toman( $vendor ? $vendor->available_balance : 0 ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon blue"><?php echo temsah_ico( 'buy.svg' ); ?></span><small>فروش کل ثبت‌شده</small><strong><?php echo esc_html( temsah_format_number( $vendor ? $vendor->total_sales : 0 ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon amber"><?php echo temsah_ico( 'category.svg' ); ?></span><small>محصولات منتشرشده</small><strong><?php echo esc_html( temsah_format_number( $pub_count ) ); ?></strong></div>
        <div class="v11-kpi"><span class="v9-icon pink"><?php echo temsah_ico( 'chat.svg' ); ?></span><small>تیکت‌های باز</small><strong><?php echo esc_html( temsah_format_number( $open_tk ) ); ?></strong></div>
    </div>

    <section class="v11-surface">
        <div class="v11-section-head"><h2>آخرین فروش‌های ثبت‌شده</h2><a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'vendor/earnings' ) ); ?>">مشاهده همه</a></div>
        <?php if ( empty( $recent_sales ) ) : ?>
            <div class="tm-empty-state"><p>هنوز فروشی برای محصولات شما ثبت نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>محصول</th><th>مبلغ فروش</th><th>سهم خالص شما</th><th>تاریخ</th></tr></thead><tbody>
                <?php foreach ( $recent_sales as $rs ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $rs->product_title ); ?></b></td>
                    <td><?php echo esc_html( temsah_format_toman( $rs->gross_amount ) ); ?></td>
                    <td><strong style="color:#047857;">+<?php echo esc_html( temsah_format_toman( $rs->vendor_amount ) ); ?></strong></td>
                    <td><?php echo esc_html( temsah_format_date( $rs->created_at ) ); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
