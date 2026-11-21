<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
global $wpdb;
$vendor = Temsah_Security::get_vendor_by_user( get_current_user_id() );
$vid    = $vendor ? (int) $vendor->id : 0;
$status = sanitize_key( wp_unslash( $_GET['status'] ?? 'all' ) );

$t_p = Temsah_DB::table( 'products' );
$sql = "SELECT p.*, wp.post_title FROM {$t_p} p INNER JOIN {$wpdb->posts} wp ON wp.ID = p.post_id WHERE p.vendor_id = %d";
$params = array( $vid );
if ( in_array( $status, array( 'published', 'pending_review', 'draft', 'rejected' ), true ) ) {
    $sql .= ' AND p.status = %s';
    $params[] = $status;
}
$sql .= ' ORDER BY p.id DESC';
$rows = $vid ? $wpdb->get_results( $wpdb->prepare( $sql, ...$params ) ) : array();
?>
<div class="v11-screen">
    <div class="v11-toolbar">
        <div><span class="v11-eyebrow">پنل فروشندگان / محصولات من</span><h1>محصولات من</h1><p>مدیریت، ویرایش و بررسی وضعیت انتشار محصولات.</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'vendor/new-product' ) ); ?>"><?php echo temsah_ico( 'paper.svg' ); ?> افزودن محصول</a>
    </div>

    <section class="v11-surface">
        <div class="v11-filter-head">
            <div class="v11-tabs">
                <a class="<?php echo 'all' === $status ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/products' ) ); ?>">همه</a>
                <a class="<?php echo 'published' === $status ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/products', array( 'status' => 'published' ) ) ); ?>">منتشرشده</a>
                <a class="<?php echo 'pending_review' === $status ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/products', array( 'status' => 'pending_review' ) ) ); ?>">در حال بررسی</a>
                <a class="<?php echo 'draft' === $status ? 'selected' : ''; ?>" href="<?php echo esc_url( temsah_url( 'vendor/products', array( 'status' => 'draft' ) ) ); ?>">پیش‌نویس</a>
            </div>
        </div>
        <?php if ( empty( $rows ) ) : ?>
            <div class="tm-empty-state"><p>محصولی با این وضعیت یافت نشد.</p></div>
        <?php else : ?>
            <div class="v9-table-wrap"><table class="v9-table"><thead><tr><th>عنوان محصول</th><th>قیمت</th><th>نسخه</th><th>فروش</th><th>وضعیت</th><th>عملیات</th></tr></thead><tbody>
                <?php foreach ( $rows as $r ) : ?>
                <tr>
                    <td><b><?php echo esc_html( $r->post_title ); ?></b></td>
                    <td><?php echo esc_html( temsah_format_toman( Temsah_CPT::effective_price( $r ) ) ); ?></td>
                    <td><?php echo esc_html( $r->current_version ); ?></td>
                    <td><?php echo esc_html( temsah_format_number( $r->sales_count ) ); ?></td>
                    <td><span class="v9-badge <?php echo 'published' === $r->status ? 'green' : ''; ?>"><?php echo esc_html( $r->status ); ?></span></td>
                    <td>
                        <a class="v9-textbutton" href="<?php echo esc_url( temsah_url( 'vendor/edit-product', array( 'id' => $r->id ) ) ); ?>">ویرایش</a>
                        <?php if ( 'published' === $r->status ) : ?> · <a class="v9-textbutton" href="<?php echo esc_url( get_permalink( (int) $r->post_id ) ); ?>" target="_blank">مشاهده</a><?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody></table></div>
        <?php endif; ?>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
