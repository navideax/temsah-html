<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
get_header();

global $wpdb;
the_post();
$post_id = get_the_ID();
$p       = Temsah_CPT::get_product_by_post( $post_id );

if ( ! $p ) {
    echo '<main class="wrap page-pad"><div class="tm-empty-state"><h1>محصول یافت نشد</h1><a class="btn-primary" href="' . esc_url( temsah_url( 'shop' ) ) . '">بازگشت به فروشگاه</a></div></main>';
    get_footer();
    return;
}

$cat_name   = Temsah_CPT::get_product_category_name( $post_id );
$cat_slug   = Temsah_CPT::get_product_category_slug( $post_id );
$seller_url = ! empty( $p->store_slug ) ? temsah_url( 'seller/' . $p->store_slug ) : temsah_url( 'sellers' );
$seller_ini = mb_substr( trim( $p->store_name ?: 'ف' ), 0, 1 );
$eff        = Temsah_CPT::effective_price( $p );
$orig       = (int) $p->price;
$disc_pct   = Temsah_CPT::discount_percent( $p );
$badges     = json_decode( (string) $p->badges_json, true ) ?: array();
$wish_ids   = is_user_logged_in() ? Temsah_Buyer::get_wishlist_product_ids( get_current_user_id() ) : array();
$in_wish    = in_array( (int) $p->id, $wish_ids, true );
$purchased  = is_user_logged_in() ? Temsah_Security::user_has_purchased_product( get_current_user_id(), (int) $p->id ) : null;

$versions = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'product_versions' ) . ' WHERE product_id = %d ORDER BY id DESC', $p->id ) );
$gallery  = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . Temsah_DB::table( 'product_gallery' ) . ' WHERE product_id = %d ORDER BY sort_order ASC', $p->id ) );

$t_fb      = Temsah_DB::table( 'product_feedback' );
$questions = $wpdb->get_results( $wpdb->prepare( "SELECT f.*, u.display_name FROM {$t_fb} f LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id WHERE f.product_id = %d AND f.kind = 'question' AND f.status = 'approved' ORDER BY f.id DESC", $p->id ) );
$reviews   = $wpdb->get_results( $wpdb->prepare( "SELECT f.*, u.display_name FROM {$t_fb} f LEFT JOIN {$wpdb->users} u ON u.ID = f.user_id WHERE f.product_id = %d AND f.kind = 'review' AND f.status = 'approved' ORDER BY f.id DESC", $p->id ) );

$related = Temsah_CPT::query_products( array( 'category' => $cat_slug, 'limit' => 4 ) );
$related = array_values( array_filter( $related, fn( $rp ) => (int) $rp->id !== (int) $p->id ) );
$related = array_slice( $related, 0, 3 );
?>
<main>
<section class="product-head-area">
    <div class="wrap">
        <div class="breadcrumbs">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>">خانه</a><span>/</span>
            <a href="<?php echo esc_url( temsah_url( 'shop' ) ); ?>">محصولات</a><span>/</span>
            <?php if ( $cat_slug ) : ?><a href="<?php echo esc_url( temsah_url( 'shop', array( 'category' => $cat_slug ) ) ); ?>"><?php echo esc_html( $cat_name ); ?></a><span>/</span><?php endif; ?>
            <span><?php echo esc_html( $p->post_title ); ?></span>
        </div>

        <?php if ( ! empty( $_GET['fb_ok'] ) ) : ?>
            <div class="tm-alert tm-alert-ok"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['fb_ok'] ) ) ); ?></div>
        <?php elseif ( ! empty( $_GET['fb_err'] ) ) : ?>
            <div class="tm-alert tm-alert-err"><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['fb_err'] ) ) ); ?></div>
        <?php endif; ?>

        <div class="product-hero-grid">
            <div class="product-art">
                <div aria-label="گالری <?php echo esc_attr( $p->post_title ); ?>" aria-roledescription="اسلایدر تصاویر محصول" class="tm-product-gallery" data-current="0" data-tm-gallery="" role="region" tabindex="0">
                    <div class="tm-gallery-track">
                        <?php if ( ! empty( $gallery ) ) : foreach ( $gallery as $gi => $g ) : ?>
                            <div class="tm-gallery-slide" role="group">
                                <?php echo wp_get_attachment_image( (int) $g->attachment_id, 'large', false, array( 'style' => 'width:100%;height:100%;object-fit:cover;border-radius:14px;' ) ); ?>
                            </div>
                        <?php endforeach; else : ?>
                            <div class="tm-gallery-slide tm-gallery-demo tm-gallery-demo-1" role="group"><?php echo temsah_cover_html( $p, true ); ?></div>
                            <div class="tm-gallery-slide tm-gallery-demo tm-gallery-demo-2" role="group"><?php echo temsah_cover_html( $p, true ); ?></div>
                        <?php endif; ?>
                    </div>
                    <button aria-label="تصویر قبلی" class="tm-gallery-nav tm-gallery-prev" data-tm-gallery-nav="prev" type="button"><?php echo temsah_ico( 'arrow---right.svg' ); ?></button>
                    <button aria-label="تصویر بعدی" class="tm-gallery-nav tm-gallery-next" data-tm-gallery-nav="next" type="button"><?php echo temsah_ico( 'arrow---left.svg' ); ?></button>
                    <div aria-label="انتخاب تصویر" class="tm-gallery-pagination">
                        <button aria-current="true" aria-label="نمایش تصویر ۱" data-tm-gallery-dot="0" type="button"></button>
                        <button aria-current="false" aria-label="نمایش تصویر ۲" data-tm-gallery-dot="1" type="button"></button>
                    </div>
                </div>
            </div>

            <div class="product-features">
                <div class="eyebrow">ویژگی‌های کلیدی</div>
                <h3>دقیقاً آنچه برای شروع نیاز داری</h3>
                <ul>
                    <li><?php echo temsah_ico( 'tick-square.svg' ); ?> طراحی واکنش‌گرا و مرتب</li>
                    <li><?php echo temsah_ico( 'tick-square.svg' ); ?> نسخه جاری: <?php echo esc_html( $p->current_version ?: '1.0.0' ); ?></li>
                    <?php if ( ! empty( $p->compatibility ) ) : ?><li><?php echo temsah_ico( 'tick-square.svg' ); ?> سازگار با <?php echo esc_html( $p->compatibility ); ?></li><?php endif; ?>
                    <li><?php echo temsah_ico( 'tick-square.svg' ); ?> دریافت فوری پس از خرید تاییدشده</li>
                    <li><?php echo temsah_ico( 'tick-square.svg' ); ?> صدور کلید لایسنس اختصاصی</li>
                </ul>
                <div class="feature-note"><?php echo temsah_ico( 'info-circle.svg' ); ?> <?php echo esc_html( $p->support_terms ?: 'پشتیبانی مستقیم از طریق سیستم تیکت حساب کاربری ارائه می‌شود.' ); ?></div>
            </div>

            <div class="product-summary">
                <span class="eyebrow"><?php echo esc_html( $cat_name ); ?></span>
                <h1><?php echo esc_html( $p->post_title ); ?></h1>
                <p><?php echo esc_html( $p->post_excerpt ); ?></p>

                <div aria-label="توسعه‌دهنده و نشان‌های محصول" class="tm-product-seller-row">
                    <div class="summary-seller">
                        <span class="avatar"><?php echo esc_html( $seller_ini ); ?></span>
                        <span>توسط <a href="<?php echo esc_url( $seller_url ); ?>"><?php echo esc_html( $p->store_name ?: 'فروشنده تمساح' ); ?></a></span>
                    </div>
                    <?php if ( ! empty( $badges ) ) : ?>
                        <div aria-label="نشان‌های این محصول" class="tm-product-inline">
                            <?php echo temsah_render_medals( $badges, 'product', $p->post_title, true ); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="summary-stats">
                    <div><b><?php echo esc_html( temsah_fa_digits( number_format( (float) $p->rating_avg, 1 ) ) ); ?> <?php echo temsah_ico( 'star.svg' ); ?></b><small>امتیاز کاربران</small></div>
                    <div><b><?php echo esc_html( temsah_format_number( $p->sales_count ) ); ?></b><small>فروش موفق</small></div>
                    <div><b><?php echo esc_html( temsah_format_date( $p->updated_at ) ); ?></b><small>آخرین بروزرسانی</small></div>
                </div>

                <div class="summary-buttons">
                    <?php if ( $purchased ) : ?>
                        <a class="btn-primary" href="<?php echo esc_url( temsah_url( 'account/downloads' ) ); ?>"><?php echo temsah_ico( 'download.svg' ); ?> دریافت فایل‌های خریداری‌شده</a>
                    <?php else : ?>
                        <button class="btn-primary" data-add="<?php echo (int) $p->id; ?>" type="button"><?php echo temsah_ico( 'bag.svg' ); ?> افزودن به سبد خرید</button>
                    <?php endif; ?>
                    <button aria-label="علاقه‌مندی" class="btn-square<?php echo $in_wish ? ' active' : ''; ?>" data-fav="<?php echo (int) $p->id; ?>" type="button"><?php echo temsah_ico( 'heart.svg' ); ?></button>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="product-tabs">
    <div class="wrap" role="tablist">
        <button aria-controls="description" aria-selected="true" class="is-active" data-product-tab="description" role="tab" tabindex="0" type="button"><?php echo temsah_ico( 'document.svg' ); ?> توضیحات محصول</button>
        <button aria-controls="features" aria-selected="false" data-product-tab="features" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'category.svg' ); ?> مشخصات محصول</button>
        <button aria-controls="updates" aria-selected="false" data-product-tab="updates" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'paper.svg' ); ?> لیست تغییرات</button>
        <button aria-controls="questions" aria-selected="false" data-product-tab="questions" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'chat.svg' ); ?> پرسش و پاسخ (<?php echo esc_html( temsah_fa_digits( count( $questions ) ) ); ?>)</button>
        <button aria-controls="reviews" aria-selected="false" data-product-tab="reviews" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'star.svg' ); ?> دیدگاه کاربران (<?php echo esc_html( temsah_fa_digits( count( $reviews ) ) ); ?>)</button>
        <button aria-controls="support" aria-selected="false" data-product-tab="support" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'ticket.svg' ); ?> پشتیبانی</button>
        <button aria-controls="faq" aria-selected="false" data-product-tab="faq" role="tab" tabindex="-1" type="button"><?php echo temsah_ico( 'info-circle.svg' ); ?> سؤالات متداول</button>
    </div>
</div>

<section class="product-detail-bg">
    <div class="wrap product-body-layout">
        <div class="product-center">
            <article class="surface detail-panel tm-tab-panel" id="description" role="tabpanel">
                <div class="panel-title"><span class="orange-marker"></span><h2>درباره <?php echo esc_html( $p->post_title ); ?></h2></div>
                <div class="tm-description-content" id="productDescriptionBody">
                    <?php echo wp_kses_post( wpautop( $p->post_content ?: $p->post_excerpt ) ); ?>
                </div>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="features" role="tabpanel">
                <div class="panel-title"><span class="orange-marker"></span><h2>مشخصات فنی محصول</h2></div>
                <div class="spec-grid">
                    <div><span>عنوان</span><b><?php echo esc_html( $p->post_title ); ?></b></div>
                    <div><span>دسته‌بندی</span><b><?php echo esc_html( $cat_name ); ?></b></div>
                    <div><span>فروشنده</span><b><?php echo esc_html( $p->store_name ?: '—' ); ?></b></div>
                    <div><span>نسخه فعلی</span><b><?php echo esc_html( $p->current_version ?: '1.0.0' ); ?></b></div>
                    <div><span>سازگاری</span><b><?php echo esc_html( $p->compatibility ?: 'مشخص نشده' ); ?></b></div>
                    <div><span>آخرین بروزرسانی</span><b><?php echo esc_html( temsah_format_date( $p->updated_at ) ); ?></b></div>
                </div>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="updates" role="tabpanel">
                <div class="panel-title"><span class="orange-marker"></span><h2>لیست تغییرات و نسخه‌ها</h2></div>
                <?php if ( empty( $versions ) ) : ?>
                    <div class="release"><span class="release-dot"></span><div><b>نسخه <?php echo esc_html( $p->current_version ?: '1.0.0' ); ?></b><small><?php echo esc_html( temsah_format_date( $p->updated_at ) ); ?></small><p>انتشار اولیه محصول در تمساح.</p></div></div>
                <?php else : foreach ( $versions as $ver ) : ?>
                    <div class="release"><span class="release-dot"></span><div><b>نسخه <?php echo esc_html( $ver->version ); ?></b><small><?php echo esc_html( temsah_format_date( $ver->created_at ) ); ?></small><p><?php echo nl2br( esc_html( $ver->changelog ) ); ?></p></div></div>
                <?php endforeach; endif; ?>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="questions" role="tabpanel">
                <div class="panel-title split"><div><span class="orange-marker"></span><h2>پرسش و پاسخ</h2></div><button class="btn-outline" data-feedback-open="question" type="button">طرح پرسش جدید</button></div>
                <?php if ( empty( $questions ) ) : ?>
                    <p class="tm-empty-feedback">هنوز پرسشی برای این محصول ثبت نشده است.</p>
                <?php else : foreach ( $questions as $q ) : $q_ini = mb_substr( trim( $q->display_name ?: 'ک' ), 0, 1 ); ?>
                    <div class="question">
                        <div class="question-avatar"><?php echo esc_html( $q_ini ); ?></div>
                        <div>
                            <b><?php echo esc_html( $q->display_name ?: 'کاربر تمساح' ); ?></b>
                            <small><?php echo esc_html( temsah_format_date( $q->created_at ) ); ?></small>
                            <p><?php echo nl2br( esc_html( $q->content ) ); ?></p>
                            <?php if ( ! empty( $q->vendor_reply ) ) : ?>
                                <div class="question-answer"><?php echo temsah_ico( 'chat.svg' ); ?> پاسخ فروشنده: <?php echo nl2br( esc_html( $q->vendor_reply ) ); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; endif; ?>

                <form class="tm-feedback-form" hidden id="tmQuestionForm" method="post" action="">
                    <?php wp_nonce_field( 'temsah_submit_product_feedback' ); ?>
                    <input type="hidden" name="temsah_action" value="submit_product_feedback">
                    <input type="hidden" name="product_id" value="<?php echo (int) $p->id; ?>">
                    <input type="hidden" name="kind" value="question">
                    <?php if ( ! is_user_logged_in() ) : ?>
                        <p>برای ثبت پرسش ابتدا <a href="<?php echo esc_url( temsah_url( 'login' ) ); ?>">وارد حساب کاربری</a> شوید.</p>
                    <?php else : ?>
                        <label>پرسش شما<textarea maxlength="1000" name="message" placeholder="سؤال خود را درباره این محصول بنویسید" required rows="4"></textarea></label>
                        <div class="tm-form-actions"><button class="btn-primary" type="submit">ارسال پرسش</button><button class="btn-outline" data-feedback-close="" type="button">انصراف</button></div>
                    <?php endif; ?>
                </form>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="reviews" role="tabpanel">
                <div class="panel-title split"><div><span class="orange-marker"></span><h2>دیدگاه خریداران</h2></div><button class="btn-outline" data-feedback-open="review" type="button">ثبت دیدگاه</button></div>
                <?php if ( empty( $reviews ) ) : ?>
                    <p class="tm-empty-feedback">هنوز دیدگاهی ثبت نشده است.</p>
                <?php else : foreach ( $reviews as $rv ) : $r_ini = mb_substr( trim( $rv->display_name ?: 'خ' ), 0, 1 ); ?>
                    <div class="question">
                        <div class="question-avatar"><?php echo esc_html( $r_ini ); ?></div>
                        <div>
                            <b><?php echo esc_html( $rv->display_name ?: 'خریدار' ); ?> · امتیاز <?php echo esc_html( temsah_fa_digits( $rv->rating ) ); ?> از ۵</b>
                            <small><?php echo esc_html( temsah_format_date( $rv->created_at ) ); ?> <?php echo $rv->is_verified_buyer ? '· خریدار تاییدشده' : ''; ?></small>
                            <p><?php echo nl2br( esc_html( $rv->content ) ); ?></p>
                        </div>
                    </div>
                <?php endforeach; endif; ?>

                <form class="tm-feedback-form" hidden id="tmReviewForm" method="post" action="">
                    <?php wp_nonce_field( 'temsah_submit_product_feedback' ); ?>
                    <input type="hidden" name="temsah_action" value="submit_product_feedback">
                    <input type="hidden" name="product_id" value="<?php echo (int) $p->id; ?>">
                    <input type="hidden" name="kind" value="review">
                    <?php if ( ! $purchased ) : ?>
                        <p>ثبت امتیاز و دیدگاه فقط برای خریداران تأییدشده این محصول امکان‌پذیر است.</p>
                    <?php else : ?>
                        <label>امتیاز<select name="rating" required><option value="5">۵ ستاره</option><option value="4">۴ ستاره</option><option value="3">۳ ستاره</option><option value="2">۲ ستاره</option><option value="1">۱ ستاره</option></select></label>
                        <label>دیدگاه شما<textarea maxlength="1000" name="message" placeholder="تجربه خود را بنویسید" required rows="4"></textarea></label>
                        <div class="tm-form-actions"><button class="btn-primary" type="submit">ثبت دیدگاه</button><button class="btn-outline" data-feedback-close="" type="button">انصراف</button></div>
                    <?php endif; ?>
                </form>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="support" role="tabpanel">
                <div class="panel-title"><span class="orange-marker"></span><h2>پشتیبانی محصول</h2></div>
                <?php if ( $purchased ) : ?>
                    <div class="support-locked" style="border-style:solid;">
                        <h3>شما خریدار تاییدشده این محصول هستید</h3>
                        <p>برای دریافت پشتیبانی فنی مستقیم از فروشنده، تیکت جدید ثبت کنید.</p>
                        <div><a class="btn-primary" href="<?php echo esc_url( temsah_url( 'account/ticket-new', array( 'product_id' => $p->id ) ) ); ?>">ثبت تیکت پشتیبانی محصول</a></div>
                    </div>
                <?php else : ?>
                    <div class="support-locked">
                        <span><?php echo temsah_ico( 'lock.svg' ); ?></span>
                        <h3>برای استفاده از پشتیبانی باید محصول را خریداری کنید.</h3>
                        <p>پس از تکمیل خرید، از بخش پشتیبانی حساب کاربری می‌توانید تیکت مستقیم برای فروشنده ثبت کنید.</p>
                        <div>
                            <button class="btn-primary" data-add="<?php echo (int) $p->id; ?>" type="button">افزودن به سبد خرید</button>
                            <a class="btn-outline" href="<?php echo esc_url( temsah_url( 'support' ) ); ?>">راهنمای پشتیبانی</a>
                        </div>
                    </div>
                <?php endif; ?>
            </article>

            <article class="surface detail-panel tm-tab-panel" hidden id="faq" role="tabpanel">
                <div class="panel-title"><span class="orange-marker"></span><h2>سؤالات متداول</h2></div>
                <details class="faq"><summary>پس از خرید فایل‌ها را از کجا دریافت می‌کنم؟</summary><p>بلافاصله پس از تایید پرداخت، فایل‌ها و کلید لایسنس در بخش دانلودهای حساب کاربری فعال می‌شوند.</p></details>
                <details class="faq"><summary>آیا پیش از خرید می‌توانم از فروشنده سؤال بپرسم؟</summary><p>بله، از تب پرسش و پاسخ می‌توانید سوال پیش از خرید خود را ثبت کنید.</p></details>
                <details class="faq"><summary>آیا به‌روزرسانی‌های بعدی رایگان است؟</summary><p>تمامی نسخه‌های جدید منتشرشده توسط فروشنده در بخش دانلودهای حساب کاربری شما قرار می‌گیرد.</p></details>
            </article>

            <?php if ( ! empty( $related ) ) : ?>
            <section class="related-section">
                <div class="section-head"><div><h2>محصولات مرتبط</h2><p>شاید این محصولات هم به کارت بیان.</p></div></div>
                <div class="cards-grid related-cards">
                    <?php foreach ( $related as $rp ) { get_template_part( 'template-parts/product-card', null, array( 'product' => $rp, 'wish_ids' => $wish_ids ) ); } ?>
                </div>
            </section>
            <?php endif; ?>
        </div>

        <aside class="product-buy-rail">
            <div class="surface buy-box">
                <div class="buy-price">
                    <small>قیمت محصول</small>
                    <?php if ( $disc_pct > 0 ) : ?><del><?php echo esc_html( temsah_format_toman( $orig ) ); ?></del><?php endif; ?>
                    <div><strong><?php echo esc_html( 0 === $eff ? 'رایگان' : temsah_format_number( $eff ) ); ?></strong><?php if ( $eff > 0 ) : ?><small>تومان</small><?php endif; ?></div>
                </div>
                <div class="buy-benefits">
                    <p><?php echo temsah_ico( 'tick-square.svg' ); ?> دریافت فایل بعد از خرید تأییدشده</p>
                    <p><?php echo temsah_ico( 'tick-square.svg' ); ?> دسترسی به بروزرسانی‌های محصول</p>
                    <p><?php echo temsah_ico( 'tick-square.svg' ); ?> پشتیبانی از طریق سیستم تیکت</p>
                </div>
                <?php if ( $purchased ) : ?>
                    <a class="btn-primary buy-full" href="<?php echo esc_url( temsah_url( 'account/downloads' ) ); ?>"><?php echo temsah_ico( 'download.svg' ); ?> دریافت از پنل کاربری</a>
                <?php else : ?>
                    <button class="btn-primary buy-full" data-add="<?php echo (int) $p->id; ?>" type="button"><?php echo temsah_ico( 'bag.svg' ); ?> افزودن به سبد خرید</button>
                <?php endif; ?>
                <button class="btn-outline buy-full" data-demo="پیش‌نمایش آنلاین برای این محصول ثبت نشده است." <?php if ( ! empty( $p->demo_url ) ) : ?>data-demo-url="<?php echo esc_url( $p->demo_url ); ?>"<?php endif; ?> type="button">
                    <?php echo temsah_ico( 'discovery.svg' ); ?> پیش‌نمایش محصول
                </button>
                <div class="buy-footer">
                    <span><?php echo temsah_ico( 'star.svg' ); ?> <?php echo esc_html( temsah_fa_digits( number_format( (float) $p->rating_avg, 1 ) ) ); ?> امتیاز</span>
                    <span><?php echo temsah_ico( 'buy.svg' ); ?> <?php echo esc_html( temsah_format_number( $p->sales_count ) ); ?> فروش</span>
                </div>
            </div>

            <div class="surface vendor-small">
                <span class="avatar"><?php echo esc_html( $seller_ini ); ?></span>
                <div>
                    <h3><?php echo esc_html( $p->store_name ?: 'فروشنده تمساح' ); ?></h3>
                    <p><?php echo esc_html( $p->vendor_specialty ?: 'توسعه‌دهنده محصولات دیجیتال' ); ?></p>
                    <a href="<?php echo esc_url( $seller_url ); ?>">مشاهده پروفایل <?php echo temsah_ico( 'arrow---left.svg' ); ?></a>
                </div>
            </div>

            <div class="seller-banner"><?php echo temsah_ico( 'shield-done.svg' ); ?> <span>خرید آگاهانه<br/><small>قبل از خرید مشخصات و شرایط استفاده را بررسی کنید.</small></span></div>
        </aside>
    </div>
</section>
</main>
<?php get_footer(); ?>
