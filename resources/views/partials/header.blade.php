<header class="header mobile-hidden">
    <div class="header__line">
        <div class="container">
            <div class="header__line-wrapper">
                <div class="header__line-actions">
                    <div class="header__line-action">
                        <div class="lng lng--list">
                            <div class="lng__link">
                                English
                            </div>
                            <div class="lng__list-wrapper">
                                <div class="lng__list">
                                    <a href="/ru" class="">
                                        Русский </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="header__line-action">
                        <div class="lng lng--list">
                            <div class="lng__link">EUR</div>
                            <div class="lng__list-wrapper">
                                <div class="lng__list">
                                    <a href="#" data-currency="EUR" class="js-region_link">EUR</a>
                                    <a href="#" data-currency="RUB" class="js-region_link">RUB</a>
                                    <a href="#" data-currency="USD" class="js-region_link">USD</a>
                                    <a href="#" data-currency="GBP" class="js-region_link">GBP</a>
                                    <a href="#" data-currency="SEK" class="js-region_link">SEK</a>
                                    <a href="#" data-currency="PLN" class="js-region_link">PLN</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="header__line-actions" id="login-actions-wrapper">

                    <div class="header__action-item">
                        <a href="#" class="header__user" data-modal-caller="" data-action="auth/registration"
                           data-cache="">main register button text</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="header__wrapper">
            <div class="header__block">
                <a href="/" class="logo">
                    <img src="/userfiles/theme/logo.svg?v=1763379158" alt="Logo">
                </a>
            </div>
            <div class="header__block right">
                <div class="header__actions">
                    <div class="header__action">
                        <div class="search-form-wrapper js-search ">
                        </div>
                    </div>

                    <div class="header__action">
                        <a href="" class="header__action-item wishlist">
                            <svg class="icon">
                                <use xlink:href="#comparison"></use>
                            </svg>
                            <span class="event">0</span>
                        </a>
                    </div>

                    <div class="header__action">
                        <a class="header__action-item wishlist" href="">
                            <svg class="icon">
                                <use xlink:href="#favorite"></use>
                            </svg>
                            <span class="event wish_count-js">0</span>
                        </a>
                    </div>
                    <div class="header__action">
                        <a class="header__action-item js-mobile-cart-container " href="/en/cart">
                            <svg class="icon">
                                <use xlink:href="#cart"></use>
                            </svg>
                            <span class="event" data-cart-items-qty="">0</span>
                            <div class="cart__popup-wrapper">
                                <div class="cart__popup" id="js-cart-popup">
                                </div>
                            </div>
                        </a>

                    </div>
                </div>
            </div>
        </div>
        <div class="header__wrapper header__wrapper--nav">
            <ul class="nav">
                @foreach($page->menu('menu') as $section)
                    <li @class([
                    'nav__item',
                    'active' => $page->section()->id === $section->id,
                    'js-scroll' => !empty($section->scroll_href) && $page->section()->main,
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

</header>
