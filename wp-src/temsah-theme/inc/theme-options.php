<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Temsah_Theme_Options {
    public static function init(): void {
        add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
        add_action( 'admin_post_temsah_save_theme_options', array( __CLASS__, 'save_options' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_media' ) );
    }

    public static function add_page(): void {
        add_theme_page(
            'تنظیمات پوسته تمساح',
            'تنظیمات پوسته تمساح',
            'manage_options',
            'temsah-theme-options',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function enqueue_media( string $hook ): void {
        if ( 'appearance_page_temsah-theme-options' !== $hook ) {
            return;
        }
        wp_enqueue_media();
    }

    public static function save_options(): void {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'دسترسی غیرمجاز', '۴۰۳', array( 'response' => 403 ) );
        }
        check_admin_referer( 'temsah_save_theme_options' );

        $opts = array(
            'custom_logo_id'    => absint( $_POST['custom_logo_id'] ?? 0 ),
            'favicon_id'        => absint( $_POST['favicon_id'] ?? 0 ),
            'hero_image_id'     => absint( $_POST['hero_image_id'] ?? 0 ),
            'brand_title_fa'    => sanitize_text_field( wp_unslash( $_POST['brand_title_fa'] ?? 'تمساح' ) ),
            'brand_title_en'    => sanitize_text_field( wp_unslash( $_POST['brand_title_en'] ?? 'TEMSAH' ) ),
            'utility_notice'    => sanitize_text_field( wp_unslash( $_POST['utility_notice'] ?? 'مارکت‌پلیس محصولات دیجیتال و توسعه وب' ) ),
            'hero_heading'      => sanitize_text_field( wp_unslash( $_POST['hero_heading'] ?? 'ابزار بهتر، پروژه حرفه‌ای‌تر.' ) ),
            'hero_subtitle'     => sanitize_text_field( wp_unslash( $_POST['hero_subtitle'] ?? 'قالب، افزونه و اسکریپت مورد نیازت رو از بین محصولات توسعه‌دهندگان ایرانی پیدا کن.' ) ),
            'promo_badge'       => sanitize_text_field( wp_unslash( $_POST['promo_badge'] ?? 'فروش ویژه و تخفیف‌ها' ) ),
            'promo_title'       => sanitize_text_field( wp_unslash( $_POST['promo_title'] ?? 'قالب‌ها و افزونه‌های منتخب با تخفیف ویژه' ) ),
            'footer_about'      => sanitize_textarea_field( wp_unslash( $_POST['footer_about'] ?? 'تمساح بازار محصولات دیجیتال برای خرید و فروش قالب، افزونه و اسکریپت است.' ) ),
            'footer_copyright'  => sanitize_text_field( wp_unslash( $_POST['footer_copyright'] ?? '© تمام حقوق برای تمساح محفوظ است.' ) ),
            'support_email'     => sanitize_email( wp_unslash( $_POST['support_email'] ?? 'support@temsah.me' ) ),
            'support_hours'     => sanitize_text_field( wp_unslash( $_POST['support_hours'] ?? 'شنبه تا چهارشنبه ۹ تا ۱۸' ) ),
        );

        update_option( 'temsah_theme_options', $opts );
        wp_safe_redirect( admin_url( 'themes.php?page=temsah-theme-options&saved=1' ) );
        exit;
    }

    public static function render_page(): void {
        $opts = get_option( 'temsah_theme_options', array() );
        $logo_url = ! empty( $opts['custom_logo_id'] ) ? wp_get_attachment_image_url( (int) $opts['custom_logo_id'], 'thumbnail' ) : temsah_asset( 'brand/temsah-tm.png' );
        $fav_url  = ! empty( $opts['favicon_id'] ) ? wp_get_attachment_image_url( (int) $opts['favicon_id'], 'thumbnail' ) : get_template_directory_uri() . '/favicon.png';
        $hero_url = ! empty( $opts['hero_image_id'] ) ? wp_get_attachment_image_url( (int) $opts['hero_image_id'], 'medium' ) : '';
        ?>
        <div class="wrap" style="direction:rtl;max-width:920px;font-family:Vazirmatn,Tahoma,sans-serif;">
            <h1>تنظیمات ظاهری پوسته تمساح (Theme Options)</h1>
            <?php if ( ! empty( $_GET['saved'] ) ) : ?>
                <div class="notice notice-success is-dismissible"><p>تنظیمات پوسته با موفقیت ذخیره شد.</p></div>
            <?php endif; ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="background:#fff;padding:24px;border:1px solid #e5e7eb;border-radius:12px;">
                <?php wp_nonce_field( 'temsah_save_theme_options' ); ?>
                <input type="hidden" name="action" value="temsah_save_theme_options">
                <table class="form-table">
                    <tr>
                        <th>لوگوی اصلی برند (Media Library)</th>
                        <td>
                            <input type="hidden" name="custom_logo_id" id="tm_logo_id" value="<?php echo esc_attr( $opts['custom_logo_id'] ?? 0 ); ?>">
                            <img id="tm_logo_preview" src="<?php echo esc_url( $logo_url ); ?>" style="width:48px;height:48px;border-radius:10px;border:1px solid #ddd;vertical-align:middle;object-fit:contain;">
                            <button type="button" class="button tm-media-pick" data-target="#tm_logo_id" data-preview="#tm_logo_preview">انتخاب از کتابخانه رسانه</button>
                            <button type="button" class="button tm-media-clear" data-target="#tm_logo_id">بازنشانی پیش‌فرض</button>
                        </td>
                    </tr>
                    <tr>
                        <th>فاوآیکون (Favicon)</th>
                        <td>
                            <input type="hidden" name="favicon_id" id="tm_fav_id" value="<?php echo esc_attr( $opts['favicon_id'] ?? 0 ); ?>">
                            <img id="tm_fav_preview" src="<?php echo esc_url( $fav_url ); ?>" style="width:32px;height:32px;border-radius:6px;border:1px solid #ddd;vertical-align:middle;">
                            <button type="button" class="button tm-media-pick" data-target="#tm_fav_id" data-preview="#tm_fav_preview">انتخاب فاوآیکون</button>
                        </td>
                    </tr>
                    <tr>
                        <th>تصویر جایگزین هیرو (اختیاری)</th>
                        <td>
                            <input type="hidden" name="hero_image_id" id="tm_hero_id" value="<?php echo esc_attr( $opts['hero_image_id'] ?? 0 ); ?>">
                            <?php if ( $hero_url ) : ?><img id="tm_hero_preview" src="<?php echo esc_url( $hero_url ); ?>" style="max-height:70px;border-radius:8px;vertical-align:middle;"><?php endif; ?>
                            <button type="button" class="button tm-media-pick" data-target="#tm_hero_id" data-preview="#tm_hero_preview">انتخاب تصویر هیرو</button>
                            <p class="description">در صورت خالی بودن، تصویربرداری اصلی V31 کدنویسی‌شده نمایش داده می‌شود.</p>
                        </td>
                    </tr>
                    <tr><th>نام فارسی و انگلیسی برند</th><td>
                        <input type="text" name="brand_title_fa" value="<?php echo esc_attr( $opts['brand_title_fa'] ?? 'تمساح' ); ?>">
                        <input type="text" name="brand_title_en" value="<?php echo esc_attr( $opts['brand_title_en'] ?? 'TEMSAH' ); ?>">
                    </td></tr>
                    <tr><th>متن نوار بالایی</th><td><input type="text" name="utility_notice" class="regular-text" value="<?php echo esc_attr( $opts['utility_notice'] ?? 'مارکت‌پلیس محصولات دیجیتال و توسعه وب' ); ?>"></td></tr>
                    <tr><th>تیتر هیرو صفحه اصلی</th><td><input type="text" name="hero_heading" class="regular-text" value="<?php echo esc_attr( $opts['hero_heading'] ?? 'ابزار بهتر، پروژه حرفه‌ای‌تر.' ); ?>"></td></tr>
                    <tr><th>توضیح هیرو صفحه اصلی</th><td><input type="text" name="hero_subtitle" class="large-text" value="<?php echo esc_attr( $opts['hero_subtitle'] ?? 'قالب، افزونه و اسکریپت مورد نیازت رو از بین محصولات توسعه‌دهندگان ایرانی پیدا کن.' ); ?>"></td></tr>
                    <tr><th>تیتر بنر تخفیف صفحه اصلی</th><td><input type="text" name="promo_title" class="large-text" value="<?php echo esc_attr( $opts['promo_title'] ?? 'قالب‌ها و افزونه‌های منتخب با تخفیف ویژه' ); ?>"></td></tr>
                    <tr><th>متن معرفی فوتر</th><td><textarea name="footer_about" rows="3" class="large-text"><?php echo esc_textarea( $opts['footer_about'] ?? 'تمساح بازار محصولات دیجیتال برای خرید و فروش قالب، افزونه و اسکریپت است.' ); ?></textarea></td></tr>
                    <tr><th>کپی‌رایت فوتر</th><td><input type="text" name="footer_copyright" class="large-text" value="<?php echo esc_attr( $opts['footer_copyright'] ?? '© تمام حقوق برای تمساح محفوظ است.' ); ?>"></td></tr>
                    <tr><th>ایمیل و ساعات پشتیبانی</th><td>
                        <input type="email" name="support_email" value="<?php echo esc_attr( $opts['support_email'] ?? 'support@temsah.me' ); ?>">
                        <input type="text" name="support_hours" value="<?php echo esc_attr( $opts['support_hours'] ?? 'شنبه تا چهارشنبه ۹ تا ۱۸' ); ?>">
                    </td></tr>
                </table>
                <p><button class="button button-primary button-large">ذخیره تنظیمات پوسته</button></p>
            </form>
        </div>
        <script>
        jQuery(function($){
            $('.tm-media-pick').on('click', function(e){
                e.preventDefault();
                var btn = $(this), target = $(btn.data('target')), preview = $(btn.data('preview'));
                var frame = wp.media({ title: 'انتخاب تصویر', button: { text: 'انتخاب' }, multiple: false });
                frame.on('select', function(){
                    var att = frame.state().get('selection').first().toJSON();
                    target.val(att.id);
                    if(preview.length) preview.attr('src', att.url);
                });
                frame.open();
            });
            $('.tm-media-clear').on('click', function(){ $($(this).data('target')).val(0); });
        });
        </script>
        <?php
    }
}
Temsah_Theme_Options::init();
