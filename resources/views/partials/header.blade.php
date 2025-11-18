<header class="header js-header">
    <div class="container">
        <div class="header__wrapper">
            <div class="header__block">
                <a href="{{ sectionHref() }}" class="logo">
                    <img src="" alt="logo">
                </a>
            </div>

            <div class="header__block hide-mobile">
                <ul class="nav">
                    @foreach($page->menu('menu') as $section)
                        <li @class([
                    'nav__item',
                    'active' => section()->id === $section->id,
                    'js-scroll' => !empty($section->scroll_href) && section()->main,
                ])
                            title="{{ $section->name }}">
                            <a href="{{ $section->getUrl() }}">
                                {{ $section->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="header__block right">
                {{--                @if (isLoged())--}}
                {{--                    <div class="header__actions hide-mobile">--}}
                {{--                        <div class="header__action">--}}
                {{--                            <div class="mini-cart">--}}
                {{--                                <a href="{{ sectionHref('cart') }}" class="mini-cart__link">--}}
                {{--                                    <img src="/images/cart.svg" alt="{!! returnWord('Cart icon', WORDS_PROJECT) !!}">--}}
                {{--                                    <span class="count"--}}
                {{--                                          data-cart-items-qty>{{ CartManager::getSummary()['qty'] }}</span>--}}
                {{--                                </a>--}}
                {{--                            </div>--}}

                {{--                            @if(section()->label !== 'cart')--}}
                {{--                                <div class="mini-cart__wrapper" id="cart-dropdown">--}}
                {{--                                    @include('partials.cart_dropdown', ['items' => CartManager::getItems(), 'summary' => CartManager::getSummary()])--}}
                {{--                                </div>--}}
                {{--                            @endif--}}
                {{--                        </div>--}}

                {{--                        <div class="header__action">--}}
                {{--                            <ul class="nav">--}}
                {{--                                <li class="nav__item with-sub">--}}
                {{--                                    <a href="<?= sectionHref('cabinet'); ?>">{!! returnWord('My account', WORDS_PROJECT) !!}</a>--}}

                {{--                                    <div class="nav__submenu-wrapper">--}}
                {{--                                        <ul class="nav__submenu">--}}
                {{--                                            @foreach($cabinet_menu as $section)--}}
                {{--                                                <li @class([--}}
                {{--                                'nav__submenu-item',--}}
                {{--                                'active' => section()->id === $section->id,--}}
                {{--                                'logout' => $section->label === 'logout',--}}
                {{--                            ])--}}
                {{--                                                    title="{{ $section->name }}">--}}
                {{--                                                    <a href="{{ $section->getUrl() }}">--}}
                {{--                                                        {{ $section->name }}--}}
                {{--                                                    </a>--}}
                {{--                                                </li>--}}
                {{--                                            @endforeach--}}
                {{--                                        </ul>--}}
                {{--                                    </div>--}}
                {{--                                </li>--}}
                {{--                            </ul>--}}
                {{--                        </div>--}}
                {{--                    </div>--}}
                {{--                @else--}}
                <div class="actions hide-mobile">
                    <a href="#" class="block__link" data-modal-caller data-action="auth/registration"
                       data-cache>{!! returnWord('Sign Up', WORDS_INTERFACE) !!}</a>
                    <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login"
                       data-cache>{!! returnWord('Sign In', WORDS_INTERFACE) !!}</a>
                </div>
                {{--                @endif--}}

                <a href="#" class="menu__open hide-desktop js-menu-open">
                    <i></i>
                </a>
            </div>
        </div>
    </div>
</header>
<!-- /header -->

<!-- mob-menu -->
<div class="menu hide-desktop js-menu" style="background-image: url(/userfiles/footer-bg.png);">
    <div class="container">
        <div class="menu__wrapper">
            <div class="menu__header">
                {{--                @if (isLoged())--}}
                {{--                    <div class="actions">--}}
                {{--                        <div class="mini-cart">--}}
                {{--                            <a href="{{ sectionHref('cart') }}" class="mini-cart__link">--}}
                {{--                                <img src="/images/cart.svg" alt="">--}}
                {{--                                <span class="count" data-cart-items-qty>{{ CartManager::getSummary()['qty'] }}</span>--}}
                {{--                            </a>--}}
                {{--                        </div>--}}

                {{--                        <ul class="nav">--}}
                {{--                            <li class="nav__item with-sub">--}}
                {{--                                <a href="{{ sectionHref('cabinet') }}">{!! returnWord('My account', WORDS_PROJECT) !!}</a>--}}

                {{--                                <div class="nav__submenu-wrapper">--}}
                {{--                                    <ul class="nav__submenu">--}}
                {{--                                        @foreach($cabinet_menu as $section)--}}
                {{--                                            <li @class([--}}
                {{--                                'nav__submenu-item',--}}
                {{--                                'active' => section()->id === $section->id,--}}
                {{--                                'logout' => $section->label === 'logout',--}}
                {{--                            ])--}}
                {{--                                                title="{{ $section->name }}">--}}
                {{--                                                <a href="{{ $section->getUrl() }}">--}}
                {{--                                                    {{ $section->name }}--}}
                {{--                                                </a>--}}
                {{--                                            </li>--}}
                {{--                                        @endforeach--}}
                {{--                                    </ul>--}}
                {{--                                </div>--}}
                {{--                            </li>--}}
                {{--                        </ul>--}}
                {{--                    </div>--}}
                {{--                @else--}}
                <div class="actions">
                    <a href="#" class="block__link" data-modal-caller data-action="auth/registration"
                       data-cache>{!! returnWord('Sign Up', WORDS_INTERFACE) !!}</a>
                    <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login"
                       data-cache>{!! returnWord('Sign In', WORDS_INTERFACE) !!}</a>
                </div>
                {{--                @endif--}}

                <a href="#" class="menu__close js-menu-close"></a>
            </div>

            <ul class="nav">
                @foreach($page->menu('menu') as $section)
                    <li @class([
                    'nav__item',
                    'active' => section()->id === $section->id,
                    'js-scroll' => !empty($section->scroll_href) && section()->main,
                ])
                        title="{{ $section->name }}">
                        <a href="{{ $section->getUrl() }}">
                            {{ $section->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
<!-- /mob-menu -->
