@extends('layouts.app')

@section('content')
    <div class="page">
        <div class="page__wrapper">
            @include('partials.header')
            <section class="section section--gray-light balance" style="background-image: url(/userfiles/bg.png);">
                <div class="container">
                    <div class="block__header">
                        @include('partials.breadcrumbs')

                        <div class="block__title center">
                            <h1><strong>{{ $page->meta('title') }}</strong></h1>
                        </div>
                    </div>

                    <div class="settings__wrapper">
                        @php
                            /** @var \App\Models\Client|null $client */
                            $client = auth('client')->user();
                        @endphp
                        <div class="settings__block">
                            <form class="form" data-form="profile">
                                <div class="block__title">
                                    <strong>{!! returnWord('Information', WORDS_PROJECT) !!}</strong>
                                </div>

                                <div class="js-alert alert" data-form="profile" data-group="default"
                                     style="display: none"></div>

                                <div class="form__block">
                                    <label class="label">{!! returnWord('Name', WORDS_INTERFACE) !!}:</label>
                                    <input type="text" class="input" name="name" value="{{ $client?->name }}"
                                           autocomplete="name">
                                </div>

                                <div class="form__block">
                                    <label class="label">{!! returnWord('Surname', WORDS_INTERFACE) !!}:</label>
                                    <input type="text" class="input" name="surname" value="{{ $client?->surname }}"
                                           autocomplete="name">
                                </div>

                                <div class="form__block">
                                    <label for="" class="label">{!! returnWord('Birth', WORDS_INTERFACE) !!}:</label>

                                    <div class="form__row">
                                        <div class="form__block small">
                                            <div class="select">
                                                <select name="birth_day">
                                                    <option value="" selected
                                                            disabled>{!! returnWord('DD', WORDS_INTERFACE) !!}</option>
                                                    @for($i = 1; $i <= 31; $i++)
                                                        <option value="{{ $i }}"
                                                            {{ $client?->birthday?->day == $i ? 'selected' : '' }}
                                                        >{{ sprintf("%02d", $i) }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form__block small">
                                            <div class="select">
                                                <select name="birth_month">
                                                    <option value="" selected
                                                            disabled>{!! returnWord('MM', WORDS_INTERFACE) !!}</option>
                                                    @foreach(getEnglishMonthAndDayNames()['months'] as $i => $month)
                                                        <option
                                                            value="{{ $i }}" {{ $client?->birthday?->month == $i ? 'selected' : '' }}
                                                        >{!! returnWord($month, WORDS_INTERFACE) !!}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form__block small">
                                            <div class="select">
                                                <select name="birth_year">
                                                    <option value="" selected
                                                            disabled>{!! returnWord('YYYY', WORDS_INTERFACE) !!}</option>
                                                    @for($i = (date('Y') - 100);
                                                               $i <= date('Y');
                                                               $i++)
                                                        <option value="{{ $i }}"
                                                            {{ $client?->birthday?->year == $i ? 'selected' : '' }}
                                                        >{{ $i }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form__block">
                                    <label class="label">{!! returnWord('E-mail', WORDS_INTERFACE) !!}:</label>
                                    <input type="email" class="input" name="mail"
                                           autocomplete="email" value="{{ $client?->mail }}">
                                </div>

                                <div class="form__block">
                                    <label for="sign-up-phone-code"
                                           class="label">{!! returnWord('Phone', WORDS_INTERFACE) !!}
                                        :</label>

                                    <div class="form__row">
                                        <div class="form__block small">
                                            <div class="select">
                                                <select name="phone_country">
                                                    <option value="" selected disabled></option>
                                                    @foreach(\App\Models\Country::whereActive()
                                                                           ->where('lang_id', lang()->id)
                                                                           ->where('phonecode', '>', 0)
                                                                           ->orderBy('phonecode')
                                                                           ->get() as $country)
                                                        <option value="<?= $country->hash ?>"
                                                            {{ $country->hash === $client?->phone_country ? 'selected' : '' }}>
                                                            +{{ $country->phonecode }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form__block large">
                                            <input type="tel" class="input" id="sign-up-phone" name="phone"
                                                   autocomplete="phone"
                                                   value="{{ $client?->phone }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form__block">
                                    <button class="<?= !empty($config['class']) ? $config['class'] : 'btn btn--main' ?>"
                                            type="submit"><span
                                            class="js-form-spinner loading-circle loading-circle--btn loading-circle--white"
                                            style="display: none;"></span>
                                        {!! returnWord('Update info', WORDS_INTERFACE) !!}
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="settings__block">
                            <form class="form" data-form="change-password">
                                <div class="block__title">
                                    <strong>{!! returnWord('Password change', WORDS_PROJECT) !!}</strong>
                                </div>

                                <div class="js-alert alert" data-form="change-password" data-group="default"
                                     style="display: none"></div>

                                <div class="form__block">
                                    <label for="settings-current-password"
                                           class="label">{!! returnWord('Current password', WORDS_INTERFACE) !!}
                                        :</label>
                                    <input type="password" class="input" id="settings-current-password"
                                           name="password_0">
                                </div>

                                <div class="form__block">
                                    <label for="settings-new-password"
                                           class="label">{!! returnWord('New password', WORDS_INTERFACE) !!}
                                        :</label>
                                    <input type="password" class="input" id="settings-new-password" name="password_1">
                                </div>

                                <div class="form__block">
                                    <label for="settings-confirm-new-password"
                                           class="label">{!! returnWord('Confirm new password', WORDS_INTERFACE) !!}
                                        :</label>
                                    <input type="password" class="input" id="settings-confirm-new-password"
                                           name="password_2">
                                </div>

                                <div class="form__block">
                                    <button class="<?= !empty($config['class']) ? $config['class'] : 'btn btn--main' ?>"
                                            type="submit"><span
                                            class="js-form-spinner loading-circle loading-circle--btn loading-circle--white"
                                            style="display: none;"></span>
                                        {!! returnWord('Save', WORDS_INTERFACE) !!}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        @include('partials.footer')
    </div>
@endsection

@section('js')
    <script>
        $(function () {

            new FormSubmit({
                formSelector: 'form[data-form=profile]',
                ajaxUrl: '/modules/cabinet/profile/profile_handler.php?lid={{ lang()->id }}',
                alertTimeout: 3000,
                createDefaultAlerts: false,
            });

            new FormSubmit({
                formSelector: 'form[data-form="change-password"]',
                ajaxUrl: '/modules/cabinet/change_password/change_password_handler.php?lid={{ lang()->id }}',
                alertTimeout: 3000,
                resetFormOnSuccess: true,
                createDefaultAlerts: false,
            });

        })
    </script>
@endsection
