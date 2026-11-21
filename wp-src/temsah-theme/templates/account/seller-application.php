<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();
get_template_part( 'template-parts/portal-sidebar-buyer' );
$user   = wp_get_current_user();
$mobile = get_user_meta( $user->ID, 'temsah_mobile', true );
$vendor = Temsah_Security::get_vendor_by_user( $user->ID );
?>
<div class="v9-screen onboard">
    <div class="onboard-top">
        <div>
            <span class="v9-eyebrow">سامانه فروشندگان / درخواست همکاری</span>
            <h1>درخواست فروشندگی در تمساح</h1>
            <p>مشخصات هویتی، بانکی و اطلاعات فروشگاه خود را جهت بررسی توسط مدیریت ارسال کنید.</p>
        </div>
        <a class="btn soft" href="<?php echo esc_url( temsah_url( 'account/seller-application-status' ) ); ?>">مشاهده وضعیت درخواست</a>
    </div>

    <?php if ( ! empty( $_GET['app_err'] ) ) : ?>
        <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['app_err'] ) ) ); ?></div>
    <?php endif; ?>

    <div class="onboard-stepper" id="onboardStepper">
        <div class="onboard-step tm-wiz-pill is-active"><span class="onboard-step-num">۱</span><div><b>قرارداد همکاری</b><small>قوانین و درصد تسویه</small></div></div>
        <div class="onboard-step tm-wiz-pill"><span class="onboard-step-num">۲</span><div><b>شماره موبایل</b><small>ثبت شماره تماس</small></div></div>
        <div class="onboard-step tm-wiz-pill"><span class="onboard-step-num">۳</span><div><b>هویتی و بانکی</b><small>کد ملی و شبا</small></div></div>
        <div class="onboard-step tm-wiz-pill"><span class="onboard-step-num">۴</span><div><b>اطلاعات فروشگاه</b><small>نام و نمونه‌کار</small></div></div>
        <div class="onboard-step tm-wiz-pill"><span class="onboard-step-num">۵</span><div><b>بارگذاری مدرک</b><small>ارسال نهایی</small></div></div>
    </div>

    <form class="surface demo-form" enctype="multipart/form-data" method="post" action="" style="padding:24px;">
        <?php wp_nonce_field( 'temsah_submit_seller_application' ); ?>
        <input type="hidden" name="temsah_action" value="submit_seller_application">

        <div class="tm-wiz-step">
            <h2>مرحله ۱: توافق‌نامه همکاری فروشندگان</h2>
            <p>در مارکت‌پلیس تمساح، تایید فروشندگی منوط به ارائه مدارک معتبر، مالکیت قانونی محصولات ارسالی و پاسخگویی منظم به تیکت‌های خریداران است.</p>
            <label class="check-row"><input name="agreement_accepted" required type="checkbox" value="1"/> قرارداد و قوانین فروشندگان تمساح را مطالعه کرده و می‌پذیرم.</label>
            <div style="margin-top:16px;"><button class="btn-primary" data-wiz-next="" type="button">مرحله بعد</button></div>
        </div>

        <div class="tm-wiz-step" hidden>
            <h2>مرحله ۲: اطلاعات تماس</h2>
            <div class="tm-alert tm-alert-info">تایید پیامکی خودکار نیازمند اتصال پنل پیامکی در تنظیمات است؛ شماره تماس شما ثبت و توسط مدیر بررسی می‌شود.</div>
            <label>نام و نام خانوادگی کامل<input name="full_name" required type="text" value="<?php echo esc_attr( $user->display_name ); ?>"/></label>
            <label>شماره موبایل (۱۱ رقم)<input name="mobile" pattern="09[0-9]{9}" placeholder="09123456789" required type="tel" value="<?php echo esc_attr( $mobile ); ?>"/></label>
            <div style="display:flex;gap:10px;margin-top:16px;"><button class="btn-outline" data-wiz-prev="" type="button">مرحله قبل</button><button class="btn-primary" data-wiz-next="" type="button">مرحله بعد</button></div>
        </div>

        <div class="tm-wiz-step" hidden>
            <h2>مرحله ۳: اطلاعات هویتی و حساب بانکی</h2>
            <div class="input-grid">
                <label>کد ملی (۱۰ رقمی)<input name="national_id" pattern="[0-9]{10}" placeholder="0012345678" required type="text"/></label>
                <label>تاریخ تولد<input name="birth_date" placeholder="1375/01/01" type="text"/></label>
            </div>
            <label>شماره شبا (IR + ۲۴ رقم)<input name="iban" pattern="IR[0-9]{24}" placeholder="IR120570000000000000000001" required type="text"/></label>
            <div style="display:flex;gap:10px;margin-top:16px;"><button class="btn-outline" data-wiz-prev="" type="button">مرحله قبل</button><button class="btn-primary" data-wiz-next="" type="button">مرحله بعد</button></div>
        </div>

        <div class="tm-wiz-step" hidden>
            <h2>مرحله ۴: مشخصات فروشگاه شما</h2>
            <label>نام فروشگاه / برند<input name="store_name" required type="text" value="<?php echo esc_attr( $vendor->store_name ?? '' ); ?>"/></label>
            <label>تخصص اصلی<input name="specialty" placeholder="مثلاً طراحی قالب و افزونه وردپرس" type="text" value="<?php echo esc_attr( $vendor->specialty ?? '' ); ?>"/></label>
            <label>لینک وب‌سایت یا نمونه‌کار<input name="portfolio_url" placeholder="https://example.com" type="url" value="<?php echo esc_attr( $vendor->portfolio_url ?? '' ); ?>"/></label>
            <label>معرفی کوتاه فروشگاه<textarea name="bio" rows="3"><?php echo esc_textarea( $vendor->bio ?? '' ); ?></textarea></label>
            <div style="display:flex;gap:10px;margin-top:16px;"><button class="btn-outline" data-wiz-prev="" type="button">مرحله قبل</button><button class="btn-primary" data-wiz-next="" type="button">مرحله بعد</button></div>
        </div>

        <div class="tm-wiz-step" hidden>
            <h2>مرحله ۵: بارگذاری مدرک هویتی و ارسال نهایی</h2>
            <p>تصویر کارت ملی یا مدرک شناسایی (JPG / PNG / PDF). فایل شما در مخزن حفاظت‌شده بدون دسترسی عمومی وب ذخیره می‌شود.</p>
            <label>فایل مدرک شناسایی<input accept=".jpg,.jpeg,.png,.pdf" name="id_document" type="file"/></label>
            <div style="display:flex;gap:10px;margin-top:16px;"><button class="btn-outline" data-wiz-prev="" type="button">مرحله قبل</button><button class="btn-primary" type="submit">ثبت و ارسال درخواست فروشندگی</button></div>
        </div>
    </form>
</div>
</div></main></div></div>
<?php get_footer(); ?>
