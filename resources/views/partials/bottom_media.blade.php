<script>
    $(function () {
        $("[data-change-currency]").on('click', function (e) {
            e.preventDefault();

            let $this = $(this);

            $.ajax({
                type: "POST",
                url: "/modules/currency/change_currency.php?lid={{ lang()->id }}",
                data: {code: $this.data('change-currency')},
                dataType: 'JSON',
                success: function (response) {
                    /* SHOW TOAST ALERT */
                    if (!response['error']) {
                        location.reload();
                    } else {
                        console.log(response);
                    }
                }
            });

        });

        $('body').on('click', 'input.error, .input__wrapper.error, textarea.textarea--error', function () {
            $(this).removeClass('error').removeClass('textarea--error').next('.js-input-error-msg').remove();
        });

        new FormSubmit({
            formSelector: '[data-add-to-cart]',
            ajaxUrl: '/api/cart/add-product',
            createDefaultAlerts: false,
            successCallback: (r, {$form, $submit}) => {
                $('[data-cart-items-qty]').html(r.summary.qty);
                $form.find('[data-go-to-cart-btn]').show();
                $submit.hide();
                toastr.success(r.msg);

                const cartDropBlock = document.querySelector('#cart-dropdown');
                if (cartDropBlock && r.cart_dropdown !== undefined) {
                    cartDropBlock.innerHTML = r.cart_dropdown;
                }
            },
        });

    });

</script>

@if(!isLoged())
    @include('partials.auth_js')
@else
    <script src="/js/ItemQuantitySelector/ItemQuantitySelector.js?v=1.<?= time() ?>"></script>
    <script>
        $(function () {
            new ItemQuantitySelector({
                itemFormSelector: '.js-cart-item-count-form',
                minQty: 1,
                onChange: ({itemForm}) => {

                    // $('[data-go-to-cart-btn]').hide();

                    fetch('/api/cart/set-product-qty', {
                        method: 'POST',
                        body: new URLSearchParams(new FormData(itemForm)),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': window.csrfToken,
                        },
                    })
                        .then(res => res.json())
                        .then(r => {
                            const summaryBlock = document.querySelector('.js-cart-summary-block');
                            if (summaryBlock && r.summary_html !== undefined) {
                                summaryBlock.innerHTML = r.summary_html;
                            }
                            const cartDropBlock = document.querySelector('#cart-dropdown');
                            if (cartDropBlock && r.cart_dropdown !== undefined) {
                                cartDropBlock.innerHTML = r.cart_dropdown;
                            }
                            $('[data-cart-items-qty]').html(r.summary.qty);
                        })
                        .catch(err => {
                            console.error('Server error:', err);
                        });
                },
            });

            $('body').on('click', '[data-remove-cart-item]', function (e) {
                e.preventDefault();

                let $this = $(this);

                fetch('/api/cart/remove-item', {
                    method: 'POST',
                    body: new URLSearchParams({id: $this.data('remove-cart-item')}),
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': window.csrfToken,
                    },
                })
                    .then(res => res.json())
                    .then(r => {

                        if (r.error) {
                            toastr.error(r.msg);
                        } else {

                            if (r.redirect_href) {
                                window.location.href = r.redirect_href;
                                return;
                            }

                            $(`[data-cart-item-row=${$this.data('remove-cart-item')}]`).remove();

                            const summaryBlock = document.querySelector('.js-cart-summary-block');
                            if (summaryBlock && r.summary_html !== undefined) {
                                summaryBlock.innerHTML = r.summary_html;
                            }
                            const cartDropBlock = document.querySelector('#cart-dropdown');
                            if (cartDropBlock && r.cart_dropdown !== undefined) {
                                cartDropBlock.innerHTML = r.cart_dropdown;
                            }
                            $('[data-cart-items-qty]').html(r.summary.qty);

                            toastr.success(r.msg);

                        }

                    })
                    .catch(err => {
                        console.error('Server error:', err);
                    });

            });

            new Scroll({
                linkSelector: '[data-scroll-to]',
                smoothScrollOffset: 100,
                smoothScrollSpeed: 700,
            })
        })
    </script>
@endif

<script src="/js/inputMask/inputMask.js?v=<?= INPUT_MASK_V ?>"></script>
<script src="/js/FormSubmit/FormSubmit.js?v=<?= FORM_SUBMIT_V ?>"></script>
<script src="/js/inputValidator/inputValidator.js?v=<?= INPUT_VALIDATOR_V ?>"></script>
<script src="/js/ModalManager/ModalManager.js?v=<?= MODAL_MANAGER_V ?>"></script>
<script src="/js/showMore/showMore.js?v=<?= SHOW_MORE_V ?>"></script>
<script src="/js/Scroll/Scroll.js?v=<?= SCROLL_V ?>"></script>
