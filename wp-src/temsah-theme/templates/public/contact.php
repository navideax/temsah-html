<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
?>
<main>
<section class="wrap page-pad">
    <div class="breadcrumbs"><a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span><span>تماس با ما</span></div>
    <div class="page-heading"><div><span class="eyebrow">پشتیبانی و ارتباط</span><h1>تماس با تمساح</h1><p>سوالات عمومی، پیشنهادها و درخواست‌های همکاری خود را برای ما ارسال کنید.</p></div></div>
    <?php if ( ! empty( $_GET['contact_ok'] ) ) : ?>
        <div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['contact_ok'] ) ) ); ?></div>
    <?php elseif ( ! empty( $_GET['contact_err'] ) ) : ?>
        <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['contact_err'] ) ) ); ?></div>
    <?php endif; ?>
    <div class="page-two-column">
        <div>
            <form class="surface article-detail" method="post" action="">
                <?php wp_nonce_field( 'temsah_send_contact_message' ); ?>
                <input type="hidden" name="temsah_action" value="send_contact_message">
                <h2>فرم ارسال پیام</h2>
                <p><label>نام و نام خانوادگی<input name="name" required type="text" style="width:100%;margin-top:6px;"></label></p>
                <p><label>ایمیل پاسخ‌گویی<input name="email" required type="email" style="width:100%;margin-top:6px;"></label></p>
                <p><label>موضوع<input name="subject" required type="text" style="width:100%;margin-top:6px;"></label></p>
                <p><label>متن پیام<textarea name="message" required rows="5" style="width:100%;margin-top:6px;"></textarea></label></p>
                <button class="btn-primary" type="submit">ارسال پیام</button>
            </form>
        </div>
        <aside class="page-side-note">
            <div class="surface summary-card">
                <h3>اطلاعات پشتیبانی</h3>
                <p>ایمیل: <b><?php echo esc_html( temsah_theme_opt( 'support_email', 'support@temsah.me' ) ); ?></b></p>
                <p>ساعات پاسخگویی: <?php echo esc_html( temsah_theme_opt( 'support_hours', 'شنبه تا چهارشنبه ۹ تا ۱۸' ) ); ?></p>
            </div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
