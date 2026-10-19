<?php defined('ABSPATH') || exit; ?>
<dialog class="local-dialog local-drawer av-cart-dialog" id="av-cart-dialog" aria-labelledby="av-cart-title" aria-modal="true">
    <header class="dialog-heading cart-drawer-heading">
        <h2 id="av-cart-title">Your cart <span class="cart-title-count" data-av-cart-title-count></span></h2>
        <button type="button" class="icon-button cart-dialog-close" aria-label="Close cart" data-av-cart-close autofocus>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
        </button>
    </header>

    <div class="cart-drawer-body">
        <section class="cart-rewards" data-av-cart-rewards aria-label="Cart rewards" hidden>
            <p class="cart-rewards-message" data-av-cart-reward-message role="status" aria-live="polite" aria-atomic="true"></p>
            <div class="cart-rewards-progress" data-av-cart-progress role="progressbar" aria-label="Progress toward cart rewards" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
                <span class="cart-rewards-progress-fill" aria-hidden="true"></span>
                <span class="cart-rewards-marker cart-rewards-marker--shipping" aria-hidden="true"></span>
                <span class="cart-rewards-marker cart-rewards-marker--gift" aria-hidden="true"></span>
            </div>
            <ol class="cart-rewards-milestones">
                <li class="cart-reward-milestone" data-av-cart-milestone="shipping">
                    <span class="cart-reward-name">Free shipping</span>
                    <span class="cart-reward-threshold" data-av-cart-shipping-threshold></span>
                </li>
                <li class="cart-reward-milestone" data-av-cart-milestone="gift">
                    <span class="cart-reward-name">Gift</span>
                    <span class="cart-reward-threshold" data-av-cart-gift-threshold></span>
                </li>
            </ol>
            <button type="button" class="cart-gift-trigger" data-av-cart-gift-open aria-controls="av-gift-dialog" aria-haspopup="dialog" aria-expanded="false" hidden>Choose your gift</button>
        </section>

        <div class="cart-drawer-items" data-av-cart-items aria-label="Cart items">
            <p class="cart-loading" role="status">Loading your cart…</p>
        </div>

        <div class="cart-feedback" data-av-cart-status role="status" aria-live="polite" aria-atomic="true" hidden></div>
        <p class="cart-feedback" data-av-cart-error role="status" aria-live="polite" aria-atomic="true" hidden></p>

        <details class="cart-disclosure cart-recommendations" data-av-cart-recommendations hidden>
            <summary>Recommended products <span class="cart-disclosure-icon" aria-hidden="true"></span></summary>
            <div class="cart-recommendation-list" data-av-cart-recommendation-items data-av-cart-recommendation-list></div>
        </details>

        <details class="cart-disclosure cart-coupon">
            <summary>Promo code <span class="cart-disclosure-icon" aria-hidden="true"></span></summary>
            <form class="cart-coupon-form" data-av-coupon-form>
                <label for="av-cart-coupon-code">Promo code</label>
                <div class="cart-coupon-fields">
                    <input id="av-cart-coupon-code" type="text" name="coupon_code" autocomplete="off" autocapitalize="none" spellcheck="false" aria-describedby="av-cart-coupon-feedback" required>
                    <button type="submit" class="cart-coupon-apply" data-av-coupon-submit>Apply</button>
                </div>
            </form>
            <p id="av-cart-coupon-feedback" class="cart-feedback" data-av-coupon-error role="status" aria-live="polite" aria-atomic="true" hidden></p>
            <div class="cart-coupons" data-av-cart-coupons aria-label="Applied promo codes"></div>
        </details>
    </div>

    <footer class="cart-drawer-summary" data-av-cart-summary hidden>
        <div class="cart-summary-discount" data-av-cart-discount hidden>
            <span>Discount</span>
            <strong data-av-cart-discount-value></strong>
        </div>
        <div class="subtotal cart-summary-total">
            <span>Total</span>
            <strong data-av-cart-total></strong>
        </div>
        <p class="cart-summary-note" data-av-cart-shipping-note>Shipping and taxes are calculated at checkout.</p>
        <a class="local-primary cart-checkout-cta" href="<?php echo esc_url(wc_get_checkout_url()); ?>">Proceed to checkout</a>
        <a class="text-button cart-full-link" href="<?php echo esc_url(wc_get_cart_url()); ?>">View cart</a>
    </footer>

    <dialog class="local-dialog av-gift-dialog" id="av-gift-dialog" aria-labelledby="av-gift-title" aria-describedby="av-gift-description" aria-modal="true">
        <header class="dialog-heading cart-gift-heading">
            <div>
                <h2 id="av-gift-title">Choose your gift</h2>
                <p id="av-gift-description">Choose a gift or continue without selecting one.</p>
            </div>
            <button type="button" class="icon-button cart-dialog-close" aria-label="Close gift selection" data-av-gift-close autofocus>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false"><path d="m6 6 12 12M18 6 6 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </button>
        </header>
        <div class="cart-gift-body">
            <p class="cart-feedback" data-av-gift-error role="status" aria-live="polite" aria-atomic="true" hidden></p>
            <div class="cart-gift-options" data-av-gift-options role="group" aria-label="Gift options"></div>
        </div>
        <footer class="cart-gift-footer">
            <button type="button" class="cart-gift-continue" data-av-gift-close>Back to cart</button>
        </footer>
    </dialog>
</dialog>
