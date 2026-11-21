<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
global $wpdb;
$uid  = get_current_user_id();
$t_l  = Temsah_DB::table( 'licenses' );
$t_p  = Temsah_DB::table( 'products' );
$rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT l.*, wp.post_title
         FROM {$t_l} l
         INNER JOIN {$t_p} p ON p.id = l.product_id
         INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id
         WHERE l.user_id = %d
         ORDER BY l.id DESC",
        $uid
    )
);
?>
<div class="v9-screen">
    <div class="v9-heading"><div><span class="v9-eyebrow">سامانه خریداران / مجوزها</span><h1>مدیریت کلیدهای لایسنس</h1><p>ثبت دامنه و مشاهده کلیدهای لایسنس صادرشده پس از خرید.</p></div></div>
    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>

    <section class="v9-panel">
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>هنوز لایسنسی برای حساب شما صادر نشده است.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>محصول</th><th>کلید لایسنس</th><th>وضعیت</th><th>دامنه متصل</th></tr></thead><tbody>
            <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $r->post_title ); ?></b></td>
                    <td><code><?php echo esc_html( $r->license_key ); ?></code></td>
                    <td><span class="v9-badge <?php echo 'active' === $r->status ? 'green' : ''; ?>"><?php echo 'active' === $r->status ? 'فعال' : 'لغوشده'; ?></span></td>
                    <td>
                        <form method="post" action="" style="display:flex;gap:6px;align-items:center;">
                            <?php wp_nonce_field( 'temsah_buyer_bind_license_domain' ); ?>
                            <input type="hidden" name="temsah_action" value="buyer_bind_license_domain">
                            <input type="hidden" name="license_id" value="<?php echo (int) $r->id; ?>">
                            <input type="text" name="domain" value="<?php echo esc_attr( $r->domain ); ?>" placeholder="example.ir" style="width:150px;">
                            <button class="btn-outline" style="padding:5px 10px;" type="submit">ثبت</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
