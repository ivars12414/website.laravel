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
                @auth
                    <div class="header__actions hide-mobile">
                        <div class="header__action">
                            <ul class="nav">
                                <li class="nav__item with-sub">
                                    <a href="{{ sectionHref('cabinet') }}" data-modal-caller data-action="auth/profile"
                                       data-cache>
                                        {{ auth()->user()?->name ?? auth()->user()?->email ?? returnWord('My account', WORDS_PROJECT) }}
                                    </a>

                                    <div class="nav__submenu-wrapper">
                                        <ul class="nav__submenu">
                                            @forelse($cabinet_menu as $section)
                                                @continue($section->label === 'logout')
                                                <li @class([
                                                    'nav__submenu-item',
                                                    'active' => section()->id === $section->id,
                                                    'logout' => $section->label === 'logout',
                                                ])
                                                    title="{{ $section->name }}">
                                                    <a href="{{ $section->getUrl() }}" data-modal-caller
                                                       data-action="cabinet/{{ $section->label }}" data-cache>
                                                        {{ $section->name }}
                                                    </a>
                                                </li>
                                            @empty
                                                <li class="nav__submenu-item" title="{!! returnWord('My account', WORDS_PROJECT) !!}">
                                                    <span>{!! returnWord('My account', WORDS_PROJECT) !!}</span>
                                                </li>
                                            @endforelse
                                            <li class="nav__submenu-item logout"
                                                title="{!! returnWord('Log out', WORDS_PROJECT) !!}">
                                                <a href="#" data-modal-caller data-action="auth/logout" data-cache>
                                                    {!! returnWord('Log out', WORDS_PROJECT) !!}
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                @else
                    <div class="actions hide-mobile">
                        <a href="#" class="block__link" data-modal-caller data-action="auth/registration"
                           data-cache>{!! returnWord('Sign Up', WORDS_INTERFACE) !!}</a>
                        <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login"
                           data-cache>{!! returnWord('Sign In', WORDS_INTERFACE) !!}</a>
                    </div>
                @endauth

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
                @auth
                    <div class="actions">
                        <ul class="nav">
                            <li class="nav__item with-sub">
                                <a href="{{ sectionHref('cabinet') }}" data-modal-caller data-action="auth/profile"
                                   data-cache>
                                    {{ auth()->user()?->name ?? auth()->user()?->email ?? returnWord('My account', WORDS_PROJECT) }}
                                </a>

                                <div class="nav__submenu-wrapper">
                                    <ul class="nav__submenu">
                                        @forelse($cabinet_menu as $section)
                                            @continue($section->label === 'logout')
                                            <li @class([
                                                'nav__submenu-item',
                                                'active' => section()->id === $section->id,
                                                'logout' => $section->label === 'logout',
                                            ])
                                                title="{{ $section->name }}">
                                                <a href="{{ $section->getUrl() }}" data-modal-caller
                                                   data-action="cabinet/{{ $section->label }}" data-cache>
                                                    {{ $section->name }}
                                                </a>
                                            </li>
                                        @empty
                                            <li class="nav__submenu-item" title="{!! returnWord('My account', WORDS_PROJECT) !!}">
                                                <span>{!! returnWord('My account', WORDS_PROJECT) !!}</span>
                                            </li>
                                        @endforelse
                                        <li class="nav__submenu-item logout" title="{!! returnWord('Log out', WORDS_PROJECT) !!}">
                                            <a href="#" data-modal-caller data-action="auth/logout" data-cache>
                                                {!! returnWord('Log out', WORDS_PROJECT) !!}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                @else
                    <div class="actions">
                        <a href="#" class="block__link" data-modal-caller data-action="auth/registration"
                           data-cache>{!! returnWord('Sign Up', WORDS_INTERFACE) !!}</a>
                        <a href="#" class="btn btn--main" data-modal-caller data-action="auth/login"
                           data-cache>{!! returnWord('Sign In', WORDS_INTERFACE) !!}</a>
                    </div>
                @endauth

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
