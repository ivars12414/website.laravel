<div class="cart__summary">
  <div class="cart__summary-row">
    <span>{!! returnWord('Subtotal', WORDS_PROJECT) !!}</span>
    <span>{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($summary['subtotal'])) !!}</span>
  </div>
  <div class="cart__summary-row">
    <span>{!! returnWord('Discount', WORDS_PROJECT) !!}</span>
    <span>{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($summary['discount'])) !!}</span>
  </div>
  <div class="cart__summary-row">
    <span>{!! returnWord('Tax', WORDS_PROJECT) !!}</span>
    <span>{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($summary['tax'])) !!}</span>
  </div>
  <div class="cart__summary-row cart__summary-row--total">
    <strong>{!! returnWord('Total', WORDS_PROJECT) !!}</strong>
    <strong>{!! \App\Services\CreditService::format(\App\Services\CreditService::convert($summary['total'])) !!}</strong>
  </div>
</div>
