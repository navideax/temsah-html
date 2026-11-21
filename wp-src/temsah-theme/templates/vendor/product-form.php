<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
$edit_id = absint( $_GET['id'] ?? 0 );
$editing = null;
if ( $edit_id > 0 ) {
    $editing = Temsah_Security::user_owns_product( $edit_id );
    if ( ! $editing ) {
        status_header( 403 );
        wp_die( 'شما مجوز ویرایش این محصول را ندارید.', 'خطای دسترسی (۴۰۳)', array( 'response' => 403 ) );
    }
    $editing = Temsah_CPT::get_product_by_id( $edit_id );
}

get_header();
get_template_part( 'template-parts/portal-sidebar-vendor' );
$cur_cat = $editing ? Temsah_CPT::get_product_category_slug( (int) $editing->post_id ) : 'wordpress';
$cats    = array(
    'wordpress' => 'قالب وردپرس',
    'plugin'    => 'افزونه وردپرس',
    'html'      => 'قالب HTML',
    'script'    => 'اسکریپت PHP',
    'dashboard' => 'پنل مدیریت',
    'app'       => 'سورس اپلیکیشن',
    'ui'        => 'رابط کاربری',
);
$covers  = array( 'cover-store' => 'فروشگاهی', 'cover-forms' => 'فرم‌ساز / ابزار', 'cover-news' => 'مجله / خبری', 'cover-speed' => 'افزونه سرعت / سیستم', 'cover-admin' => 'داشبورد مدیریت', 'cover-ui' => 'کیت UI', 'cover-agency' => 'شرکتی / لندینگ', 'cover-task' => 'مدیریت پروژه', 'cover-booking' => 'رزرواسیون', 'cover-fashion' => 'فروشگاهی مدرن', 'cover-mobile' => 'اپلیکیشن', 'cover-menu' => 'مگامنو' );
?>
<div class="v11-screen">
    <div class="v11-toolbar">
        <div><span class="v11-eyebrow">مدیریت محصولات</span><h1><?php echo $editing ? 'ویرایش محصول' : 'ثبت محصول جدید'; ?></h1><p>مشخصات، قیمت، توضیحات و فایل بسته‌بندی‌شده محصول را وارد کنید.</p></div>
        <a class="v9-action" href="<?php echo esc_url( temsah_url( 'vendor/products' ) ); ?>">بازگشت به محصولات</a>
    </div>

    <?php if ( ! empty( $_GET['ok'] ) ) : ?><div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['ok'] ) ) ); ?></div><?php endif; ?>
    <?php if ( ! empty( $_GET['err'] ) ) : ?><div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['err'] ) ) ); ?></div><?php endif; ?>

    <section class="v11-surface">
        <form class="demo-form" enctype="multipart/form-data" method="post" action="">
            <?php wp_nonce_field( 'temsah_vendor_save_product' ); ?>
            <input type="hidden" name="temsah_action" value="vendor_save_product">
            <input type="hidden" name="product_id" value="<?php echo $editing ? (int) $editing->id : 0; ?>">

            <label>عنوان محصول<input name="title" required type="text" value="<?php echo esc_attr( $editing->post_title ?? '' ); ?>"/></label>
            <div class="input-grid">
                <label>دسته‌بندی
                    <select name="category">
                        <?php foreach ( $cats as $slug => $lbl ) : ?>
                            <option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $cur_cat, $slug ); ?>><?php echo esc_html( $lbl ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
                <label>طرح جلد محصول (Cover)
                    <select name="cover_style">
                        <?php foreach ( $covers as $ck => $cl ) : ?>
                            <option value="<?php echo esc_attr( $ck ); ?>" <?php selected( $editing->cover_style ?? 'cover-store', $ck ); ?>><?php echo esc_html( $cl ); ?></option>
                        <?php endforeach; ?>
                    </select>
                </label>
            </div>

            <div class="input-grid">
                <label>قیمت اصلی (تومان)<input min="0" name="price" required type="number" value="<?php echo esc_attr( $editing->price ?? 0 ); ?>"/></label>
                <label>قیمت با تخفیف (اختیاری)<input min="0" name="sale_price" type="number" value="<?php echo esc_attr( $editing->sale_price ?? '' ); ?>"/></label>
            </div>

            <div class="input-grid">
                <label>نسخه فعلی<input name="version" required type="text" value="<?php echo esc_attr( $editing->current_version ?? '1.0.0' ); ?>"/></label>
                <label>سازگاری<input name="compatibility" placeholder="WordPress 6.4+ / PHP 8.1+" type="text" value="<?php echo esc_attr( $editing->compatibility ?? '' ); ?>"/></label>
            </div>

            <label>لینک پیش‌نمایش آنلاین (اختیاری)<input name="demo_url" placeholder="https://demo.example.com" type="url" value="<?php echo esc_attr( $editing->demo_url ?? '' ); ?>"/></label>
            <label>معرفی کوتاه<input name="excerpt" required type="text" value="<?php echo esc_attr( $editing->post_excerpt ?? '' ); ?>"/></label>
            <label>توضیحات کامل محصول<textarea name="content" rows="6"><?php echo esc_textarea( $editing->post_content ?? '' ); ?></textarea></label>
            <label>تغییرات این نسخه (Changelog)<textarea name="changelog" placeholder="شرح تغییرات نسخه جدید..." rows="3"></textarea></label>
            <label>آپلود فایل محصول (ZIP/RAR - ذخیره در مخزن حفاظت‌شده)<input accept=".zip,.rar,.7z,.gz,.pdf" name="product_file" type="file"/></label>

            <div style="display:flex;gap:12px;margin-top:16px;">
                <button class="btn-primary" name="submit_mode" type="submit" value="pending_review">ارسال برای تایید / ذخیره</button>
                <button class="btn-outline" name="submit_mode" type="submit" value="draft">ذخیره به عنوان پیش‌نویس</button>
            </div>
        </form>
    </section>
</div>
</div></main></div></div>
<?php get_footer(); ?>
